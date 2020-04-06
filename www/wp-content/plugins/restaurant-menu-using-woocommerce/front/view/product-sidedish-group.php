<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$min_limit = apply_filters('pisol_enable_min_limit',false);
?>
<div class="side-dish-group" data-max="<?php echo $side_dish->max; ?>" data-min="<?php echo (isset($side_dish->min) ? $side_dish->min : 0) ; ?>">
<div class="sidedish-group"><?php echo $side_dish->group_name; ?> <?php if($side_dish->max > 0): ?><small>(<?php _e('You can select upto: ','pisol-restautant-menu'); echo $side_dish->max; ?>)</small><?php endif; ?>
    <?php if(isset($side_dish->min) && $side_dish->min > 0 && $min_limit): ?><small>(<?php _e('You must select at least: ','pisol-restautant-menu'); echo $side_dish->min; ?>)</small><?php endif; ?>
</div>
    <div class="dishes-group" data-max="<?php echo $side_dish->max; ?>" data-min="<?php echo (isset($side_dish->min) ? $side_dish->min : 0) ; ?>" data-cat="<?php echo $cat; ?>" data-product="<?php echo $product_id; ?>" style="display:none;">
    <?php 
    $dish_count = 0;
    $currency_position = get_option("woocommerce_currency_pos","left");
    foreach($side_dish->sidedish as $dishes){
        if($dishes->price=="" || !isset($dishes->price)){
            $dishes->price = 0;
        }
        echo '<div><input type="checkbox" class="dish" id="group['.$product_id.']['.$cat.']['.$group_count.']['.$dish_count.']" name="group['.$group_count.']['.$dish_count.']" data-unique="['.$group_count.']['.$dish_count.']" value="'.$dishes->name.'" data-hash="'.$dishes->hash.'" data-product="'.$product_id.'" data-price="'.$dishes->price.'" data-max="'.$side_dish->max.'" data-cat="'.$cat.'"><label for="group['.$product_id.']['.$cat.']['.$group_count.']['.$dish_count.']"><span>'.$dishes->name.'</span> <span>'.(($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): "").' '.$dishes->price.' '.(($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): "").'</span></label></div>';
        $dish_count++;
    }
    ?>
    </div>
</div>