<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class pisol_admin{

    private $active_tab = 'display'; 

    private $settings = array();

    private $cat_array = array();

    function __construct() {
        
        
        add_filter( 'plugin_row_meta', array($this,'register_plugins_links'), 10, 2);
        add_action( 'admin_menu', array($this,'plugin_menu') );
        
        
    }

   

   
    /* 
        Add a important link to product description, like setting page,
        Pro version buy link, Documentation link 
    */
    function register_plugins_links ($links, $file) {

        if ($file == PISOL_RESTAURANT_MENU_BASE) {
                $links[] = '<a href="https://woo-restaurant.com/">' . __('Documentation','pisol-restautant-menu') . '</a>';
                $links[] = '<a href="http://www.piwebsolution.com/product/restaurant-menu-using-woocommerce/">' . __('Buy Pro','pisol-restautant-menu') . '</a>';
        }

        return $links;
    }



    /* 
        Reguister admin menu 
    */
    function plugin_menu(){
        
        $menu = add_submenu_page('woocommerce', __('Restaurant Menu Setting','pisol-restautant-menu'), __('Restaurant Menu','pisol-restautant-menu'), 'manage_options', 'pisol-restaurant-menu',  array($this, 'restaurant_menu_option_page')  );

        add_action( 'load-' . $menu, array($this,'enqueue_style') );
    }

    /* 
        Restaurant menu setting page 
    */
    function restaurant_menu_option_page(){
        
        ?>
        <div class="container mt-2">
            <div class="row">
                    <div class="col-12">
                        <div class='bg-dark'>
                        <div class="row">
                            <div class="col-12 col-sm-2 py-2">
                            <a href="https://www.piwebsolution.com/" target="_blank"><img class="img-fluid ml-2" src="<?php echo PISOL_RESTAURANT_MENU_URL; ?>admin/view/img/pi-web-solution.svg"></a>
                            </div>
                            <div class="col-12 col-sm-10 d-flex text-center small">
                                <?php //do_action('pisol_dtt_tab'); ?>
                                <a class=" mr-0 ml-auto fon-weight-bold px-3 text-light d-flex align-items-center bg-primary border-left border-right" target="_blank" href="https://woo-restaurant.com/category/restaurant-menu-documentation/">
            Documentation 
        </a>
                            </div>
                        </div>
                        </div>
                    </div>
            </div>
            <div class="row">
                <div class="col-12">
                <div class="bg-light border p-3">
                    <div class="row">
                        <div class="col">
                        <?php do_action('pisol_restaurant_menu_tab_content'); ?>
                        </div>
                        <?php do_action('pisol_restaurant_menu_tab_msg'); ?>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <?php
    }


    /*
        Link style sheet to admin part
    */
    function enqueue_style(){
        wp_enqueue_style( 'pisol_admin_bootstrap', PISOL_RESTAURANT_MENU_URL.'admin/view/css/bootstrap.css');
        //wp_enqueue_style( 'pisol_admin_style', PISOL_RESTAURANT_MENU_URL.'admin/view/css/style.css');
    }

}

new pisol_admin();


