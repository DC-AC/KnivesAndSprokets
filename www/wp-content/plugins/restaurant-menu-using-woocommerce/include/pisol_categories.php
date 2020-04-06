<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class pisol_categories{


    /* 
        This return all the top level categories 
        @ This return a complet category object in array form
    */
    function top_level_catagories(){

        $hide_empty = apply_filters("pisol_rm_pro_hide_empty_cat", false);
        
        $args = array(
            'taxonomy'   => "product_cat",
            'parent'        => 0,
            'hide_empty' => $hide_empty,
        );
        $product_categories = get_terms($args);
        return $product_categories;

    }

    /* 
        Get all child category for given category
        @ This return only child category id
    */
    function get_child_categories($parent_cat_id){

        $args = array(
            'hierarchical' => 1,
            'show_option_none' => '',
            'hide_empty' => 0,
            'parent' => $parent_cat_id,
            'taxonomy' => 'product_cat'
        );
     
        $child_categories_sorted = get_categories($args);
        $child_categories = array();
        foreach($child_categories_sorted as $category){
            $child_categories[] = $category->term_id;
        }

        return $child_categories;

    }

    /*
        Get category object
    */
    function get_category_object($cat_id){
        return get_term_by( 'id', $cat_id, 'product_cat' ); 
    }

    /* 
        Get top level category in form of an array for the admin side option
        array(id=>name)    
    */
    function get_top_cat_array(){
        $cat_objects  = $this->top_level_catagories();
        $cat = array();
        //  print_r($cat_objects);
        foreach($cat_objects as $cat_object){
            $cat[$cat_object->term_id] = $cat_object->name;
        }
        return $cat;
    }

}