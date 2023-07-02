<?php

function simple_bootstrap_theme_load_scripts(){
    //attach css files
    wp_enqueue_style("bootstrap", get_template_directory_uri()."/assets/css/styles.css", array(), "1.0", "all");
    // attach style.css 
    wp_enqueue_style("style", get_stylesheet_uri(), array(), "1.0","all");
    // attach script.js 
    wp_enqueue_script("script", get_template_directory_uri()."/assets/js/scripts.js", array("jquery"), "1.0",true);
  
}
add_action("wp_enqueue_scripts", "simple_bootstrap_theme_load_scripts");
//register nav menu
function simple_bootstrap_theme_nav_config(){
    register_nav_menus(array(
        "simp_btsp_thm_primary_menu_id" => "simple bootstrap theme primary menu(Top Menu)",
        "simp_btsp_thm_secondary_menu_id" => "simple bootstrap theme sidebar"

    ));
    //register theme support like featured image
    add_theme_support("post-thumbnails");
    

    add_theme_support("woocommerce", array(
        "thumbnail_image_width" => 150,
        "single_image_width" => 200,
        "product_grid" => array(
            "default_columns" => 10,
            "min_columns" => 2,
            "max_columns" => 3,
        )
    ));
    add_theme_support("custom-logo", [
        "height" => 90,
        "width" => 90,
        "flex_height" => true,
        "flex_width" => true

    ]);
    // product thumbnail effect support
    add_theme_support("wc-product-gallery-zoom");
    add_theme_support("wc-product-gallery-lightbox");
    add_theme_support("wc-product-gallery-slider");

}
add_action("after_setup_theme", "simple_bootstrap_theme_nav_config");
//adding li class from here
function simple_bootstrap_theme_add_li_class($classes, $item, $args){
    //"nav-item" comes from header.php
    $classes[] = "nav-item";
    return $classes;
}
add_filter("nav_menu_css_class", "simple_bootstrap_theme_add_li_class", 1, 3);
// 1 is priority while 3 is function of perameters which is access


//adding classes to anchor links
function simple_bootstrap_theme_add_anchor_links($classes, $item, $args){
    //"nav-linkk" comes from header.php
    //$classes['class'] is because 0 index value in inspect, to remove 0 index we put class inside array 
    $classes['class'] = "nav-link";
    return $classes;
}
add_filter("nav_menu_link_attributes", "simple_bootstrap_theme_add_anchor_links", 1, 3);

if(class_exists("woocommerce")){
    //we add the folder include/wc-modifications.php
    include_once 'include/wc-modifications.php';
}


/**
 * Show cart contents / total Ajax
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'simple_bootstrap_theme_woocommerce_header_add_to_cart_fragment' );

function simple_bootstrap_theme_woocommerce_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	ob_start();

	?>
    <span class="items-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
	<?php
	$fragments['span.items-count'] = ob_get_clean();
	return $fragments;
}

//copyright footer
function  simple_bootstrap_theme_laod_wp_customizer($wp_customize){
        // customzier code


        // adding section
        $wp_customize->add_section("sec_copyright", array(
            "title" => "Copyright Section",
            "description" => "This is a copyright section" 
        ));

        // adding settings/field
        $wp_customize->add_setting("set_copyright", array(
            //theme mod ia our title/label
            "type" => "theme_mod",
            //default means the value will be null inside text field initailly
            "default" => "",
            // sanitize_callback will senitizr the value before saving/sanding the value to database
            "sanitize_callback" => "sanitize_text_field",

        ));

           //add control 
           $wp_customize->add_control("set_copyright", array(
            "label" => "Copyright",
            "description" => "Please fill the copyright text",
            "section" => "sec_copyright",
            "type" => "text",

        ));
// section of new arrival / popularity control limit and columns
          
        // adding section
           $wp_customize->add_section("sec_product_panel", array(
            "title" => "product panel Limit and Columns",
            "description" => "This is a section which is going to provide the controls for home page product panels" 
        ));
            //for Limits of new arrival control
        // adding settings/field
        $wp_customize->add_setting("set_new_arrival_limit", array(
            //theme mod ia our title/label
            "type" => "theme_mod",
            //default means the value will be null inside text field initailly
            "default" => "",
            // sanitize_callback will senitizr the value before saving/sanding the value to database
            "sanitize_callback" => "absint",

        ));

           //add control 
           $wp_customize->add_control("set_new_arrival_limit", array(
            "label" => "New Arrival - Product Limit",
            "description" => "Please fill the Limit of new arrival",
            "section" => "sec_product_panel",
            "type" => "number",

        )); 
            //for columns of new arrival control
         // adding settings/field
         $wp_customize->add_setting("set_new_arrival_column", array(
            //theme mod ia our title/label
            "type" => "theme_mod",
            //default means the value will be null inside text field initailly
            "default" => "",
            // sanitize_callback will senitizr the value before saving/sanding the value to database
            "sanitize_callback" => "absint",

        ));
         //add control 
         $wp_customize->add_control("set_new_arrival_column", array(
            "label" => "New Arrival - Product column",
            "description" => "Please fill the Column of new arrival",
            "section" => "sec_product_panel",
            "type" => "number",

        ));



        //for limit of popularity control
        // adding settings/field
        $wp_customize->add_setting("set_popular_limit", array(
            //theme mod ia our title/label
            "type" => "theme_mod",
            //default means the value will be null inside text field initailly
            "default" => "",
            // sanitize_callback will senitizr the value before saving/sanding the value to database
            "sanitize_callback" => "absint",

        ));
        //add control 
        $wp_customize->add_control("set_popular_limit", array(
            "label" => "Popularity - Product Limit",
            "description" => "Please fill the Limit of Popularity",
            "section" => "sec_product_panel",
            "type" => "number",

        ));
             //for columns of popularity control
        // adding settings/field
        $wp_customize->add_setting("set_popular_column", array(
            //theme mod ia our title/label
            "type" => "theme_mod",
            //default means the value will be null inside text field initailly
            "default" => "",
            // sanitize_callback will senitizr the value before saving/sanding the value to database
            "sanitize_callback" => "absint",

        ));
        //add control 
        $wp_customize->add_control("set_popular_column", array(
            "label" => "Popularity - Product Column",
            "description" => "Please fill the Column of Popularity",
            "section" => "sec_product_panel",
            "type" => "number",

        ));
}

add_action("customize_register", "simple_bootstrap_theme_laod_wp_customizer");