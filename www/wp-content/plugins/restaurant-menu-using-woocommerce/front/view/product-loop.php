<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<?php
    if(isset($cat)){
        $search_result = false;
    }else{
        $search_result = true;
    }
?>
<table class="pisol_table">
    <thead>
            <?php if($show_image): ?>
            <th class="pisol_img"></th>
            <?php endif; ?>
            <th class="pisol_name" colspan="2"><?php esc_html_e( 'Product', 'woocommerce' );  ?><?php do_action('pisol_product_filter'); ?></th>
            <th class="pisol_price"><?php _e('Price/Unit', 'pisol-restautant-menu'); ?></th>
            <th class="pisol_action"></th>
    </thead>
    <tbody>
    <?php
        if ($loop->have_posts()){
            $currency_position = get_option("woocommerce_currency_pos","left");
            while ($loop->have_posts()) : $loop->the_post();
            
                $product = wc_get_product( $loop->get_the_ID() );

                if(apply_filters('pisol_hide_out_of_stock',false)){
                    if(!$product->is_in_stock()) continue;

                    $manage_stock =  $product->managing_stock();

                    if($manage_stock){
                        $stock = $product->get_stock_quantity();

                        if(!($stock > 0)) continue;
                    }
                }

                if($search_result){
                    $categories = $product->get_category_ids();
                    $cat = $categories[0];
                }

                echo '<tr id="product_'.$product->get_id().'_'.$cat.'" class="product_row '.$this->food_type_class($product->get_id(), true).' child_category_selector_'.$cat.'" data-cat="'.$cat.'"  data-product="'.$product->get_id().'">';
                
                $this->pisol_product_image($product, $cat);

                    echo '<td>';
                    echo '<div class="product_name"><a href="javascript:void(0);"  data-id="'.$product->get_id().'">'.$product->get_name().'</a></div>';
                    if($product->get_short_description() != "" && apply_filters('pisol_rm_pro_pisol_short_desc',false)):
                    echo '<small class="pisol-short-desc">'.$product->get_short_description().'</small>'; 
                    endif;                  
                    echo '<ul class="added_sidedishes"></ul>';
                    echo '</td>';
                    echo '<td  class="pi-food-type">';
                    echo $this->get_food_type($product->get_id());
                    echo '</td>';
                    echo '<td>';
                    self::price($product);
                    if($product->is_type('simple')){
                        echo '<div class="quantity"><input type="number" id="quantity_5b3b76e828c6d" class="input-text qty text" step="1" min="1" max="" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" aria-labelledby=""></div>';
                    }
                    echo '</td>';
                    echo '<td>';
                    if($product->is_type('simple')){
                        echo  do_shortcode('[add_to_cart id="'.$product->get_id().'"]');
                    }else{
                        echo '<a  data-product-id="'.$product->get_id().'"  data-cat="'.$cat.'" class="quick_view button product_type_simple add_to_cart_button ajax_add_to_cart" href="javascript:void(0)">'.__('Select Option','woocommerce').'</a>';
                    }
                    echo '</td>';
               echo '</tr>';
               if($product->is_type('simple')){
               echo '<tr data-cat="'.$cat.'" data-product="'.$product->get_id().'" class="pisol_sidedish_row '.$this->food_type_class($product->get_id(), true).' child_category_selector_'.$cat.'" id="pisol_sidedish_row_'.$product->get_id().'_'.$cat.'">';
               echo '<td colspan="'.$columns.'">';
                  $this->get_sidedishes($product->get_id(), $cat);
               echo '</td>';
               echo '</tr>';
               }
            endwhile; 
            $this->pisol_get_child_cat_products($cat);
        }else{
            /* 
            This make sure if the parent category dont have product 
            even then child category can be shown
            */
            $this->pisol_get_child_cat_products($cat);
        }
    ?>
    </tbody>
</table>