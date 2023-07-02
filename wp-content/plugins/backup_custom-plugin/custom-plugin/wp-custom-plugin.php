<?php
/*
 * Plugin Name: custom plugin
 * Plugin URI: https://www.google.com/
 * Description: An custom toolkit that helps you. Beautifully.
 * Version: 1.0
 * Author: jarry
 * Author URI: https://facebook.com
 * Text Domain: custom-plugin
 */

//  echo plugins_url();
//  echo "<br/>";
//  echo plugin_dir_path(__FILE__);die;


// define constants we can define our global varible
//we can access our text files and  php files 
define('PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
//we can access our js,css files and images
define('PLUGIN_URL',plugins_url());
define('PLUGIN_VERSION','1.0');
//add_menu_page we can add our plugin page in wp menu tab 
function add_my_custom_menu(){
    //after menu tab active we provide a name customplugin 
    //show menu title inside wp dashboard Custom plugin
    //if wp dashboard login then show manage_options as Custom plugin
    // slug as custom-plugin
    // custom_admin_view will work when someone click on custom plugin 
    //show plugin icon dashicons-admin-users
    // 6 is the plugin position
    add_menu_page("customplugin",//page title
    "Custom plugin", //user title
    "manage_options", //capability - admin level access
    "custom-plugin", //page slug - parant slug
    "add_new_function", //callback function
    "dashicons-admin-users", //icon
    6 //postion
    );
    add_submenu_page(
        "custom-plugin", //parant slug means our menu slug
        "Add new", //page title
        "Add new", //menu title
        "manage_options", //capability - admin level access
        "custom-plugin", //submenu slug
        "add_new_function", //callback function
    );
    add_submenu_page(
        "custom-plugin", //parant slug means our menu slug
        "All pages", //page title
        "All pages", //menu title
        "manage_options", //capability - admin level access
        "all-pages", //submenu slug
        "all_page_function", //callback function
    );
 }
 //action hook
 add_action("admin_menu","add_my_custom_menu");

//callback function for submenu
 function add_new_function(){
    //add new function
    include_once PLUGIN_DIR_PATH."/views/addnew.php";
 }
 //callback function for submenu
 function all_page_function(){
    //all page function
    include_once PLUGIN_DIR_PATH."/views/allpage.php";
 }
function custom_plugin_assets(){
    //css and js files
    wp_enqueue_style("custompluign-style", //unique name of css file
    PLUGIN_URL."/custom-plugin/assets/css/style.css", //path of css file
    '', //any other file is dependent or not but there is no any file is dependent
    PLUGIN_VERSION, //Plugin version number
); 
    //css and js files
    wp_enqueue_script("custompluign-script", //unique name of js file
    PLUGIN_URL."/custom-plugin/assets/js/script.js", //path of js file
    '', //any other file is dependent or not but there is no any file is dependent
    PLUGIN_VERSION, //Plugin version number
    true //if we want to add link inside footer
); 
}
//when our plugin activate, initialize first time 
add_action("init","custom_plugin_assets");

//automatic database table creation
function custom_plugin_tables(){
    //global object by which we run our query
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    if(count($wpdb->get_var{'SHOW TABLES LIKE "wp_custom_plugin"'}) == 0){

    $sql_query_to_create_table = "CREATE TABLE `wp_custom_plugin` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` int(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `phone` varchar(255) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1"; // sql query to create table

    dbDelta($sql_query_to_create_table);
    
    }
}
register_activation_hook(__FILE__,'custom_plugin_tables'); 

function deactivate_table(){
    //uninstall mysql database code
    global $wpdb;
    $wpdb->query("DROP TABLE IF Exists wp_custom_plugin");
}

register_deactivation_hook(__FILE__,'deactivate_table');


