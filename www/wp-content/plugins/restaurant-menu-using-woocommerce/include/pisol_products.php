<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class pisol_products{

    private $show_image = false;
    private $columns = 4;
    private $top_cat;

    function __construct(){
        add_action( 'wp_ajax_nopriv_pisol_get_products', array($this,'pisol_get_product_from_cat' ));
        add_action( 'wp_ajax_pisol_get_products',  array($this,'pisol_get_product_from_cat'));
        
        add_action( 'wp_ajax_nopriv_pisol_search_product', array($this,'pisol_search_product' ));
        add_action( 'wp_ajax_pisol_search_product',  array($this,'pisol_search_product'));
        
        add_action( 'wp_ajax_nopriv_pisol_product', array($this,'pisol_product' ));
        add_action( 'wp_ajax_pisol_product',  array($this,'pisol_product'));
        
        
        add_filter( 'woocommerce_add_cart_item_data', array($this,'add_cart_item_data'), 10, 2 );
        add_filter( 'woocommerce_get_item_data', array($this,'get_item_data'), 10, 2 );
        add_action( 'woocommerce_before_calculate_totals', array($this,'add_custom_price') );
        add_action( 'woocommerce_new_order_item', array($this,'add_order_item_meta'), 10, 3 );
        
       

        /* Redirect single product page to cart page */
        add_action( 'template_redirect', array($this,'single_product_page_redirect'));
        add_action( 'template_redirect', array($this,'shop_page_redirect'));
        add_action( 'template_redirect', array($this,'category_page_redirect'));

        add_action('pisol_product_filter', array($this,'product_filter'),2);

        $this->show_image = apply_filters("pisol_rm_pro_pisol_show_image",false);
        if( $this->show_image ){
            $this->columns = 5;
        }else{
            $this->columns = 4;
        }
    }

    /* 
        Get product from woocommerce category 
    */
    function pisol_get_product_from_cat(){
        /*
            we only need integer value, and this function convert 
            all the input to int so even if some one places some wrong input
            it will get converted to int and no category found page will come
        */
        $load_variable = apply_filters('pisol_load_variable',1);
        if(empty($load_variable)){
            $product_type = array('simple');
        }else{
            $product_type = array('simple','variable');
        }

        $cat = intval($_POST['pisol_cat_id']);
        $this->top_cat = $cat;
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status'      => 'publish',
            'tax_query' => array(array(
                    'taxonomy' => 'product_cat',
                    'include_children' => false,
                    'terms' => array($cat)),
                    array(
                        'taxonomy' => 'product_type',
                        'field'    => 'slug',
                        'terms'    => $product_type, 
                    )
                    ),
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            );
        
        $loop = new WP_Query($args);

        $show_image = $this->show_image;
        $columns = $this->columns;

        include PISOL_RESTAURANT_MENU_PATH.'front/view/product-loop.php';
        wp_reset_postdata();

       

        if(isset($_POST['pisol_cat_id'])){
            exit(0);
        }
    }

    /*
    input: product_name
    output: product in tabe form
    */

    function pisol_search_product(){
        $product_name = esc_sql($_POST['product_name']);
        $args = array(
            'post_type' => 'product',
            's' => $product_name,
            'post_status' => 'publish',
            'orderby'     => 'title', 
            'order'       => 'ASC'        
        );

        $loop = new WP_Query($args);

        $show_image = $this->show_image;
        $columns = $this->columns;

        include PISOL_RESTAURANT_MENU_PATH.'front/view/product-loop.php';
        wp_reset_postdata();

       

        if(isset($_POST['product_name'])){
            exit(0);
        }
    }

    /*
        Input: product id
        Output: product layout for popup
    */
    function pisol_product(){
        $product = wc_get_product($_GET['product_id']);
        $categories = $product->get_category_ids();
        $img = $this->pisol_product_image_src($product->get_id(), $categories[0]);
        include PISOL_RESTAURANT_MENU_PATH.'front/view/product.php';
        if(isset($_GET['product_id'])){
            exit(0);
        }
    }

    /*
        Get Child categories for the parent category
    */
    function pisol_get_child_cat_products($parent_cat_id){
        $cat_object = new pisol_categories();
        $child_cats = $cat_object->get_child_categories($parent_cat_id);
        foreach($child_cats as $child_cat ){
            $child_cat_name = $cat_object->get_category_object($child_cat );
            $this->pisol_get_product_from_child_cat($child_cat, $child_cat_name->name);
        }
    }

    /*
        Child product loop
    */
    function pisol_get_product_from_child_cat($child_cat, $child_cat_name){
        $load_variable = apply_filters('pisol_load_variable',1);
        if(empty($load_variable)){
            $product_type = array('simple');
        }else{
            $product_type = array('simple','variable');
        }
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status'      => 'publish',
            'tax_query' => array(array(
                    'taxonomy' => 'product_cat',
                    'include_children' => false,
                    'terms' => array($child_cat)),
                    array(
                        'taxonomy' => 'product_type',
                        'field'    => 'slug',
                        'terms'    => $product_type, 
                    )
                
                    ),
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            );
        $cat = $child_cat;
        $loop = new WP_Query($args);

        $show_image = $this->show_image;
        $columns = $this->columns;

        $hide_empty_cat = get_option('pisol_hide_empty_cat',true);
        
        include PISOL_RESTAURANT_MENU_PATH.'front/view/child-cat-product-loop.php';
       

        wp_reset_postdata();
    }

    /*
        Get sidedishes for the product
    */
    function get_sidedishes($product_id, $cat){
       //print_r( get_post_meta($product_id, 'pisol_sidedishes',true));
       $sidedishes = json_decode( get_post_meta($product_id, 'pisol_sidedishes',true) );
       $group_count = 0;
       if(isset($sidedishes)):
       foreach($sidedishes as $side_dish){
           include PISOL_RESTAURANT_MENU_PATH.'front/view/product-sidedish-group.php';
           $group_count++;
       }
        endif;
    }

   

    /* adding extra data in cart */
    function add_cart_item_data( $cart_item, $product_id ){
        //print_r($_POST['dish']);
        if(isset($_POST['dish'])){
            foreach($_POST['dish'] as $group => $dishes){
                foreach($dishes as $key => $dish){
                    if($this->check_hash_of_sidedish($product_id, $dish['name'], $dish['price'], $dish['hash'])){
                        $cart_item['dishes'][$group][$key]['name'] = sanitize_text_field( $dish['name'] );
                        $cart_item['dishes'][$group][$key]['price'] = sanitize_text_field( $dish['price'] );
                        $cart_item['dishes'][$group][$key]['hash'] = sanitize_text_field( $dish['hash'] );
                    }
                    
                }
            }
        // print_r($cart_item);
        }
        return $cart_item;
    }

    /*
        This function checks the name and price of the sidedish submited by user 
        with the one stored in the system by help of hash
    */
    function check_hash_of_sidedish($product_id, &$name, &$price,$hash){
        $sidedishes = json_decode( get_post_meta($product_id, 'pisol_sidedishes',true) );
        $return = false;
        foreach($sidedishes as $side_dish){
            foreach($side_dish->sidedish as $dishes){
                if($dishes->hash == $hash){
                    $name = $dishes->name;
                    $price  = $dishes->price;
                    return true;
                }
            }
        }

        return $return;
    }

    /* display extra data in cart */
    function get_item_data( $other_data, $cart_item ) {
        $currency_position = get_option("woocommerce_currency_pos","left");
        //print_r( $cart_item['dishes']);
        if(isset($cart_item['dishes'])){
            foreach($cart_item['dishes'] as $key => $side_dishes){
                //print_r($side_dishes);
                foreach($side_dishes as $side_dish){
                   // print_r($side_dish);
                   if($side_dish['price'] == ""){
                        $price = 0;
                    }else{
                        $price = $side_dish['price'];
                    }
                    $other_data[] = array(
                        'name' =>  $side_dish['name'],
                        'value' => (($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): "").sanitize_text_field($price ).(($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): ""),
                    );
                    
                }
            }
        }
       
       
       
        return $other_data;
     
    }

    /* Modyfi price of item based on option selected in side dishes */
    function add_custom_price( $cart_object ) {
       
        foreach ( $cart_object->cart_contents as $key => $value ) {
           
            if(isset($value['dishes'])){
            
                $product = wc_get_product($value['data']->get_ID() );
                $new = floatval($product->get_price()) + floatval($this->calculate_side_total($value['dishes'] ));
               
              $value['data']->set_price($new);
            }
            
        }
    }

    function calculate_side_total($side_groups ){
        $side_total = 0;
        foreach($side_groups as $group){
            foreach($group as $dishes){
                if($dishes['price'] == ""){
                    $price = 0;
                }else{
                    $price = $dishes['price'];
                }
                $side_total = $side_total + $price; 
            }
        }
        return $side_total;
    }
    
    /*
        Adding sidedish detial to order 
    */
    function add_order_item_meta($item_id, $item, $order_id){
        $dishes = isset($item->legacy_values['dishes']) ? $item->legacy_values['dishes'] : false;
       if(empty($dishes)) return;

       foreach($dishes as   $group){
            foreach($group as $dish){
                if($dish['price'] ==""){
                    $dish['price'] = 0;
                }
                wc_add_order_item_meta( $item_id, $dish['name'], wc_price($dish['price']) );
            }
       }
    }

    /*
        Redirect single product of type simple, variable product will have there own page page to cart page
    */
    function single_product_page_redirect(){
        global $post;
        $cart_url = wc_get_cart_url();
        $redirect_option = apply_filters("pisol_rm_pro_pisol_product_redirect", true );
        if($redirect_option):
            if ( is_product() ){
                $product = wc_get_product( $post->ID );
                if( $product->is_type( 'simple' ) ){
                    wp_redirect( $cart_url ,301 );
                    exit;
                }
            }
        endif;
    }

    /*
        Redirect Show page to Menu / cart page
    */
    function shop_page_redirect(){
        $cart_url = wc_get_cart_url();
        $redirect_option = apply_filters( "pisol_rm_pro_pisol_shop_redirect", true );
        if($redirect_option):
            if ( is_shop() ){
                    wp_redirect( $cart_url ,301 );
                    exit;
            }
        endif;
    }

    /*
        Product category page redirect to Menu / Cart page
    */
    function category_page_redirect(){
        $cart_url = wc_get_cart_url();
        $redirect_option = apply_filters( "pisol_rm_pro_pisol_category_redirect", true );
        if($redirect_option):
            if ( is_product_category() ){
                    wp_redirect( $cart_url ,301 );
                    exit;
            }
        endif;
    }

    /* 
        Get type of food
    */
    function get_food_type($product_id){
        $type = get_post_meta($product_id, 'pisol_dish_type', 'none');

        if($type == 'none') return;
        
        $output = "<div class='dish_type'>";
        if($type == 'veg'){
            $output .= '<img src="'.PISOL_RESTAURANT_MENU_URL.'/front/view/img/veg.svg" class="pisol-icon" title="Veg">';
        }elseif($type == 'non_veg'){
            $output .= '<img src="'.PISOL_RESTAURANT_MENU_URL.'/front/view/img/nonveg.svg" class="pisol-icon" title="Non Veg">';
        }
        $output .= "</div>";
        return $output;
    }

    function food_type_class($product_id, $return){
        $type = get_post_meta($product_id, 'pisol_dish_type', 'none');

        if($return){
            return $type;
        }else{
            echo $type;
        }

    }

    function product_filter(){
        /* 
            diabling this since it was not working proper, when i added child category open and close
            and that function look more usefull then this filter, its js is still there in the js file
        */
        /*
        echo '
                <div class="pisol_filter">
                    <a class="type_filter active" data-class=".none" href="javascript:void(0);">'.__('All').'</a>
                    <a class="type_filter" data-class=".veg" href="javascript:void(0);">'.__('Veg').'</a>
                    <a class="type_filter" data-class=".non_veg" href="javascript:void(0);">'.__('Non Veg').'</a>
                </div>
            ';
            */
    }

    /*
        it retunr image src given product id and category id
    */

    function pisol_product_image_src($product, $cat){
        $show_image = $this->show_image;
        $use_herarchy = apply_filters( "pisol_rm_pro_pisol_herarchy_image", false );
        if($show_image):

            if($use_herarchy){
                $cat_img_id = get_term_meta( $cat, 'thumbnail_id', true );

                $top_cat_img_id = get_term_meta( $this->top_cat, 'thumbnail_id', true );

                if(get_the_post_thumbnail_url( $product, 'thumbnail' ) != ""){
                    $img_src = get_the_post_thumbnail_url( $product, 'thumbnail' );
                }elseif($cat_img_id != "" && $cat_img_id != 0){
                    $img_src = wp_get_attachment_image_src( $cat_img_id,'thumbnail',false )[0];
                }elseif($top_cat_img_id != "" && $top_cat_img_id != 0){
                    $img_src = wp_get_attachment_image_src( $top_cat_img_id,'thumbnail',false )[0];
                }else{
                    $img_src = wc_placeholder_img_src();
                }
            }else{
                if(get_the_post_thumbnail_url( $product, 'thumbnail' ) != ""){
                    $img_src = get_the_post_thumbnail_url( $product, 'thumbnail' );
                }else{
                    $img_src = wc_placeholder_img_src();
                }
            }
            return $img_src;    
            
        endif;
        return false;
    }

    static function pisol_get_product_image_src($product, $cat){
        $show_image = apply_filters("pisol_rm_pro_pisol_show_image",false);
        $use_herarchy = apply_filters( "pisol_rm_pro_pisol_herarchy_image", false );
        if($show_image):

            if($use_herarchy){
                $cat_img_id = get_term_meta( $cat, 'thumbnail_id', true );

                $top_cat_img_id = get_term_meta( $cat, 'thumbnail_id', true );

                if(get_the_post_thumbnail_url( $product, 'thumbnail' ) != ""){
                    $img_src = get_the_post_thumbnail_url( $product, 'thumbnail' );
                }elseif($cat_img_id != "" && $cat_img_id != 0){
                    $img_src = wp_get_attachment_image_src( $cat_img_id,'thumbnail',false )[0];
                }elseif($top_cat_img_id != "" && $top_cat_img_id != 0){
                    $img_src = wp_get_attachment_image_src( $top_cat_img_id,'thumbnail',false )[0];
                }else{
                    $img_src = wc_placeholder_img_src();
                }
            }else{
                if(get_the_post_thumbnail_url( $product, 'thumbnail' ) != ""){
                    $img_src = get_the_post_thumbnail_url( $product, 'thumbnail' );
                }else{
                    $img_src = wc_placeholder_img_src();
                }
            }
            return $img_src;    
            
        endif;
        return false;
    }


    function pisol_product_image($product, $cat){
        $img_src = $this->pisol_product_image_src($product->get_id(), $cat);
        if( $img_src != false):
            echo '<td class="pisol_prod_image">';
                echo '<img src="'.esc_url($img_src).'" width="150" class="img-fluid" alt="'.$product->get_name().'"/>';
            echo '</td>';
        endif;
    }

    static function price($product){
        if( $product->is_type('variable')){
            self::variablePrice($product);
        }else{
            self::simplePrice($product);
        }
    }

    static function variablePrice($product){
        $currency_position = get_option("woocommerce_currency_pos","left");
        $sale_price     =  $product->get_variation_sale_price( 'min', true );
        $regular_price  =  $product->get_variation_regular_price( 'max', true );
        echo (($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): "").$sale_price.(($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): "").' - '.(($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): "").$regular_price.(($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): "");
    }

    static function simplePrice($product){
        $currency_position = get_option("woocommerce_currency_pos","left");
        echo ($product->is_on_sale()) ? '<span class="strike">'.(($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): "").$product->get_regular_price().(($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): "").'</span><br>' : "";
        echo  (($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): "").'<span class="product_price" data-original="'.$product->get_price().'">'.$product->get_price().'</span>'.(($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): "");
    }

}
$var =   new pisol_products();

add_action( 'init', array( $var, '__construct' ) );