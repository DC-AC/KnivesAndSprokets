<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

?>
<?php

class pisol_admin_meta{

    private $meta_values;

    function __construct(){
        add_action( 'save_post_product',  array($this,'product_save' ),10,2);
        add_action( 'add_meta_boxes_product', array($this,'register_metabox') ); 
        add_filter( 'woocommerce_product_data_tabs',array($this, 'food_types'),10,1 );
        add_action('woocommerce_product_data_panels', array($this, 'woocom_custom_product_data_fields'));
        add_action( 'woocommerce_process_product_meta_simple', array($this,'woocom_save_proddata_custom_fields'),10,1 );
    }

    function register_metabox(){  
        add_meta_box(
            'pisol-sidedish',
            __( 'Side Dishes', 'pisol-restautant-menu' ),
            array($this,'metabox_callback'),
            null,
            'advanced',
            'high',
            null
        );
    }

   

    function metabox_callback(){
        global $post;
        $values = get_post_meta( $post->ID, 'pisol_sidedishes',true );
        $min_limit = apply_filters('pisol_enable_min_limit',false);
        wp_enqueue_script( 'sidedish', PISOL_RESTAURANT_MENU_URL.'admin/view/js/sidedish.js', array('jquery'));
        wp_enqueue_style( 'sidedish', PISOL_RESTAURANT_MENU_URL.'admin/view/css/sidedish.css');
        wp_localize_script( 'sidedish', 'pi_restaurant', array('is_pro'=>$min_limit) );
        ?>
        <div class="show_if_simple">
        <button class="button show_if_simple" id="pisol_add_side_dish_group">Add Side Dish Group</button>
        
            <?php 
            if(isset($values) && $values != "null"){
                
                $this->meta_values = json_decode($values);
                //print_r($this->meta_values);
                $this->side_dish_groups(); 
                
            }else{
                echo '<div id="pisol_sidedish_group_container" data-group-counter="0" ></div>';
            }
            ?>
        </div>
        <div class="hide_if_simple">
            <strong>Side dishes can only be added in Simple product</strong>
        </div>
        <?php
    }

    function product_save($post_id){
       // Bail if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        
        if(isset($_POST['woocommerce_quick_edit_nonce'])){

            if (wp_verify_nonce($_POST['woocommerce_quick_edit_nonce'], 'woocommerce_quick_edit_nonce')){
                return;
            }
        }

        if(!isset($_POST['sidedish'])){
            delete_post_meta( $post_id,'pisol_sidedishes');
            return ;
        } 

        
        

        $one_step = wp_unslash($_POST['sidedish']);
        $sd_input = $this->recursive_sanitize_text_field($one_step);
        $before_final = $this->remove_empty($sd_input);
        $final = json_encode($before_final, JSON_UNESCAPED_UNICODE);
        update_post_meta( $post_id,'pisol_sidedishes', $final);
        
    }

