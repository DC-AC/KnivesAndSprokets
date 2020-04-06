<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>
<?php
class pisol_front{

    private $top_categories = array();

    function __construct(){
       

        add_action('pisol_product_category', array($this,'product_categories'),1);
        
        add_action('pisol_product_table', array($this,'product_table'));
        add_filter( 'woocommerce_locate_template', array($this,'pisol_woocommerce_locate_template'), 10, 3 );
        add_action( 'wp_enqueue_scripts', array($this,'enque_style') );
        add_action( 'wp_enqueue_scripts', array($this,'enque_script') );       

        add_filter( 'woocommerce_cart_item_thumbnail', array($this,'filter_woocommerce_cart_item_thumbnail'), 10, 3 ); 

        add_action( 'wp_enqueue_scripts', array($this,'inlineCss') );

    }

    /* 
        This function overwrite Cart template with its own template 
    */
    function pisol_woocommerce_locate_template($template, $template_name, $template_path){
            
            global $woocommerce;

            $value_theme_overwrite = apply_filters("pisol_rm_pro_theme_overwrite", false);

            $_template = $template;

            if ( ! $template_path ) 
            $template_path = $woocommerce->template_url;

            
            $plugin_path  = PISOL_RESTAURANT_MENU_PATH  . 'front/view/woocommerce/';

            // Look within passed path within the theme - this is priority
            /* 
                If we remove this code then the theme cart template wont be used at all
                with this line if cart template is there in the template then it will be used over the 
                plugin template

                Remove complete if loop and put $template = false; for free version
            */
            if($value_theme_overwrite){
                $template = locate_template(
                        array(
                        $template_path . $template_name,
                        $template_name
                        )
                    );
            }else{
                $template = false;
            }


        
            if( ! $template && file_exists( $plugin_path . $template_name ) )
                $template = $plugin_path . $template_name;
            
            if ( ! $template )
                $template = $_template;

            return $template;

    }

    /*
        Link style sheet to front part
    */
    function enque_style(){
        wp_enqueue_style( 'pisol_front_style', PISOL_RESTAURANT_MENU_URL.'front/view/css/style.css');
        wp_enqueue_style( 'pisol_magnific_popup', PISOL_RESTAURANT_MENU_URL.'front/view/css/magnific-popup.css');
        
        wp_enqueue_style( 'pisol_animate', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.0.0/animate.min.css');
        
        if(is_rtl()){
            wp_enqueue_style( 'pisol_front_style_rtl', PISOL_RESTAURANT_MENU_URL.'front/view/css/style-rtl.css'); 
        }
    }

    /*
        Link js to front part
    */
    function enque_script(){
        $categories = new pisol_categories();
        $this->top_categories = $categories->top_level_catagories();

        /*
            This check is needed, bec if we are not showing empty category and there are no product in the
            website then no top level category comes out and we get error, so we dont want to process if there is no category 
            coming out
        */
        if(!empty($this->top_categories)){
            $min_limit = apply_filters('pisol_enable_min_limit',false);
            if($min_limit){
            wp_enqueue_script( 'pisol_front_min', PISOL_RESTAURANT_MENU_URL.'front/view/js/min-selection.js',array('jquery'));
            }
            wp_enqueue_script( 'pisol_front_script', PISOL_RESTAURANT_MENU_URL.'front/view/js/script.js',array('jquery'));
            wp_enqueue_script( 'pisol_magnific_popup',PISOL_RESTAURANT_MENU_URL.'front/view/js/jquery.magnific-popup.min.js',array('jquery', 'pisol_front_script','wc-add-to-cart-variation'));

            if(apply_filters("pisol_rm_pro_pisol_product_popup",false)){
               
                $car_popup_caller = '
                jQuery(document).ready(function($){
                    $(document).on("click",".pisol_table .product_name a",function(e){
                        var product_id = $(this).data("id");
                
                        $.magnificPopup.open({
                            items: {
                            src: pisol.ajax_url+"?action=pisol_product&product_id="+product_id,
                            type: "ajax",
                            showCloseBtn: true,
                            },
                        });
                
                    });
                });
                ';
                wp_add_inline_script( 'pisol_magnific_popup', $car_popup_caller);
            }   
            $currency_position = get_option("woocommerce_currency_pos","left");
            wp_localize_script( 'pisol_front_script', 'pisol', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'currency' => (($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): ""),
            'currency_right' => (($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): ""),
            'default_cat'=> (isset($_GET['cat_id']) && term_exists((int)$_GET['cat_id'], 'product_cat') ) ? esc_html(esc_sql($_GET['cat_id'])) : get_option('pisol_default_cat',$this->top_categories[0]->term_id),
            'no_product_msg'=>__('There are no product of this type','pisol-restautant-menu') 
            ));
        }
        
    }

