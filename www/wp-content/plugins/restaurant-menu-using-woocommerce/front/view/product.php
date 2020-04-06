<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>
<?php
    $open_animation = get_option('pisol_rest_animation','fadeIn');
?>
<div class="pisol-popup animated <?php echo $open_animation ; ?>">
    <div class="pisol-title">
    <?php echo '<h3>'.$product->get_name().": "; ?>
    <?php if($product->is_type('simple')): ?>
    <small>
    <?php
    $currency_position = get_option("woocommerce_currency_pos","left");
            echo ($product->is_on_sale()) ? '<span class="strike">'.(($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): "").$product->get_regular_price().(($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): "").' </span>' : "";
            echo  (($currency_position == "left" || $currency_position == "left_space") ? get_woocommerce_currency_symbol(): "").'<span class="product_price" data-original="'.$product->get_price().'">'.$product->get_price().'</span>'.(($currency_position == "right" || $currency_position == "right_space") ? get_woocommerce_currency_symbol(): "");
    ?>
    </small>
    <?php endif; ?>
    <?php echo '</h3>'; ?>
    <button title="%title%" type="button" class="mfp-close">&#215;</button>
    </div>
    <div class="pisol-flex">
        <?php if($img != "" && isset($img)): ?>
        <div class="pisol-image">
            <img src="<?php echo $img; ?>" class="pisol-fluid">
            <div class="pisol-popup-price">
            
            </div>
        </div>
        <?php endif; ?>
        <div class="pisol-content">
            <?php echo $product->get_description(); ?>
        </div>
    </div>

</div>