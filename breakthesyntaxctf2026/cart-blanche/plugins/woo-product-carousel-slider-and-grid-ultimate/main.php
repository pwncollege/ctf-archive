<?php
/*
Plugin Name: Product Carousel Slider & Grid Ultimate for WooCommerce
Plugin URI:  https://wpwax.com/product/woocommerce-product-carousel-slider-grid-ultimate-pro
Description: It is a fully responsive and mobile friendly WooCommerce Product Carousel, Slider and Grid plugin which comes with lots of features.
Version:     1.9.10
Author:      wpWax
Author URI:  https://wpwax.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages/
Text Domain: woo-product-carousel-slider-and-grid-ultimate
WC requires at least: 3.0
WC tested up to: 8.8
 */
defined('ABSPATH') || die('Direct access is not allow');

if( ! in_array('woocommerce-product-carousel-slider-grid-ultimate-pro/main.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {

    /**
     * Main Woocommerce Product Carousel Slider Ultimate class.
     *
     * @since 1.0.0
     */
    Final class Woocmmerce_Product_carousel_slider_ultimate
    {

        /**
        *
        * @since 1.0.0
        */
        private static $instance;

        /**
         * all metabox
         * @since 1.0.0
         */
        public $metabox;

        /**
         *custom post
        *@since 1.0.0
        */
        public $custom_post;

        /**
         *all shortcode
        *@since 1.0.0
        */
        public $shortcode;

        /**
         * Main Woocmmerce_Product_carousel_slider_ultimate Instance.
         *
         *
         * @since 1.0
         * @static
         * @static_var array $instance
         * @uses instanceof::adl_constants() Setup the constants needed.
         * @uses instanceof::wcpcsu_include() Include the required files.
         * @uses instanceof::wcpcsu_load_textdomain() load the language files.
         * @return object|Woocmmerce_Product_carousel_slider_ultimate The one true Woocmmerce_Product_carousel_slider_ultimate
         */
        public static function instance() {
            if(!isset(self::$instance) && !(self::$instance instanceof Woocmmerce_Product_carousel_slider_ultimate)) {
                self::$instance = new Woocmmerce_Product_carousel_slider_ultimate;
                //if woocmmerce plugin not activate
                if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                    add_action( 'admin_notices', array(self::$instance, 'WCPCSU_admin_notice') );
                }
                self::$instance->adl_constants();
                add_action('plugin_loaded',array( self::$instance,'wcpcsu_load_textdomain' ) );
                add_action('admin_enqueue_scripts',array(self::$instance, 'wcpcsu_enqueue_file'));
                add_action('template_redirect',array(self::$instance, 'template_enqueue_file'));
                add_action('admin_menu',array(self::$instance,'upgrade_to_pro'));

                add_action( 'elementor/preview/enqueue_styles', [ self::$instance, 'el_preview_style' ] );
                add_action( 'elementor/preview/enqueue_scripts', [ self::$instance, 'el_preview_script' ] );

                add_action( 'enqueue_block_editor_assets', [ self::$instance, 'enqueue_block_editor_assets' ] );

                if( empty( get_option('wcpcsu_dismiss_discount_notice') ) ) {
                    add_action( 'admin_notices', array( self::$instance, 'admin_notices') );
                }

                if( empty( get_option('migrate_serialize_to_json') ) ) {
                    add_action( 'admin_init', array( self::$instance, 'migrate_serialize_data') );
                }
                self::$instance->wcpcsu_include();
                self::$instance->custom_post = new Wcpcsu_Custom_Post();
                self::$instance->metabox = new Wcpcsu_Meta_Box();
                self::$instance->shortcode = new wcpcsu_Shortcode();
                // Initialize appsero tracking
                self::$instance->init_appsero();
                add_action( 'before_woocommerce_init', array( self::$instance, 'declare_woocommerce_feature_compatibility' ) );
            }

            return self::$instance;
        }
        
        public function declare_woocommerce_feature_compatibility() {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WCPCSU_FILE, true );
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WCPCSU_FILE, false );
            }
        }

        public function migrate_serialize_data() {
            
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $args = array(
                'post_type'      => WCPCSU_CUSTOM_POST_TYPE,
                'post_status'    => 'any',
                'posts_per_page' => -1,
            );

            $query = new WP_Query( $args );
            
            // Check if there are posts in the query
            if ( $query->have_posts() ) {
                // Loop through each post
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $wcpscu_data = get_post_meta( get_the_ID(), 'wcpscu', true );
                    
                    if ( ! empty( $wcpscu_data ) && ! is_json_encoded( $wcpscu_data ) ) {
                        $unserialized_data = unserialize( base64_decode( $wcpscu_data ) );
                        
                        $json_decode_data = Woocmmerce_Product_carousel_slider_ultimate::json_encoded( $unserialized_data );
                        update_post_meta( get_the_ID(), 'wcpscu', $json_decode_data );
                    }
                }

                // Restore the global post data
                wp_reset_postdata();
            }
            update_option( 'migrate_serialize_to_json', true );

        }

        public function admin_notices() {
            global $pagenow, $typenow;
            if ( 'index.php' == $pagenow || 'plugins.php' == $pagenow || 'wcpcsu-custom-post' == $typenow ) {
                require_once WCPCSU_INC_DIR . '/notice.php';
            }
        }

        /**
         * Setup plugin constants.
         * @access private
         * @since 1.0
         * @return void
         */
        public function adl_constants() {
            // Plugin Folder Path.
            if ( ! defined( 'WCPCSU_DIR' ) ) { define( 'WCPCSU_DIR', plugin_dir_path( __FILE__ ) ); }
            // Plugin Folder URL.
            if ( ! defined( 'WCPCSU_URL' ) ) { define( 'WCPCSU_URL', plugin_dir_url( __FILE__ ) ); }
            // Plugin Root File.
            if ( ! defined( 'WCPCSU_FILE' ) ) { define( 'WCPCSU_FILE', __FILE__ ); }
            if ( ! defined( 'WCPCSU_BASE' ) ) { define( 'WCPCSU_BASE', plugin_basename( __FILE__ ) ); }
            // Plugin Text domain File.
            if ( ! defined( 'WCPCSU_TEXTDOMAIN' ) ) { define( 'WCPCSU_TEXTDOMAIN', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); }
            // Plugin Includes Path
            if ( !defined('WCPCSU_INC_DIR') ) { define('WCPCSU_INC_DIR', WCPCSU_DIR.'includes/'); }
            // Plugin Language File Path
            if ( !defined('WCPCSU_LANG_DIR') ) { define('WCPCSU_LANG_DIR', dirname(plugin_basename( __FILE__ ) ) . '/languages'); }
            //custom post type id
            if ( !defined('WCPCSU_CUSTOM_POST_TYPE') ) { define('WCPCSU_CUSTOM_POST_TYPE', 'wcpcsu-custom-post'); }
        }

        public function upgrade_to_pro () {
            add_submenu_page( 'edit.php?post_type=wcpcsu-custom-post', esc_html__( 'Support', 'woocommerce-product-carousel-slider-and-grid-ultimate' ), esc_html__( 'Usage & Support', 'woocommerce-product-carousel-slider-and-grid-ultimate' ), 'manage_options', 'support', array( self::$instance, 'support_view' ) );
        }

        public function support_view () {
            include WCPCSU_INC_DIR . 'upgrade-pro.php';
        }

        /**
         * plugin text domain
         */
        public function wcpcsu_load_textdomain()
        {
            load_plugin_textdomain( 'woocommerce-product-carousel-slider-and-grid-ultimate', false, WCPCSU_LANG_DIR );
        }

        /**
         * include all require files
         *
         * @access private
         * @since 1.0.0
         * @return void
         */
        public function wcpcsu_include() {
            require_once WCPCSU_INC_DIR . 'gutenberg/init.php';
            require_once WCPCSU_INC_DIR . 'elementor/init.php';
            require_once WCPCSU_INC_DIR . 'helper-functions.php';
            wpcsu_load_dependencies( 'all', WCPCSU_INC_DIR . 'classes/' );
        }

        public function WCPCSU_admin_notice() { ?>
            <div class="error">
                <p>
                    <?php
                    printf('%s <strong>%s</strong>', esc_html__('WooCommerce plugin is not activated. Please install and activate it to use', 'woocommerce-product-carousel-slider-and-grid-ultimate'), esc_html__('WooCommerce Product Carousel Slider Ultimate Plugin', 'woocommerce-product-carousel-slider-and-grid-ultimate') );
                    ?>
                </p>
            </div>
        <?php
        }

        public function wcpcsu_enqueue_file () {
            global $typenow, $pagenow;

            if( $typenow == WCPCSU_CUSTOM_POST_TYPE ) {
                wp_enqueue_style('wcpcsu-admin-cmb2', WCPCSU_URL . 'admin/css/cmb2.min.css');
                wp_enqueue_style('wcpcsu-admin', WCPCSU_URL . 'admin/css/wcpcsu-admin.css');
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');
                wp_enqueue_script('wcpcsu-admin-js', WCPCSU_URL . 'admin/js/wcpcs-admin.js', array('jquery'));
            }

            if ( 'index.php' == $pagenow || 'plugins.php' == $pagenow || 'wcpcsu-custom-post' == $typenow ) {
                wp_enqueue_style('wcpcsu-notice', WCPCSU_URL . 'admin/css/wcpcsu-notice.css');
            }

        }

        public function template_enqueue_file () {
            wp_register_style( 'wcpcsu-main', WCPCSU_URL . 'assets/css/style.css' );
            wp_register_style( 'wcpcsu-swmodal', WCPCSU_URL . 'assets/css/swmodal.css' );
            wp_register_style( 'wcpcsu-swiper', WCPCSU_URL . 'assets/css/swiper-bundle.min.css' );
            wp_register_script( 'wcpcsu-main-js', WCPCSU_URL . 'assets/js/main.js' );
            wp_register_script( 'wcpcsu-swmodal-js', WCPCSU_URL . 'assets/js/swmodal.js' );
            wp_register_script( 'wcpcsu-swiper-js', WCPCSU_URL . 'assets/js/swiper-bundle.min.js' );

            wp_localize_script('wcpcsu-swmodal-js','wcpcsu_quick_view',array(
                'ajax_url'           => admin_url( 'admin-ajax.php' ),

            ));
        }
        
        public function el_preview_style() {
            wp_enqueue_style( 'wcpcsup-main', WCPCSU_URL . 'assets/css/style.css' );
            wp_enqueue_style( 'wcpcsup-swmodal', WCPCSU_URL . 'assets/css/swmodal.css' );
            wp_enqueue_style( 'wcpcsup-swiper', WCPCSU_URL . 'assets/css/swiper-bundle.min.css' );

		}

        public function el_preview_script() {
            wp_enqueue_script( 'wcpcsup-main-js', WCPCSU_URL . 'assets/js/main.js' );
            wp_enqueue_script( 'wcpcsup-swmodal-js', WCPCSU_URL . 'assets/js/swmodal.js' );
            wp_enqueue_script( 'wcpcsup-swiper-js', WCPCSU_URL . 'assets/js/swiper-bundle.min.js' );

            wp_localize_script('wcpcsup-swmodal-js','wcpcsu_quick_view',array(
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
        
            ));
            
		}

        public function enqueue_block_editor_assets() {
            wp_enqueue_style( 'wcpcsup-block-editor', WCPCSU_URL . 'admin/css/block-editor.css' );
        }

        /**
         * Encodes a PHP value into its JSON representation.
         * @param $data
         * @return string
         */
        public static function json_encoded( $data ) {
            return json_encode( $data );
        }

        /**
         * Decodes a JSON-encoded string into a PHP associative array.
         * @param string $data The JSON-encoded string to be decoded.
         * @return array Returns the decoded PHP associative array on success, or an empty array on failure.
         */
        public static function json_decoded( $data ) {
        
            $decoded_data = json_decode( $data, true );

            
            if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded_data ) ) {
                return $decoded_data;
            } else {
                return array();
            }
        }

        /**
         * Initialize appsero tracking.
         *
         * @see https://github.com/Appsero/client
         *
         * @return void
         */
        public function init_appsero() {
            if ( ! class_exists( '\Appsero\Client' ) ) {
                require_once WCPCSU_INC_DIR . 'appsero/src/Client.php';
            }

            $client = new \Appsero\Client( 'a39a0a19-5945-4527-84b0-a13bcfac1faa', 'Product Carousel Slider & Grid Ultimate for WooCommerce', __FILE__ );

            // Active insights
            $client->insights()->init();
        }

    } //end of class

    function WCPCSU() {
        return Woocmmerce_Product_carousel_slider_ultimate::instance();
    }

    // Get WCPCSU ( Woocommerce Product Carousel Slider Ultimate plugin ) Running.
    if( ! class_exists('Woocmmerce_Product_carousel_slider_ultimate_Pro') ){
        WCPCSU();
    }
    function wpcsu_image_cropping( $attachmentId, $width, $height, $crop = true, $quality = 100 )
    {
        $resizer = new Wpcsu_Image_Resizer( $attachmentId );

        return $resizer->resize( $width, $height, $crop, $quality );
    }

}