    /*
        This adds Product category on top of the product table
    */
    function product_categories(){
        if(!empty($this->top_categories)){
            $default_cat = (isset($_GET['cat_id']) && term_exists((int)$_GET['cat_id'], 'product_cat') ) ? esc_sql($_GET['cat_id']) : apply_filters('pisol_rm_pro_pisol_default_cat',$this->top_categories[0]->term_id);
            $this->top_categories = apply_filters('pisol_rm_pro_pisol_hide_cat', $this->top_categories);
            include PISOL_RESTAURANT_MENU_PATH.'front/view/button.php';
        }
    }

    /*
        This shows product in table form
    */
    function product_table(){		
        $this->product_loop();
    }

    function product_loop(){
      echo '<div class="pisol_search"><input type="text" id="pisol_product_search" name="pisol_product_search" placeholder="'.__('Product name to search', 'pisol-restautant-menu').'"/><button id="pisol_search_all_product" class="pisol_btn pisol_all">'.__("Search Product", 'pisol-restautant-menu').'</button></div><div id="pisol_product_table"></div>';
    }

    /* 
        return : product image src if not then false
        takes: product id
    */
    function get_product_image($product_id){
        $img_src = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
        if($img_src != ""){
            return $img_src;
        }
        return false;
    }

    /*
        take: product id
        return: product categories
    */
    function get_product_cat($product_id){
        $terms = get_the_terms ( $product_id, 'product_cat' );
        return $terms[0]->term_id;
    }

    /*
    take: cat_id
    return: src of cat or false
    */
    function cat_image($cat_id){
        $cat_img_id = get_term_meta( $cat_id, 'thumbnail_id', true );
        if($cat_img_id != "" && $cat_img_id != 0){
            return wp_get_attachment_image_src( $cat_img_id, 'thumbnail',false )[0];
        }
        return false;
    }

    /*
        Take: cat id
        return: parent cat id
    */
    function parent_cat($cat_id){
        $parentcats = get_ancestors($cat_id, 'product_cat');
        if(isset($parentcats[0])):
            return $parentcats[0];
        endif;
    }

    /* 
        Add product image to the cart counter part
        show: product image -> category image -> top category image -> type image -> placeholder
    */
    function filter_woocommerce_cart_item_thumbnail($product_get_image, $cart_item, $cart_item_key ){
        $class = 'woocommerce-placeholder wp-post-image';
        $output = "";

        $image = $this->get_product_image($cart_item['product_id']);

        if($image){
            $output = '<img src="'.esc_url($image).'" class="'.$class.'">';
            return $output;
        }

        $cat_id = $this->get_product_cat($cart_item['product_id']);

        $category_image = $this->cat_image($cat_id);
        
        if($category_image){
            $output = '<img src="'.esc_url($category_image).'" class="'.$class.'">';
            return $output;
        }
        
        $parent_cat_id = $this->parent_cat($cat_id);

        $parent_cat_image = $this->cat_image($parent_cat_id);

        if($parent_cat_image){
            $output = '<img src="'.esc_url($parent_cat_image).'" class="'.$class.'">';
            return $output;
        }

        $output = '<img src="'.esc_url(wc_placeholder_img_src()).'" class="'.$class.'">';
        return $output;
    }

    function inlineCss(){
        wp_register_style( 'pisol-rest-dummy-handle', false );
        wp_enqueue_style( 'pisol-rest-dummy-handle' );
        $layout = get_option('pisol_rest_layout','left-cart-right-product');
        switch($layout){
            case 'left-cart-right-product':
                $css = '
                /* Left: cart, Product: right */
                @media (max-width:980px){
                .pisol_cart{
                    grid-row:3/4;
                }
                }
                ';
            break;

            case 'left-product-right-cart':
                $css = '
                /* Left: product, right: cart */
                    
                    .pisol_cart {
                        grid-column: 2/3;
                        grid-row: 2/3;
                    }

                    .pisol_products{
                        grid-column: 1/2;
                        grid-row: 2/3;
                    }

                    @media (max-width:980px){
                        .pisol_cart{
                            grid-row:3/4;
                        }
                        }
                ';
            break;

            case 'product-top-cart-bottom':
                $css = '
                /* Left: product, right: cart */
                    
                    .pisol_products {
                        grid-column: 1/3;
                        grid-row: 2/3;
                    }

                    .pisol_cart{
                        grid-column: 1/3;
                        grid-row: 3/4;
                    }
                ';
            break;

            case 'product-bottom-cart-top':
                $css = '
                /* Left: product, right: cart */
                    
                    .pisol_cart {
                        grid-column: 1/3;
                        grid-row: 2/3;
                    }

                    .pisol_products{
                        grid-column: 1/3;
                        grid-row: 3/4;
                    }
                ';
            break;
        }
        wp_add_inline_style( 'pisol-rest-dummy-handle', $css );
    }

}

new pisol_front();

