<?php
	/**
	 * Plugin Name:  WooCommerce Cart Redirect To Checkout Page
	 * Plugin URI:   https://wordpress.org/plugins/woo-cart-redirect-to-checkout-page/
	 * Description:  Redirect to checkout page after successfully product added to cart.
	 * Version:      1.0.1
	 * Author:       Emran Ahmed
	 * Author URI:   https://getwooplugins.com/
	 * License:      GPLv2.0+
	 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain:  woo-cart-redirect-to-checkout-page
	 * Domain Path:  /languages/
	 */
	
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
	
	if ( ! class_exists( 'Woo_Cart_Redirect_To_Checkout_Page' ) ):
		
		class Woo_Cart_Redirect_To_Checkout_Page {
			
			public function __construct() {
				$this->constants();
				$this->hooks();
				do_action( 'woo_cart_redirect_to_checkout_page_loaded', $this );
			}
			
			public function constants() {
				define( 'WOO_CRTCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
				define( 'WOO_CRTCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
				define( 'WOO_CRTCP_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
				define( 'WOO_CRTCP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
				define( 'WOO_CRTCP_PLUGIN_FILE', __FILE__ );
			}
			
			public function hooks() {
				
				// Init
				add_action( 'init', array( $this, 'init' ), 0 );
				
				// Product Settings Option
				add_filter( 'woocommerce_product_settings', array( $this, 'settings' ) );
				
				// Cart Redirect
				add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'cart_redirect' ) );
				
				// Ajax Add to cart redirect
				add_filter( 'wc_add_to_cart_params', array( $this, 'add_to_cart_js_params' ) );
				
				// Plugin Row Meta
				add_filter( 'plugin_action_links_' . WOO_CRTCP_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
			}
			
			public function init() {
				// Before init action.
				do_action( 'before_woo_cart_redirect_to_checkout_page_init' );
				load_plugin_textdomain( 'woo-cart-redirect-to-checkout-page', FALSE, WOO_CRTCP_PLUGIN_DIRNAME . '/languages' );
				do_action( 'woo_cart_redirect_to_checkout_page_init' );
			}
			
			public function add_to_cart_js_params( $data ) {
				
				if ( 'yes' === get_option( 'ea_woo_cart_redirect_checkout', 'yes' ) ) {
					$data[ 'cart_redirect_after_add' ] = get_option( 'ea_woo_cart_redirect_checkout', 'yes' );
				}
				
				return $data;
			}
			
			public function cart_redirect( $url ) {
				if ( 'yes' === get_option( 'ea_woo_cart_redirect_checkout', 'yes' ) ) {
					return wc_get_checkout_url();
				}
				
				return $url;
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
						'desc'     => esc_html__( 'Redirect to the checkout page after successful addition.', 'woo-cart-redirect-to-checkout-page' ),
						'id'       => 'ea_woo_cart_redirect_checkout',
						'default'  => 'yes',
						'type'     => 'checkbox',
						//'checkboxgroup' => '',
						'desc_tip' => __( 'This setting will overwrite "Redirect to the cart page after successful addition" settings. <br> Means it will redirect to checkout page rather than cart page after successful addition.', 'woo-cart-redirect-to-checkout-page' ),
					)
				) );
				
				return $settings;
				
			}
		}
		
		new Woo_Cart_Redirect_To_Checkout_Page();
	
	endif;