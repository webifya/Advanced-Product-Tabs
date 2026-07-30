<?php

defined( 'ABSPATH' ) || exit;

final class WCT_Plugin {
    private static $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );

        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }

        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'register_global_tab_type' ) );
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_data_tab' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'render_product_panel' ) );
        add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_product_tabs' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_filter( 'woocommerce_product_tabs', array( $this, 'filter_product_tabs' ), 98 );
        add_filter( 'manage_wct_global_tab_posts_columns', array( $this, 'global_tab_columns' ) );
        add_action( 'manage_wct_global_tab_posts_custom_column', array( $this, 'global_tab_column_content' ), 10, 2 );
        add_action( 'add_meta_boxes_wct_global_tab', array( $this, 'add_global_tab_meta_box' ) );
        add_action( 'save_post_wct_global_tab', array( $this, 'save_global_tab_meta' ) );
    }

    public static function activate(): void {
        if ( ! class_exists( 'WooCommerce' ) ) {
            deactivate_plugins( plugin_basename( WCT_FILE ) );
            wp_die( esc_html__( 'WooCommerce Custom Tabs requires WooCommerce to be installed and active.', 'woocommerce-custom-tabs' ) );
        }
        self::instance()->register_global_tab_type();
        flush_rewrite_rules();
    }

    public function declare_compatibility(): void {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WCT_FILE, true );
        }
    }

    public function load_textdomain(): void {
        load_plugin_textdomain( 'woocommerce-custom-tabs', false, dirname( plugin_basename( WCT_FILE ) ) . '/languages' );
    }

    public function woocommerce_missing_notice(): void {
        if ( current_user_can( 'activate_plugins' ) ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'WooCommerce Custom Tabs requires WooCommerce to be installed and active.', 'woocommerce-custom-tabs' ) . '</p></div>';
        }
    }

    public function register_global_tab_type(): void {
        register_post_type(
            'wct_global_tab',
            array(
                'labels' => array(
                    'name'          => __( 'Product Tabs', 'woocommerce-custom-tabs' ),
                    'singular_name' => __( 'Product Tab', 'woocommerce-custom-tabs' ),
                    'add_new_item'  => __( 'Add New Product Tab', 'woocommerce-custom-tabs' ),
                    'edit_item'     => __( 'Edit Product Tab', 'woocommerce-custom-tabs' ),
                ),
                'public'              => false,
                'show_ui'             => true,
                'show_in_menu'        => 'woocommerce',
                'show_in_rest'        => true,
                'supports'            => array( 'title', 'editor', 'page-attributes' ),
                'capability_type'      => 'product',
                'map_meta_cap'         => true,
                'menu_position'        => 58,
                'exclude_from_search'  => true,
            )
        );
    }

    public function add_product_data_tab( array $tabs ): array {
        $tabs['wct_custom_tabs'] = array(
            'label'    => __( 'Custom Tabs', 'woocommerce-custom-tabs' ),
            'target'   => 'wct_custom_tabs_panel',
            'class'    => array(),
            'priority' => 85,
        );
        return $tabs;
    }

    public function render_product_panel(): void {
        global $post;
        $tabs = get_post_meta( $post->ID, '_wct_product_tabs', true );
        $tabs = is_array( $tabs ) ? $tabs : array();
        wp_nonce_field( 'wct_save_product_tabs', 'wct_product_tabs_nonce' );
        ?>
        <div id="wct_custom_tabs_panel" class="panel woocommerce_options_panel hidden">
            <div class="wct-panel-header">
                <p><?php esc_html_e( 'Create tabs that appear only on this product. Drag rows to reorder them.', 'woocommerce-custom-tabs' ); ?></p>
                <button type="button" class="button button-primary wct-add-tab"><?php esc_html_e( 'Add Tab', 'woocommerce-custom-tabs' ); ?></button>
            </div>
            <div class="wct-tab-list">
                <?php foreach ( $tabs as $index => $tab ) : ?>
                    <?php $this->render_tab_row( (int) $index, $tab ); ?>
                <?php endforeach; ?>
            </div>
            <script type="text/html" id="tmpl-wct-tab-row">
                <?php $this->render_tab_row( '{{{data.index}}}', array() ); ?>
            </script>
        </div>
        <?php
    }

    private function render_tab_row( $index, array $tab ): void {
        $title    = isset( $tab['title'] ) ? $tab['title'] : '';
        $content  = isset( $tab['content'] ) ? $tab['content'] : '';
        $priority = isset( $tab['priority'] ) ? absint( $tab['priority'] ) : 50;
        $enabled  = ! isset( $tab['enabled'] ) || ! empty( $tab['enabled'] );
        ?>
        <div class="wct-tab-row">
            <div class="wct-tab-row-bar">
                <span class="dashicons dashicons-move wct-drag"></span>
                <strong class="wct-row-title"><?php echo esc_html( $title ?: __( 'New Tab', 'woocommerce-custom-tabs' ) ); ?></strong>
                <button type="button" class="button-link wct-toggle"><?php esc_html_e( 'Expand/Collapse', 'woocommerce-custom-tabs' ); ?></button>
                <button type="button" class="button-link-delete wct-remove"><?php esc_html_e( 'Remove', 'woocommerce-custom-tabs' ); ?></button>
            </div>
            <div class="wct-tab-fields">
                <p class="form-field">
                    <label><?php esc_html_e( 'Tab title', 'woocommerce-custom-tabs' ); ?></label>
                    <input type="text" class="wct-title" name="wct_tabs[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>">
                </p>
                <p class="form-field">
                    <label><?php esc_html_e( 'Priority', 'woocommerce-custom-tabs' ); ?></label>
                    <input type="number" min="1" max="999" name="wct_tabs[<?php echo esc_attr( $index ); ?>][priority]" value="<?php echo esc_attr( $priority ); ?>">
                    <span class="description"><?php esc_html_e( 'Lower numbers appear first. Description is normally 10, Additional information 20, Reviews 30.', 'woocommerce-custom-tabs' ); ?></span>
                </p>
                <p class="form-field wct-content-field">
                    <label><?php esc_html_e( 'Content', 'woocommerce-custom-tabs' ); ?></label>
                    <textarea rows="8" name="wct_tabs[<?php echo esc_attr( $index ); ?>][content]" placeholder="<?php esc_attr_e( 'HTML and shortcodes are supported.', 'woocommerce-custom-tabs' ); ?>"><?php echo esc_textarea( $content ); ?></textarea>
                </p>
                <p class="form-field">
                    <label><?php esc_html_e( 'Enabled', 'woocommerce-custom-tabs' ); ?></label>
                    <input type="hidden" name="wct_tabs[<?php echo esc_attr( $index ); ?>][enabled]" value="0">
                    <input type="checkbox" name="wct_tabs[<?php echo esc_attr( $index ); ?>][enabled]" value="1" <?php checked( $enabled ); ?>>
                </p>
            </div>
        </div>
        <?php
    }

    public function save_product_tabs( WC_Product $product ): void {
        if ( empty( $_POST['wct_product_tabs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wct_product_tabs_nonce'] ) ), 'wct_save_product_tabs' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_product', $product->get_id() ) ) {
            return;
        }

        $raw_tabs = isset( $_POST['wct_tabs'] ) && is_array( $_POST['wct_tabs'] ) ? wp_unslash( $_POST['wct_tabs'] ) : array();
        $tabs     = array();
        foreach ( $raw_tabs as $raw_tab ) {
            $title = isset( $raw_tab['title'] ) ? sanitize_text_field( $raw_tab['title'] ) : '';
            if ( '' === $title ) {
                continue;
            }
            $tabs[] = array(
                'title'    => $title,
                'content'  => isset( $raw_tab['content'] ) ? wp_kses_post( $raw_tab['content'] ) : '',
                'priority' => isset( $raw_tab['priority'] ) ? max( 1, min( 999, absint( $raw_tab['priority'] ) ) ) : 50,
                'enabled'  => ! empty( $raw_tab['enabled'] ) ? 1 : 0,
            );
        }
        $product->update_meta_data( '_wct_product_tabs', $tabs );
    }

    public function admin_assets( string $hook ): void {
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->post_type, array( 'product', 'wct_global_tab' ), true ) ) {
            return;
        }
        wp_enqueue_style( 'wct-admin', WCT_URL . 'assets/admin.css', array(), WCT_VERSION );
        wp_enqueue_script( 'wct-admin', WCT_URL . 'assets/admin.js', array( 'jquery', 'jquery-ui-sortable', 'wp-util' ), WCT_VERSION, true );
    }

    public function filter_product_tabs( array $tabs ): array {
        global $product;
        if ( ! $product instanceof WC_Product ) {
            return $tabs;
        }

        foreach ( $this->get_global_tabs_for_product( $product ) as $global_tab ) {
            $key = 'wct_global_' . $global_tab->ID;
            $tabs[ $key ] = array(
                'title'    => get_the_title( $global_tab ),
                'priority' => max( 1, absint( get_post_meta( $global_tab->ID, '_wct_priority', true ) ?: $global_tab->menu_order ?: 50 ) ),
                'callback' => array( $this, 'render_global_tab' ),
                'wct_id'   => $global_tab->ID,
            );
        }

        $product_tabs = $product->get_meta( '_wct_product_tabs', true );
        if ( is_array( $product_tabs ) ) {
            foreach ( $product_tabs as $index => $tab ) {
                if ( empty( $tab['enabled'] ) || empty( $tab['title'] ) ) {
                    continue;
                }
                $key = 'wct_product_' . $product->get_id() . '_' . absint( $index );
                $tabs[ $key ] = array(
                    'title'       => sanitize_text_field( $tab['title'] ),
                    'priority'    => isset( $tab['priority'] ) ? absint( $tab['priority'] ) : 50,
                    'callback'    => array( $this, 'render_product_tab' ),
                    'wct_content' => isset( $tab['content'] ) ? $tab['content'] : '',
                );
            }
        }
        return $tabs;
    }

    private function get_global_tabs_for_product( WC_Product $product ): array {
        $posts = get_posts(
            array(
                'post_type'      => 'wct_global_tab',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
                'no_found_rows'  => true,
            )
        );
        $matched = array();
        foreach ( $posts as $post ) {
            $scope = get_post_meta( $post->ID, '_wct_scope', true ) ?: 'all';
            $ids   = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_wct_assignment_ids', true ) ) );
            if ( 'all' === $scope ) {
                $matched[] = $post;
            } elseif ( 'products' === $scope && in_array( $product->get_id(), $ids, true ) ) {
                $matched[] = $post;
            } elseif ( 'categories' === $scope && array_intersect( $ids, $product->get_category_ids() ) ) {
                $matched[] = $post;
            } elseif ( 'tags' === $scope && array_intersect( $ids, $product->get_tag_ids() ) ) {
                $matched[] = $post;
            }
        }
        return $matched;
    }

    public function render_global_tab( string $key, array $tab ): void {
        $post_id = isset( $tab['wct_id'] ) ? absint( $tab['wct_id'] ) : 0;
        $content = $post_id ? get_post_field( 'post_content', $post_id ) : '';
        echo wp_kses_post( do_shortcode( wpautop( $content ) ) );
    }

    public function render_product_tab( string $key, array $tab ): void {
        $content = isset( $tab['wct_content'] ) ? $tab['wct_content'] : '';
        echo wp_kses_post( do_shortcode( wpautop( $content ) ) );
    }

    public function add_global_tab_meta_box(): void {
        add_meta_box( 'wct_assignment', __( 'Tab Display Settings', 'woocommerce-custom-tabs' ), array( $this, 'render_global_tab_meta_box' ), 'wct_global_tab', 'side', 'default' );
    }

    public function render_global_tab_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'wct_save_global_tab', 'wct_global_tab_nonce' );
        $scope    = get_post_meta( $post->ID, '_wct_scope', true ) ?: 'all';
        $ids      = implode( ',', array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_wct_assignment_ids', true ) ) ) );
        $priority = absint( get_post_meta( $post->ID, '_wct_priority', true ) ?: 50 );
        ?>
        <p><label for="wct_scope"><strong><?php esc_html_e( 'Display on', 'woocommerce-custom-tabs' ); ?></strong></label></p>
        <select name="wct_scope" id="wct_scope" class="widefat">
            <option value="all" <?php selected( $scope, 'all' ); ?>><?php esc_html_e( 'All products', 'woocommerce-custom-tabs' ); ?></option>
            <option value="products" <?php selected( $scope, 'products' ); ?>><?php esc_html_e( 'Selected product IDs', 'woocommerce-custom-tabs' ); ?></option>
            <option value="categories" <?php selected( $scope, 'categories' ); ?>><?php esc_html_e( 'Selected category IDs', 'woocommerce-custom-tabs' ); ?></option>
            <option value="tags" <?php selected( $scope, 'tags' ); ?>><?php esc_html_e( 'Selected tag IDs', 'woocommerce-custom-tabs' ); ?></option>
        </select>
        <p><label for="wct_assignment_ids"><strong><?php esc_html_e( 'Assignment IDs', 'woocommerce-custom-tabs' ); ?></strong></label></p>
        <input type="text" class="widefat" id="wct_assignment_ids" name="wct_assignment_ids" value="<?php echo esc_attr( $ids ); ?>" placeholder="12, 34, 56">
        <p class="description"><?php esc_html_e( 'Enter comma-separated product, category, or tag IDs. Ignored when displaying on all products.', 'woocommerce-custom-tabs' ); ?></p>
        <p><label for="wct_priority"><strong><?php esc_html_e( 'Priority', 'woocommerce-custom-tabs' ); ?></strong></label></p>
        <input type="number" class="widefat" min="1" max="999" id="wct_priority" name="wct_priority" value="<?php echo esc_attr( $priority ); ?>">
        <?php
    }

    public function save_global_tab_meta( int $post_id ): void {
        if ( empty( $_POST['wct_global_tab_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wct_global_tab_nonce'] ) ), 'wct_save_global_tab' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_product', $post_id ) ) {
            return;
        }
        $allowed = array( 'all', 'products', 'categories', 'tags' );
        $scope   = isset( $_POST['wct_scope'] ) ? sanitize_key( wp_unslash( $_POST['wct_scope'] ) ) : 'all';
        $scope   = in_array( $scope, $allowed, true ) ? $scope : 'all';
        $ids_raw = isset( $_POST['wct_assignment_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['wct_assignment_ids'] ) ) : '';
        $ids     = array_values( array_unique( array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', $ids_raw ) ) ) ) );
        $priority = isset( $_POST['wct_priority'] ) ? max( 1, min( 999, absint( $_POST['wct_priority'] ) ) ) : 50;
        update_post_meta( $post_id, '_wct_scope', $scope );
        update_post_meta( $post_id, '_wct_assignment_ids', $ids );
        update_post_meta( $post_id, '_wct_priority', $priority );
    }

    public function global_tab_columns( array $columns ): array {
        $columns['wct_scope']    = __( 'Display', 'woocommerce-custom-tabs' );
        $columns['wct_priority'] = __( 'Priority', 'woocommerce-custom-tabs' );
        return $columns;
    }

    public function global_tab_column_content( string $column, int $post_id ): void {
        if ( 'wct_scope' === $column ) {
            echo esc_html( ucfirst( get_post_meta( $post_id, '_wct_scope', true ) ?: 'all' ) );
        }
        if ( 'wct_priority' === $column ) {
            echo esc_html( absint( get_post_meta( $post_id, '_wct_priority', true ) ?: 50 ) );
        }
    }
}
