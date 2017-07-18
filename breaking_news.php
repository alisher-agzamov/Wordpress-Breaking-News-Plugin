<?php
/*
Plugin Name: Breaking News
Description: Plugin to display breaking news
Version: 1.0
Author: Alisher Agzamov
*/

define('BREAKING_NEWS_PLUGIN_BASENAME', plugin_basename( __FILE__ ));

// includes
include_once( 'options_page.php' );
include_once( 'post_editor.php' );
include_once( 'shortcode.php' );

register_activation_hook( __FILE__, function() {
    if( ! get_option(Breaking_News_Options_Page::$options_settings_key) ) {
        // set default option values
        update_option(Breaking_News_Options_Page::$options_settings_key, Breaking_News_Options_Page::default_option_values());
    }
} );


register_uninstall_hook( __FILE__, 'breaking_news_deactivation' );

function breaking_news_deactivation() {
    // delete option values
    delete_option(Breaking_News_Options_Page::$options_settings_key);
    delete_option(Breaking_News_Post_Editor::$options_content_data_key);
}

if( is_admin() ) {
    new Breaking_News_Options_Page();
}

// Loads a plugin's translated strings
add_action( 'plugins_loaded', function(){
    load_plugin_textdomain( 'breaking_news', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
} );