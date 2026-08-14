<?php

defined( 'ABSPATH' ) || exit;

final class TABORA_Enhancements {
    private static $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        add_action( 'admin_menu', array( $this, 'add_settings_page' ), 99 );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enhanced_admin_assets' ), 20 );
        add_filter( 'woocommerce_product_tabs', array( $this, 'apply_tab_controls' ), 999 );
        add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_product_tab_extras' ), 20 );
        add_action( 'add_meta_boxes_tabora_global_tab', array( $this, 'add_global_display_meta_box' ) );
        add_action( 'save_post_tabora_global_tab', array( $this, 'save_global_display_meta' ), 20 );
    }

    public function add_settings_page(): void {
        add_submenu_page(
            'woocommerce',
            __( 'Product Tabs Settings', 'tabora-product-tabs-for-woocommerce' ),
            __( 'Tab Settings', 'tabora-product-tabs-for-woocommerce' ),
            'manage_woocommerce',
            'tabora-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings(): void {
        register_setting(
            'tabora_settings_group',
            'tabora_settings',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'default'           => array(),
            )
        );
    }

    public function sanitize_settings( $input ): array {
        $input = is_array( $input ) ? $input : array();
        return array(
            'mobile_accordion' => ! empty( $input['mobile_accordion'] ) ? 1 : 0,
            'breakpoint'       => isset( $input['breakpoint'] ) ? max( 320, min( 1200, absint( $input['breakpoint'] ) ) ) : 768,
            'open_first'       => ! empty( $input['open_first'] ) ? 1 : 0,
            'hide_description' => ! empty( $input['hide_description'] ) ? 1 : 0,
            'hide_additional'  => ! empty( $input['hide_additional'] ) ? 1 : 0,
            'hide_reviews'     => ! empty( $input['hide_reviews'] ) ? 1 : 0,
            'tab_style'        => in_array( $input['tab_style'] ?? '', array( 'default', 'pills', 'underline' ), true ) ? $input['tab_style'] : 'default',
        );
    }

    private function settings(): array {
        return wp_parse_args(
            get_option( 'tabora_settings', array() ),
            array(
                'mobile_accordion' => 1,
                'breakpoint'       => 768,
                'open_first'       => 1,
                'hide_description' => 0,
                'hide_additional'  => 0,
                'hide_reviews'     => 0,
                'tab_style'        => 'default',
            )
        );
    }

    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }
        $settings = $this->settings();
        ?>
        <div class="wrap tabora-settings-wrap">
            <h1><?php esc_html_e( 'Tabora Product Tabs for WooCommerce', 'tabora-product-tabs-for-woocommerce' ); ?></h1>
            <p><?php esc_html_e( 'Responsive display and WooCommerce product-tab controls by Mahfuzar Rahman.', 'tabora-product-tabs-for-woocommerce' ); ?></p>
            <form method="post" action="options.php">
                <?php settings_fields( 'tabora_settings_group' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Mobile accordion', 'tabora-product-tabs-for-woocommerce' ); ?></th>
                        <td><label><input type="checkbox" name="tabora_settings[mobile_accordion]" value="1" <?php checked( $settings['mobile_accordion'] ); ?>> <?php esc_html_e( 'Convert product tabs into an accordion on smaller screens.', 'tabora-product-tabs-for-woocommerce' ); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tabora-breakpoint"><?php esc_html_e( 'Mobile breakpoint', 'tabora-product-tabs-for-woocommerce' ); ?></label></th>
                        <td><input id="tabora-breakpoint" type="number" min="320" max="1200" name="tabora_settings[breakpoint]" value="<?php echo esc_attr( $settings['breakpoint'] ); ?>"> px</td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Accordion state', 'tabora-product-tabs-for-woocommerce' ); ?></th>
                        <td><label><input type="checkbox" name="tabora_settings[open_first]" value="1" <?php checked( $settings['open_first'] ); ?>> <?php esc_html_e( 'Open the first tab by default.', 'tabora-product-tabs-for-woocommerce' ); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tabora-tab-style"><?php esc_html_e( 'Desktop style', 'tabora-product-tabs-for-woocommerce' ); ?></label></th>
                        <td>
                            <select id="tabora-tab-style" name="tabora_settings[tab_style]">
                                <option value="default" <?php selected( $settings['tab_style'], 'default' ); ?>><?php esc_html_e( 'Theme default', 'tabora-product-tabs-for-woocommerce' ); ?></option>
                                <option value="pills" <?php selected( $settings['tab_style'], 'pills' ); ?>><?php esc_html_e( 'Pills', 'tabora-product-tabs-for-woocommerce' ); ?></option>
                                <option value="underline" <?php selected( $settings['tab_style'], 'underline' ); ?>><?php esc_html_e( 'Underline', 'tabora-product-tabs-for-woocommerce' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Default tabs', 'tabora-product-tabs-for-woocommerce' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="tabora_settings[hide_description]" value="1" <?php checked( $settings['hide_description'] ); ?>> <?php esc_html_e( 'Hide Description', 'tabora-product-tabs-for-woocommerce' ); ?></label><br>
                            <label><input type="checkbox" name="tabora_settings[hide_additional]" value="1" <?php checked( $settings['hide_additional'] ); ?>> <?php esc_html_e( 'Hide Additional information', 'tabora-product-tabs-for-woocommerce' ); ?></label><br>
                            <label><input type="checkbox" name="tabora_settings[hide_reviews]" value="1" <?php checked( $settings['hide_reviews'] ); ?>> <?php esc_html_e( 'Hide Reviews', 'tabora-product-tabs-for-woocommerce' ); ?></label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function frontend_assets(): void {
        if ( ! is_product() ) {
            return;
        }
        $settings = $this->settings();
        wp_enqueue_style( 'tabora-frontend', TABORA_URL . 'assets/frontend.css', array(), TABORA_VERSION );
        wp_enqueue_script( 'tabora-frontend', TABORA_URL . 'assets/frontend.js', array( 'jquery' ), TABORA_VERSION, true );
        wp_localize_script(
            'tabora-frontend',
            'taboraFrontend',
            array(
                'accordion' => (bool) $settings['mobile_accordion'],
                'breakpoint' => absint( $settings['breakpoint'] ),
                'openFirst' => (bool) $settings['open_first'],
                'style' => sanitize_html_class( $settings['tab_style'] ),
            )
        );
    }

    public function enhanced_admin_assets(): void {
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->post_type, array( 'product', 'tabora_global_tab' ), true ) ) {
            return;
        }
        wp_enqueue_script( 'tabora-admin-enhancements', TABORA_URL . 'assets/admin-enhancements.js', array( 'jquery' ), TABORA_VERSION, true );
    }

    public function apply_tab_controls( array $tabs ): array {
        $settings = $this->settings();
        if ( ! empty( $settings['hide_description'] ) ) {
            unset( $tabs['description'] );
        }
        if ( ! empty( $settings['hide_additional'] ) ) {
            unset( $tabs['additional_information'] );
        }
        if ( ! empty( $settings['hide_reviews'] ) ) {
            unset( $tabs['reviews'] );
        }

        foreach ( $tabs as $key => &$tab ) {
            if ( 0 !== strpos( $key, 'tabora_' ) ) {
                continue;
            }
            $icon = '';
            $class = '';
            $visibility = 'all';

            if ( isset( $tab['tabora_id'] ) ) {
                $post_id = absint( $tab['tabora_id'] );
                $icon = sanitize_text_field( get_post_meta( $post_id, '_tabora_icon', true ) );
                $class = sanitize_html_class( get_post_meta( $post_id, '_tabora_css_class', true ) );
                $visibility = sanitize_key( get_post_meta( $post_id, '_tabora_visibility', true ) ?: 'all' );
            } elseif ( isset( $tab['tabora_icon'] ) ) {
                $icon = sanitize_text_field( $tab['tabora_icon'] );
                $class = sanitize_html_class( $tab['tabora_class'] ?? '' );
                $visibility = sanitize_key( $tab['tabora_visibility'] ?? 'all' );
            }

            if ( 'logged_in' === $visibility && ! is_user_logged_in() ) {
                unset( $tabs[ $key ] );
                continue;
            }
            if ( 'logged_out' === $visibility && is_user_logged_in() ) {
                unset( $tabs[ $key ] );
                continue;
            }
            if ( $icon ) {
                $tab['title'] = '<span class="tabora-tab-icon" aria-hidden="true">' . esc_html( $icon ) . '</span> ' . wp_kses_post( $tab['title'] );
            }
            if ( $class ) {
                $tab['tabora_class'] = $class;
            }
        }
        unset( $tab );
        return $tabs;
    }

    public function save_product_tab_extras( WC_Product $product ): void {
        if ( empty( $_POST['tabora_product_tabs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tabora_product_tabs_nonce'] ) ), 'tabora_save_product_tabs' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_product', $product->get_id() ) ) {
            return;
        }
        // Each extras field is sanitized below according to its expected content type.
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $raw = isset( $_POST['tabora_tabs'] ) && is_array( $_POST['tabora_tabs'] ) ? wp_unslash( $_POST['tabora_tabs'] ) : array();
        $saved = $product->get_meta( '_tabora_product_tabs', true );
        if ( ! is_array( $saved ) ) {
            return;
        }
        foreach ( $saved as $index => &$tab ) {
            $source = $raw[ $index ] ?? array();
            $tab['icon'] = sanitize_text_field( $source['icon'] ?? '' );
            $tab['css_class'] = sanitize_html_class( $source['css_class'] ?? '' );
            $visibility = sanitize_key( $source['visibility'] ?? 'all' );
            $tab['visibility'] = in_array( $visibility, array( 'all', 'logged_in', 'logged_out' ), true ) ? $visibility : 'all';
        }
        unset( $tab );
        $product->update_meta_data( '_tabora_product_tabs', $saved );
    }

    public function add_global_display_meta_box(): void {
        add_meta_box(
            'tabora-global-display',
            __( 'Display Options', 'tabora-product-tabs-for-woocommerce' ),
            array( $this, 'render_global_display_meta_box' ),
            'tabora_global_tab',
            'side',
            'default'
        );
    }

    public function render_global_display_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'tabora_save_global_display', 'tabora_global_display_nonce' );
        $icon = get_post_meta( $post->ID, '_tabora_icon', true );
        $class = get_post_meta( $post->ID, '_tabora_css_class', true );
        $visibility = get_post_meta( $post->ID, '_tabora_visibility', true ) ?: 'all';
        ?>
        <p><label for="tabora-icon"><strong><?php esc_html_e( 'Icon or emoji', 'tabora-product-tabs-for-woocommerce' ); ?></strong></label><br><input id="tabora-icon" type="text" class="widefat" name="tabora_icon" value="<?php echo esc_attr( $icon ); ?>" placeholder="✓"></p>
        <p><label for="tabora-css-class"><strong><?php esc_html_e( 'Custom CSS class', 'tabora-product-tabs-for-woocommerce' ); ?></strong></label><br><input id="tabora-css-class" type="text" class="widefat" name="tabora_css_class" value="<?php echo esc_attr( $class ); ?>"></p>
        <p><label for="tabora-visibility"><strong><?php esc_html_e( 'Visibility', 'tabora-product-tabs-for-woocommerce' ); ?></strong></label><br>
            <select id="tabora-visibility" class="widefat" name="tabora_visibility">
                <option value="all" <?php selected( $visibility, 'all' ); ?>><?php esc_html_e( 'Everyone', 'tabora-product-tabs-for-woocommerce' ); ?></option>
                <option value="logged_in" <?php selected( $visibility, 'logged_in' ); ?>><?php esc_html_e( 'Logged-in users', 'tabora-product-tabs-for-woocommerce' ); ?></option>
                <option value="logged_out" <?php selected( $visibility, 'logged_out' ); ?>><?php esc_html_e( 'Guests only', 'tabora-product-tabs-for-woocommerce' ); ?></option>
            </select>
        </p>
        <?php
    }

    public function save_global_display_meta( int $post_id ): void {
        if ( empty( $_POST['tabora_global_display_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tabora_global_display_nonce'] ) ), 'tabora_save_global_display' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }
        update_post_meta( $post_id, '_tabora_icon', sanitize_text_field( wp_unslash( $_POST['tabora_icon'] ?? '' ) ) );
        update_post_meta( $post_id, '_tabora_css_class', sanitize_html_class( wp_unslash( $_POST['tabora_css_class'] ?? '' ) ) );
        $visibility = sanitize_key( wp_unslash( $_POST['tabora_visibility'] ?? 'all' ) );
        update_post_meta( $post_id, '_tabora_visibility', in_array( $visibility, array( 'all', 'logged_in', 'logged_out' ), true ) ? $visibility : 'all' );
    }
}
