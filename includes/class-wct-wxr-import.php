<?php

defined( 'ABSPATH' ) || exit;

/**
 * Completes media hydration for products imported through Tools > Import > WordPress.
 *
 * Product records are imported first. Remote media is then processed one product at a
 * time through WooCommerce Action Scheduler, with WP-Cron as a fallback. Keeping image
 * downloads outside the WXR request prevents import timeouts and HTTP 500 errors.
 */
final class WCT_WXR_Import {
    private const FEATURED_META = '_apt_import_featured_image_url';
    private const GALLERY_META  = '_apt_import_gallery_image_urls';
    private const EMBEDDED_META = '_apt_import_embedded_media_urls';
    private const SOURCE_META   = '_apt_source_url';
    private const ERROR_META    = '_apt_import_media_errors';
    private const QUEUED_META   = '_apt_import_media_queued';
    private const ATTEMPTS_META = '_apt_import_media_attempts';
    private const ACTION_HOOK   = 'wct_process_wxr_product_media';
    private const ACTION_GROUP  = 'advanced-product-tabs-import';
    private const MAX_ATTEMPTS  = 3;

    public static function init(): void {
        add_action( 'import_end', array( __CLASS__, 'queue_imported_products' ), 20 );
        add_action( 'admin_init', array( __CLASS__, 'maybe_queue_pending_products' ) );
        add_action( self::ACTION_HOOK, array( __CLASS__, 'process_product' ), 10, 1 );
    }

    /**
     * Recover a partially completed/failed import when an administrator next loads wp-admin.
     */
    public static function maybe_queue_pending_products(): void {
        if ( ! current_user_can( 'upload_files' ) || get_transient( 'apt_wxr_queue_lock' ) ) {
            return;
        }

        set_transient( 'apt_wxr_queue_lock', 1, MINUTE_IN_SECONDS );
        self::queue_imported_products();
    }

    /**
     * Queue products containing temporary WXR media metadata. No remote request is made here.
     */
    public static function queue_imported_products(): void {
        if ( ! function_exists( 'wc_get_product' ) ) {
            return;
        }

        $product_ids = get_posts(
            array(
                'post_type'              => 'product',
                'post_status'            => 'any',
                'posts_per_page'         => -1,
                'fields'                 => 'ids',
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'meta_query'             => array(
                    'relation' => 'OR',
                    array(
                        'key'     => self::FEATURED_META,
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key'     => self::GALLERY_META,
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key'     => self::EMBEDDED_META,
                        'compare' => 'EXISTS',
                    ),
                ),
            )
        );

        $delay = 0;
        foreach ( $product_ids as $product_id ) {
            $product_id = (int) $product_id;
            $attempts   = absint( get_post_meta( $product_id, self::ATTEMPTS_META, true ) );

            if ( $attempts >= self::MAX_ATTEMPTS || get_post_meta( $product_id, self::QUEUED_META, true ) ) {
                continue;
            }

            self::schedule_product( $product_id, $delay );
            $delay += 5;
        }
    }

    private static function schedule_product( int $product_id, int $delay = 0 ): void {
        update_post_meta( $product_id, self::QUEUED_META, time() );

        if ( 0 === $delay && function_exists( 'as_enqueue_async_action' ) ) {
            $action_id = as_enqueue_async_action(
                self::ACTION_HOOK,
                array( 'product_id' => $product_id ),
                self::ACTION_GROUP
            );

            if ( $action_id ) {
                return;
            }
        }

        if ( function_exists( 'as_schedule_single_action' ) ) {
            $action_id = as_schedule_single_action(
                time() + max( 1, $delay ),
                self::ACTION_HOOK,
                array( 'product_id' => $product_id ),
                self::ACTION_GROUP,
                true
            );

            if ( $action_id ) {
                return;
            }
        }

        $args = array( $product_id );
        if ( ! wp_next_scheduled( self::ACTION_HOOK, $args ) ) {
            $scheduled = wp_schedule_single_event( time() + max( 5, $delay ), self::ACTION_HOOK, $args );
            if ( false !== $scheduled && ! is_wp_error( $scheduled ) ) {
                return;
            }
        }

        delete_post_meta( $product_id, self::QUEUED_META );
    }

    /**
     * Process one product in its own background request.
     */
    public static function process_product( $product_id ): void {
        $product_id = absint( $product_id );
        if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
            return;
        }

        delete_post_meta( $product_id, self::QUEUED_META );
        $attempts = absint( get_post_meta( $product_id, self::ATTEMPTS_META, true ) ) + 1;
        update_post_meta( $product_id, self::ATTEMPTS_META, $attempts );

        $errors = self::hydrate_product( $product_id );

        if ( ! $errors ) {
            delete_post_meta( $product_id, self::ATTEMPTS_META );
            return;
        }

