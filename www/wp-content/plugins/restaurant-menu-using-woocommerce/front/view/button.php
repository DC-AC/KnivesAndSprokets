<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>
<div class="pisol_parent_category">
<?php
    foreach($this->top_categories as $cat){
        
        echo '<a class="pisol_cat_button '.(($default_cat == $cat->term_id) ? "active" : "").'" href="javascript:void(0);" data-id="'.$cat->term_id.'">'.$cat->name.'</a>';
    }
?>
</div>