<?php
	/**
	 * Plugin Name:  WooCommerce Add to Cart Redirect
	 * Plugin URI:   https://wordpress.org/plugins/woo-cart-redirect-to-checkout-page/
	 * Description:  Redirect to checkout page or any other page after successfully product added to cart.
	 * Version:      2.0.0
	 * Author:       Emran Ahmed
	 * Text Domain:  woo-cart-redirect-to-checkout-page
	 * Requires at least: 4.5
	 * Tested up to: 4.9
	 * WC requires at least: 2.7
	 * WC tested up to: 3.3
	 * Domain Path:  /languages
	 * Author URI:   https://getwooplugins.com/
	 * License:      GPLv3
	 * License URI:  http://www.gnu.org/licenses/gpl-3.0.html
	 */
	
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
	
	if ( ! class_exists( 'Woo_Cart_Redirect_To_Checkout_Page' ) ):
		
		final class Woo_Cart_Redirect_To_Checkout_Page {
			
			protected $_version = '2.0.0';
			
			protected static $_instance = NULL;
			
			public static function instance() {
				if ( is_null( self::$_instance ) ) {
					self::$_instance = new self();
				}
				
				return self::$_instance;
			}
			
			public function __construct() {
				$this->constants();
				$this->hooks();
				do_action( 'woo_cart_redirect_to_checkout_page_loaded', $this );
			}
			
			public function constants() {
				$this->define( 'WOO_CRTCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
				$this->define( 'WOO_CRTCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
				$this->define( 'WOO_CRTCP_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
				$this->define( 'WOO_CRTCP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
				$this->define( 'WOO_CRTCP_PLUGIN_FILE', __FILE__ );
			}
			
			public function define( $name, $value, $case_insensitive = FALSE ) {
				if ( ! defined( $name ) ) {
					define( $name, $value, $case_insensitive );
				}
			}
			
			public function hooks() {
				
				// Init
				add_action( 'init', array( $this, 'language' ) );
				
				// Product Settings Option
				add_filter( 'woocommerce_product_settings', array( $this, 'settings' ) );
				
				// Cart Redirect
				add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'cart_redirect' ) );
				
				// Ajax Add to cart redirect
				
				if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
					add_filter( 'wc_add_to_cart_params', array( $this, 'add_to_cart_js_params' ) );
				}
				
				add_filter( 'woocommerce_get_script_data', array( $this, 'add_script_data' ), 10, 2 );
				
				// Plugin Row Meta
				add_filter( 'plugin_action_links_' . WOO_CRTCP_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
			}
			
			public function language() {
				load_plugin_textdomain( 'woo-cart-redirect-to-checkout-page', FALSE, WOO_CRTCP_PLUGIN_DIRNAME . '/languages' );
			}
			
			// @TODO: OLD WC
			public function add_to_cart_js_params( $data ) {
				
				if ( (bool) get_option( 'woo_cart_redirect_to_page', '' ) ) {
					$data[ 'cart_redirect_after_add' ] = (bool) get_option( 'woo_cart_redirect_to_page' ) ? 'yes' : 'no';
				}
				
				return $data;
			}
			
			public function add_script_data( $params, $handle ) {
				if ( 'wc-add-to-cart' == $handle ) {
					$params = array_merge( $params, array(
						'cart_redirect_after_add' => (bool) get_option( 'woo_cart_redirect_to_page' ) ? 'yes' : 'no'
					) );
				}
				
				return $params;
			}
			
			public function cart_redirect( $url ) {
				
				if ( (bool) get_option( 'woo_cart_redirect_to_page' ) ) {
					$url = get_permalink( get_option( 'woo_cart_redirect_to_page' ) );
				}
				
				return apply_filters( 'woo_cart_redirect_to_page', $url );
			}
			
			public function plugin_action_links( $links ) {
				
				if ( is_plugin_active( WOO_CRTCP_PLUGIN_BASENAME ) ) {
					$action_links = apply_filters( 'woo_cart_redirect_to_checkout_page_action_links', array(
						'settings' => sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', esc_url( add_query_arg( array(
							                                                                                       'page'    => 'wc-settings',
							                                                                                       'tab'     => 'products',
							                                                                                       'section' => 'display',
						                                                                                       ), admin_url( 'admin.php' ) ) ), esc_attr__( 'Settings', 'woo-cart-redirect-to-checkout-page' ) ),
					) );
					
					return array_merge( $action_links, $links );
				}
				
				return (array) $links;
			}
			
			public function settings( $settings ) {
				array_splice( $settings, 4, 0, array(
					array(
						'title'    => esc_html__( 'Add to cart redirect to', 'woo-cart-redirect-to-checkout-page' ),
						'id'       => 'woo_cart_redirect_to_page',
						'selected' => get_option( 'woocommerce_checkout_page_id' ),
						'type'     => 'single_select_page',
						'class'    => 'wc-enhanced-select-nostd',
						'css'      => 'min-width:300px;',
						'desc_tip' => esc_html__( 'After item added to cart page will redirect to a specific page.', 'woo-cart-redirect-to-checkout-page' ),
					)
				) );
				
				return $settings;
			}
		}
		
		function woo_cart_redirect_to_checkout_page() {
			return Woo_Cart_Redirect_To_Checkout_Page::instance();
		}
		
		add_action( 'plugins_loaded', 'woo_cart_redirect_to_checkout_page' );
	
	endif;