        if ( $attempts < self::MAX_ATTEMPTS ) {
            self::schedule_product( $product_id, $attempts * 5 * MINUTE_IN_SECONDS );
        }
    }

    /**
     * @return array<string,string> Import errors keyed by remote URL.
     */
    private static function hydrate_product( int $product_id ): array {
        $product = wc_get_product( $product_id );

        if ( ! $product ) {
            return array();
        }

        $errors  = array();
        $url_map = array();

        $featured_url = esc_url_raw( (string) get_post_meta( $product_id, self::FEATURED_META, true ) );
        if ( $featured_url ) {
            $attachment_id = self::import_attachment( $featured_url, $product_id );
            if ( is_wp_error( $attachment_id ) ) {
                $errors[ $featured_url ] = $attachment_id->get_error_message();
            } else {
                $product->set_image_id( $attachment_id );
                $local_url = wp_get_attachment_url( $attachment_id );
                if ( $local_url ) {
                    $url_map[ $featured_url ] = $local_url;
                }
                delete_post_meta( $product_id, self::FEATURED_META );
            }
        }

        $gallery_urls = self::normalize_url_list( get_post_meta( $product_id, self::GALLERY_META, true ) );
        if ( $gallery_urls ) {
            $gallery_ids    = array();
            $failed_gallery = array();

            foreach ( $gallery_urls as $gallery_url ) {
                $attachment_id = self::import_attachment( $gallery_url, $product_id );
                if ( is_wp_error( $attachment_id ) ) {
                    $errors[ $gallery_url ] = $attachment_id->get_error_message();
                    $failed_gallery[]       = $gallery_url;
                    continue;
                }

                $gallery_ids[] = $attachment_id;
                $local_url     = wp_get_attachment_url( $attachment_id );
                if ( $local_url ) {
                    $url_map[ $gallery_url ] = $local_url;
                }
            }

            if ( $gallery_ids ) {
                $product->set_gallery_image_ids( array_values( array_unique( $gallery_ids ) ) );
            }

            if ( $failed_gallery ) {
                update_post_meta( $product_id, self::GALLERY_META, $failed_gallery );
            } else {
                delete_post_meta( $product_id, self::GALLERY_META );
            }
        }

        $embedded_urls   = self::normalize_url_list( get_post_meta( $product_id, self::EMBEDDED_META, true ) );
        $failed_embedded = array();

        foreach ( $embedded_urls as $embedded_url ) {
            if ( isset( $url_map[ $embedded_url ] ) ) {
                continue;
            }

            $attachment_id = self::import_attachment( $embedded_url, $product_id );
            if ( is_wp_error( $attachment_id ) ) {
                $errors[ $embedded_url ] = $attachment_id->get_error_message();
                $failed_embedded[]       = $embedded_url;
                continue;
            }

            $local_url = wp_get_attachment_url( $attachment_id );
            if ( $local_url ) {
                $url_map[ $embedded_url ] = $local_url;
            }
        }

        if ( $url_map ) {
            self::rewrite_product_content( $product_id, $url_map );
        }

        if ( $failed_embedded ) {
            update_post_meta( $product_id, self::EMBEDDED_META, $failed_embedded );
        } else {
            delete_post_meta( $product_id, self::EMBEDDED_META );
        }

        if ( $errors ) {
            update_post_meta( $product_id, self::ERROR_META, $errors );
        } else {
            delete_post_meta( $product_id, self::ERROR_META );
        }

        $product->save();
        return $errors;
    }

    private static function rewrite_product_content( int $product_id, array $url_map ): void {
        $tabs = get_post_meta( $product_id, '_wct_product_tabs', true );
        if ( is_array( $tabs ) ) {
            update_post_meta( $product_id, '_wct_product_tabs', self::replace_urls_recursive( $tabs, $url_map ) );
        }

        $post = get_post( $product_id );
        if ( $post ) {
            $content = str_replace( array_keys( $url_map ), array_values( $url_map ), (string) $post->post_content );
            $excerpt = str_replace( array_keys( $url_map ), array_values( $url_map ), (string) $post->post_excerpt );

            if ( $content !== $post->post_content || $excerpt !== $post->post_excerpt ) {
                wp_update_post(
                    wp_slash(
                        array(
                            'ID'           => $product_id,
                            'post_content' => $content,
                            'post_excerpt' => $excerpt,
                        )
                    )
                );
            }
        }
    }

    private static function replace_urls_recursive( $value, array $url_map ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                $value[ $key ] = self::replace_urls_recursive( $item, $url_map );
            }
            return $value;
        }

        if ( is_string( $value ) ) {
            return str_replace( array_keys( $url_map ), array_values( $url_map ), $value );
        }

        return $value;
    }

    private static function normalize_url_list( $raw ): array {
        if ( is_string( $raw ) ) {
            $decoded = json_decode( $raw, true );
            if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
                $raw = $decoded;
            } elseif ( false !== strpos( $raw, ',' ) ) {
                $raw = preg_split( '/\s*,\s*/', $raw );
            } elseif ( $raw ) {
                $raw = array( $raw );
            }
        }

        if ( ! is_array( $raw ) ) {
            return array();
        }

        $urls = array();
        foreach ( $raw as $url ) {
            $url = esc_url_raw( (string) $url );
            if ( $url && wp_http_validate_url( $url ) ) {
                $urls[] = $url;
            }
        }

        return array_values( array_unique( $urls ) );
    }

    private static function import_attachment( string $url, int $product_id ) {
        if ( ! wp_http_validate_url( $url ) ) {
            return new WP_Error( 'apt_invalid_media_url', __( 'The imported media URL is invalid.', 'advanced-product-tabs' ) );
        }

        $existing = get_posts(
            array(
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => self::SOURCE_META,
                'meta_value'     => $url,
            )
        );

        if ( $existing ) {
            return (int) $existing[0];
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $temporary_file = download_url( $url, 45 );
        if ( is_wp_error( $temporary_file ) ) {
            return $temporary_file;
        }

        $path     = (string) wp_parse_url( $url, PHP_URL_PATH );
        $filename = sanitize_file_name( wp_basename( $path ) );
        if ( ! $filename ) {
            $filename = 'imported-product-media.jpg';
        }

        $file_array = array(
            'name'     => $filename,
            'tmp_name' => $temporary_file,
        );

        $attachment_id = media_handle_sideload( $file_array, $product_id );
        if ( is_wp_error( $attachment_id ) ) {
            @unlink( $temporary_file );
            return $attachment_id;
        }

        update_post_meta( $attachment_id, self::SOURCE_META, $url );
        return (int) $attachment_id;
    }
}
