<?php
/*
Plugin Name: Restaurant Menu using WooCommerce
Plugin URI: https://woo-restaurant.com/
Description: Online Food ordering made easy, just install and your online food business is ready
Version: 4.3.6
Author: PI Websolution
Author URI: piwebsolution.com
Text Domain: pisol-restautant-menu
Domain Path: /languages
WC tested up to: 4.0.1
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('PISOL_RESTAURANT_MENU_URL', plugin_dir_url(__FILE__));
define('PISOL_RESTAURANT_MENU_PATH', plugin_dir_path( __FILE__ ));
define('PISOL_RESTAURANT_MENU_BASE', plugin_basename(__FILE__));
define('PISOL_RESTAURANT_MENU_PRICE', '$25');
define('PISOL_RESTAURANT_MENU_BUY_URL', 'https://www.piwebsolution.com/cart/?add-to-cart=574&variation_id=675');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/* 
    Making sure woocommerce is there 
*/
if(!is_plugin_active( 'woocommerce/woocommerce.php')){
    function pisol_rm_my_error_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'Please Install and Activate WooCommerce plugin, without that this plugin cant work', 'pisol-restautant-menu' ); ?></p>
        </div>
        <?php
       
    }
    add_action( 'admin_notices', 'pisol_rm_my_error_notice' );
    deactivate_plugins(plugin_basename(__FILE__));
    return;
}



require_once( PISOL_RESTAURANT_MENU_PATH . 'include/pisol_products.php');
require_once( PISOL_RESTAURANT_MENU_PATH . 'include/pisol_categories.php');
require_once( PISOL_RESTAURANT_MENU_PATH . 'include/pisol.class.form.php');
require_once( PISOL_RESTAURANT_MENU_PATH . 'include/pisol.class.promotion.php');
require_once( PISOL_RESTAURANT_MENU_PATH . 'admin/meta/pisol_admin_meta.php');
require_once( PISOL_RESTAURANT_MENU_PATH . 'quickview/class.frontend.php');

add_action( 'plugins_loaded', 'pisol_load_language' );
function pisol_load_language(){
    load_plugin_textdomain( 'pisol-restautant-menu', false, basename( dirname( __FILE__ ) ) . '/languages'  );
}

if(is_admin() ){
    require_once( PISOL_RESTAURANT_MENU_PATH . 'admin/pisol_admin.php');
}else{
    require_once( PISOL_RESTAURANT_MENU_PATH . 'front/pisol_front.php');
}

function pisol_prm_plugin_link( $links ) {
	$links = array_merge( array(
        '<a href="' . esc_url( admin_url( '/admin.php?page=pisol-restaurant-menu' ) ) . '">' . __( 'Settings' ) . '</a>',
        '<a style="color:#0a9a3e; font-weight:bold;" target="_blank" href="' . esc_url(PISOL_RESTAURANT_MENU_BUY_URL) . '">' . __( 'Buy PRO Version' ) . '</a>'
	), $links );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pisol_prm_plugin_link' );

if (!class_exists('pisol_restaurant_menu_pro_option')) {
    require_once( PISOL_RESTAURANT_MENU_PATH . 'admin/pisol_restaurant_menu_option.php');
    add_action('admin_init', 'pi_resturant_menu_free_option');
    function pi_resturant_menu_free_option(){
        update_option('woocommerce_enable_ajax_add_to_cart','yes' );
        new pisol_restaurant_menu_option();
    }
}