    /**
     * Recursive sanitation for an array
     * 
     * @param $array
     *
     * @return mixed
     */
    function recursive_sanitize_text_field($array) {

        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = $this->recursive_sanitize_text_field($value);
            }
            else {
                $value = filter_var( $value ,  FILTER_SANITIZE_STRING  );
            }
        }

        return $array;
    }

    function woocom_save_proddata_custom_fields($post_id){
         // Bail if we're doing an auto save
         if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

         if(isset($_POST['woocommerce_quick_edit_nonce'])){

            if (wp_verify_nonce($_POST['woocommerce_quick_edit_nonce'], 'woocommerce_quick_edit_nonce')){
                return;
            }
        }
        
         if(!isset($_POST['pisol_dish_type'])) return ;
         
        
        
         
        $dish_type = 'none';

        /*
            I am just checking the input value, 
            i am not storing this user input in the system or using it in DB query
            thats why no filtes is applied
        */
        if($_POST['pisol_dish_type'] == 'non_veg'){
            $dish_type = 'non_veg';
        }

        if($_POST['pisol_dish_type'] == 'veg'){
            $dish_type = 'veg';
        }
        
        update_post_meta( $post_id,'pisol_dish_type', $dish_type);
    }

    function remove_empty($sidedishes_groups){
        $count = 0;
        
        foreach($sidedishes_groups as $key => $sd_group){

            

            /* If maximum is not set , set it to one */
            if($sd_group['max'] <= 0 || $sd_group['max'] == ""){
                $sidedishes_groups[$key]['max'] = 1 ;
            }
            
            /* This remove a side dish group if there is no group name in it */
            if($sd_group['group_name'] == ""){
               unset($sidedishes_groups[$key]);
            }
            $count2 = 0;
            /* This remove sidedish if there is not name in it */
            foreach($sd_group['sidedish'] as $sidedish_key => $sidedish){
                $sidedishes_groups[$key]['sidedish'][$sidedish_key]['hash'] = md5($sidedish['name'].$count.$count2);
                if($sidedish['name'] == ""){
                    unset($sidedishes_groups[$key]['sidedish'][$sidedish_key]);
                }

                $count2++;
            }

            /* This removes the group if it does not have any side dish in it */
            if(!isset($sd_group['sidedish']) || sizeof($sidedishes_groups[$key]['sidedish']) <= 0){
                unset($sidedishes_groups[$key]);
            }

            $count++;

        }
        

        return $sidedishes_groups;
    }

    function side_dish_groups(){

        $group_counter = 0;
        echo '<div id="pisol_sidedish_group_container" data-group-counter="'.(isset($this->meta_values) ? sizeof($this->meta_values) : 0).'">';
        if($this->meta_values != ""):
        foreach($this->meta_values as $side_dish_group){
            $side_dishes = isset($side_dish_group->sidedish) ? sizeof($side_dish_group->sidedish) : 0 ;
            echo '<div class="sidedish_group" data-groupid="'.$group_counter.'" data-sidedishes="'.$side_dishes.'">';
                    $this->side_dish_group_inner($group_counter, $side_dish_group);
            echo '</div>';
            $group_counter++;
        }
        endif;
        echo '</div>';
    }

    function side_dish_group_inner($group_counter, $side_dish_group){
        $min_limit = apply_filters('pisol_enable_min_limit',false);
        echo '<table class="sidedish-table">
        <tr>
            <td style="vertical-align:bottom;">
                <p class="form-field" style="margin-bottom:0px;">Side Dish Group Name:<input required type="text" name="sidedish['.$group_counter.'][group_name]" placeholder="Side Dish Group Name *" value="'.$side_dish_group->group_name.'"></p>
            </td>
            <td style="vertical-align:bottom;"> 
                <button class="button pisol_add_side_dish">Add Side Dish</button> <button class="button remove_sidedish_group">Remove Side Dish Group</button>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="side_dish_container">';

                $this->side_dishes($group_counter, $side_dish_group);

        echo '  </div>
            </td>
        <tr>
            <td>
            Max Selectable: <input type="number" min="1" required value="'.$side_dish_group->max.'" name="sidedish['.$group_counter.'][max]" placeholder="Maximum number of dish that can be selected">
            </td>
            <td>
            Min Selectable: '.(!$min_limit ? '(Buy PRO version for this)' : '').' <input type="number" min="0" required value="'.(isset($side_dish_group->min) ? $side_dish_group->min : 0).'" name="sidedish['.$group_counter.'][min]" 
            '.($min_limit ? 
            'placeholder="Minimum number of dish that has to be selected"' :
            'placeholder="(BUY PRO VERSION FOR THIS)" disabled').'
            >
            </td>
        </tr>
        </table>';

    }

    function side_dishes($group_counter, $side_dish_group){
        $counter = 0;
        if(isset($side_dish_group->sidedish)):
            foreach($side_dish_group->sidedish as $side_dishes){
                echo '<div class="sidedish_row">
                <input type="text" required value="'.$side_dishes->name.'" name="sidedish['.$group_counter.'][sidedish]['.$counter.'][name]" placeholder="Side Dish Name*">
                <input type="number" step="0.01" min="0"  value="'.$side_dishes->price.'"  name="sidedish['.$group_counter.'][sidedish]['.$counter.'][price]" placeholder="Side Dish Price">
                <button class="button button-primary remove_sidedish">Remove</button>
                </div>';
                $counter++;
            }
        endif;
    }

    /* 
        Addign Foot type: veg or non veg
    */
    function food_types($product_data_tabs){
        $product_data_tabs['pisol-foodtype-tab'] = array( 
            'label' => __( 'Dish Type', 'pisol-restautant-menu' ), 
            'target' => 'pisol_dish_type', 
            'class' => array( 'show_if_simple' ), ); 
        
            return $product_data_tabs;

    }

    function woocom_custom_product_data_fields(){
        global $post;
            

        echo "<div id = 'pisol_dish_type' class = 'panel woocommerce_options_panel' >";
        woocommerce_wp_select( array( 
            'id' => 'pisol_dish_type', 
            'label' => __( 'Dish Type (Veg/Non Veg)', 'pisol-restautant-menu' ),
            'options' => array( 
                'none' => __( 'Select Type', 'pisol-restautant-menu' ), 
                'veg' => __( 'Vegetarian', 'pisol-restautant-menu' ), 
                'non_veg' => __( 'Non-Vegetarian', 'pisol-restautant-menu' ) 
                ) 
                ) 
            );

        echo "</div>";

    }
    
}

new pisol_admin_meta();