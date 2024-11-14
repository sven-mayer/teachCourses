<?php
/**
 * Plugin Name:         teachCourses
 * Plugin URI:          https://github.com/sven-mayer/teachcourses
 * Description:         With teachCourses you can easy manage courses.
 * Author:              Sven Mayer (Michael Winkler - Original Author)
 * Author URI:          https://github.com/sven-mayer/teachcourses
 * Version:             1.0.0
 * Requires at least:   3.9
 * Text Domain:         teachcourses
 * Domain Path:         /languages
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI:   https://github.com/sven-mayer/teachcourses
 * GitHub Branch:       master
*/

/*
   LICENCE

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/************/
/* Includes */
/************/

define('TEACHCOURSES_GLOBAL_PATH', plugin_dir_path(__FILE__));

// Loads contstants
include_once('core/constants.php');

// Core functions
include_once('core/admin.php');
include_once('core/ajax-callback.php');
include_once('core/class-ajax.php');
include_once('core/class-document-manager.php');
include_once('core/class-export.php');
include_once('core/class-html.php');
include_once('core/class-icons.php');
include_once('core/class-db-helpers.php');
include_once('core/class-db-options.php');
include_once('core/deprecated.php');
include_once('core/general.php');
include_once('core/shortcodes.php');
include_once('core/class-db-courses.php');
include_once('core/class-db-documents.php');
include_once('core/class-db-terms.php');


// Admin menus
if ( is_admin() ) {
    include_once('admin/class-authors-page.php');
    include_once('admin/add-course.php');
    
    include_once('admin/settings.php');
    include_once('admin/show-courses.php');
    include_once('admin/add-course.php');
    include_once('admin/show-single-course.php');

    include_once('admin/show-term.php');
    include_once('admin/add-term.php');
}

/*********/
/* Menus */
/*********/

/**
 * Add menu for courses
 * @since 0.1.0
 * @todo Remove support for WordPress < 3.9
 */
function tc_add_menu() {
    global $wp_version;
    global $tc_admin_show_courses_page;
    global $tc_admin_add_course_page;
    $pos = TEACHCOURSES_MENU_POSITION;

    $logo = (version_compare($wp_version, '3.8', '>=')) ? plugins_url( 'images/logo_small.png', __FILE__ ) : plugins_url( 'images/logo_small_black.png', __FILE__ );
    // die("products_first_ends");
    $tc_admin_show_courses_page = add_menu_page(
            __('Course','teachcourses'), 
            __('Courses','teachcourses'),
            'use_teachcourses_courses', 
            'teachcourses',
            array('TC_Courses_Page','init'), 
            $logo, 
            $pos);

    $tc_admin_add_course_page = add_submenu_page(
            'teachcourses',
            __('Add New Course','teachcourses'), 
            __('Add New Course', 'teachcourses'),
            'use_teachcourses_courses',
            'teachcourses-add',
            array('TC_Add_Course_Page','init'));


        $tc_admin_add_course_page = add_submenu_page(
        'teachcourses',
        __('Term','teachcourses'), 
        __('Term', 'teachcourses'),
        'use_teachcourses_courses',
        'teachcourses-term',
        array('TC_Term_Page','init'));

        // Note: An alternative in case we will stwich to a custom post type
        // $tc_admin_add_course_page = add_submenu_page(
        //     'teachcourses',
        //     __('All Course','teachcourses'), 
        //     __('All Course', 'teachcourses'),
        //     'use_teachcourses_courses',
        //     'edit.php?post_type=course');
        
        // Note: An alternative in case we will stwich to a custom post type
        // $tc_admin_add_course_page = add_submenu_page(
        //     'teachcourses',
        //     __('Add New Course','teachcourses'), 
        //     __('Add New Course','teachcourses'), 
        //     'use_teachcourses_courses',
        //     'post-new.php?post_type=course');

    add_action("load-$tc_admin_show_courses_page", array('TC_Courses_Page','tc_show_course_page_help'));
    add_action("load-$tc_admin_show_courses_page", array('TC_Courses_Page','tc_show_course_page_screen_options'));
}

/**
 * Add option screen
 * @since 4.2.0
 */
function tc_add_menu_settings() {
    add_options_page(__('teachcourses Settings','teachcourses'),'teachcourses','administrator','teachcourses/settings.php', 'tc_show_admin_settings');
}

/**
 * Adds custom screen options
 * @global type $tc_admin_show_authors_page
 * @param string $current   Output before the custom screen options
 * @param object $screen    WP_Screen object
 * @return string
 * @since 8.1
 */
function tc_show_screen_options($current, $screen) {
    global $tc_admin_show_authors_page;
    
    if( !is_object($screen) ) {
        return;
    }
    
    // Show screen options for the authors page
    if ( $screen->id == $tc_admin_show_authors_page ) {
        return $current . tc_Authors_Page::print_screen_options();
    }
}

/*****************/
/* Mainfunctions */
/*****************/

/**
 * Returns the current teachcourses version
 * @return string
*/
function get_tc_version() {
    return '8.1.5';
}

/**
 * Returns the WordPress version
 * @global string $wp_version
 * @return string
 * @since 5.0.13
 */
function tc_get_wp_version () {
    global $wp_version;
    return $wp_version;
}

/*************************/
/* Installer and Updater */
/*************************/

/**
 * Database update manager
 * @since 4.2.0
 */
function tc_db_update() {
   require_once('core/class-tables.php');
   require_once('core/class-update.php');
   tc_Update::force_update();
}

/**
 * Database synchronisation manager
 * @param string $table     authors or stud_meta
 * @since 5.0.0
 */
function tc_db_sync($table) {
    require_once('core/class-tables.php');
    require_once('core/class-update.php');
    if ( $table === 'authors' ) {
        tc_Update::fill_table_authors();
    }
}

/**
 * teachcourses plugin activation
 * @param boolean $network_wide
 * @since 4.0.0
 */
function tc_activation ( $network_wide ) {
    global $wpdb;
    // it's a network activation
    if ( $network_wide ) {
        $old_blog = $wpdb->blogid;
        // Get all blog ids
        $blogids = $wpdb->get_col($wpdb->prepare("SELECT `blog_id` FROM $wpdb->blogs"));
        foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            tc_install();
        }
        switch_to_blog($old_blog);
        return;
    }
    // it's a normal activation
    else {
        tc_install();
    }
}

/**
 * Activates the error reporting
 * @since 5.0.13
 */
function tc_activation_error_reporting () {
    file_put_contents(__DIR__.'/teachcourses_activation_errors.html', ob_get_contents());
}

/**
 * Installation manager
 */
function tc_install() {
    require_once('core/class-tables.php');
    tc_Tables::create();
}

/**
 * Uninstallation manager
 */
function tc_uninstall() {
    require_once('core/class-tables.php');
    tc_Tables::remove();
}

/****************************/
/* tinyMCE plugin functions */
/****************************/

/**
 * Hooks functions for tinymce plugin into the correct filters
 * @since 5.0.0
 */
function tc_add_tinymce_button() {
    // the user need at least the edit_post capability (by default authors, editors, administrators)
    if ( !current_user_can( 'edit_posts' ) ) {
        return;
    }

    // the user need at least one of the teachcourses capabilities
    if ( !current_user_can( 'use_teachcourses' ) || !current_user_can( 'use_teachcourses_courses' ) ) {
        return;
    }

    add_filter('mce_buttons', 'tc_register_tinymce_buttons');
    add_filter('mce_external_plugins', 'tc_register_tinymce_js');
}

/**
 * Adds a tinyMCE button for teachcourses
 * @param array $buttons
 * @return array
 * @since 5.0.0
 */
function tc_register_tinymce_buttons ($buttons) {
    array_push($buttons, 'teachcourses_tinymce');
    return $buttons;
}

/**
 * Adds a teachcourses plugin to tinyMCE
 * @param array $plugins
 * @return array
 * @since 5.0.0
 */
function tc_register_tinymce_js ($plugins) {
    $plugins['teachcourses_tinymce'] = plugins_url( 'public/js/tinymce-plugin.js', __FILE__ );
    return $plugins;
}

/*********************/
/* Loading functions */
/*********************/

/**
 * Admin interface script loader
 */
function tc_backend_scripts() {
    $version = get_tc_version();
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    
    // Load scripts only, if it's a teachcourses page
    if (( strpos($page, 'teachcourses') === false ) && (strpos($page, 'add_course') === false )) {
        return;
    }
    wp_enqueue_style('teachcourses-print-css', plugins_url( 'public/styles/print.css', __FILE__ ), false, $version, 'print');
    wp_enqueue_script('teachcourses-standard', plugins_url( 'public/js/backend.js', __FILE__ ) );
    wp_enqueue_style('teachcourses.css', plugins_url( 'public/styles/teachcourses.css', __FILE__ ), false, $version);
    wp_enqueue_script('media-upload');
    add_thickbox();
    
    /* SlimSelect v1.27 */
    wp_enqueue_script('slim-select', plugins_url( 'includes/slim-select/slimselect.min.js', __FILE__ ) );
    wp_enqueue_style('slim-select.css', plugins_url( 'includes/slim-select/slimselect.min.css', __FILE__ ) );
    
    // Load jQuery + ui plugins + plupload
    wp_enqueue_script(array('jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-resizable', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery-ui-dialog', 'plupload'));
    wp_enqueue_style('teachcourses-jquery-ui.css', plugins_url( 'public/styles/jquery.ui.css', __FILE__ ) );
    wp_enqueue_style('teachcourses-jquery-ui-dialog.css', includes_url() . '/css/jquery-ui-dialog.min.css');
    
    // Languages for plugins
    $current_lang = ( version_compare( tc_get_wp_version() , '4.0', '>=') ) ? get_option('WPLANG') : WPLANG;
    $array_lang = array('de_DE','it_IT','es_ES', 'sk_SK');
    if ( in_array( $current_lang , $array_lang) ) {
        wp_enqueue_script('teachcourses-datepicker-de', plugins_url( 'public/js/datepicker/jquery.ui.datepicker-' . $current_lang . '.js', __FILE__ ) );
    }
}

/**
 * Frontend script loader
 */
function tc_frontend_scripts() {
    $type_attr = current_theme_supports( 'html5', 'script' ) ? '' : ' type="text/javascript"';
    $version   = get_tc_version();

    /* start */
    echo PHP_EOL . '<!-- teachcourses -->' . PHP_EOL;

    /* tp-frontend script */
    echo '<script' . $type_attr . ' src="' . plugins_url( 'public/js/frontend.js?ver=' . $version, __FILE__ ) . '"></script>' . PHP_EOL;

    /* tp-frontend style */
    $value = get_tc_option('stylesheet');
    if ($value == '1') {
        echo '<link type="text/css" href="' . plugins_url( 'public/styles/teachcourses_front.css?ver=' . $version, __FILE__ ) . '" rel="stylesheet" />' . PHP_EOL;
    }

    /* altmetric support */
    if ( TEACHCOURSES_ALTMETRIC_SUPPORT === true ) {
        echo '<script' . $type_attr . ' src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>' . PHP_EOL;
    }

    /* END */
    echo '<!-- END teachcourses -->' . PHP_EOL;
}

/**
 * Load language files
 * @since 0.30
 */
function tc_language_support() {
    $domain = 'teachcourses';
    $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
    $path = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
    $mofile = WP_PLUGIN_DIR . '/' . $path . $domain . '-' . $locale . '.mo';
    
    // Load the plugins language files first instead of language files from WP languages directory 
    if ( !load_textdomain($domain, $mofile) ) {
        load_plugin_textdomain($domain, false, $path);
    }
}

/**
 * Adds a link to the WordPress plugin menu
 * @param array $links
 * @param string $file
 * @return array
 */
function tc_plugin_link($links, $file){
    if ($file == plugin_basename(__FILE__)) {
        return array_merge($links, array( sprintf('<a href="options-general.php?page=teachcourses/settings.php">%s</a>', __('Settings') ) ));
    }
    return $links;
}

function tc_register_custom_rewrite_rule() {
    add_rewrite_rule('^teaching/([^/]*)/([^/]*)/?$','index.php?pagename=techcourses&term=$matches[1]&course=$matches[2]','top');
}

function tc_register_query_vars($vars) {
    $vars[] = 'pagename';
    $vars[] = 'term_id';
    $vars[] = 'course';
    return $vars;
}

// Register WordPress-Hooks
register_activation_hook( __FILE__, 'tc_activation');
add_action('init', 'tc_language_support');

add_action('wp_ajax_teachcourses', 'tc_ajax_callback');
add_action('wp_ajax_teachcoursesdocman', 'tc_ajax_doc_manager_callback');
add_action('admin_menu', 'tc_add_menu_settings');
add_action('wp_head', 'tc_frontend_scripts');
add_action('admin_init','tc_backend_scripts');
add_filter('plugin_action_links','tc_plugin_link', 10, 2);
add_action('wp_ajax_tc_document_upload', 'tc_handle_document_uploads' );
add_filter('screen_settings', 'tc_show_screen_options', 10, 2 );

// Register tinyMCE Plugin
if ( version_compare( tc_get_wp_version() , '3.9', '>=') ) {
    add_action('admin_head', 'tc_add_tinymce_button');
    add_action('admin_head', 'tc_write_data_for_tinymce' );
 }

// Activation Error Reporting
if ( TEACHCOURSES_ERROR_REPORTING === true ) {
    register_activation_hook( __FILE__, 'tc_activation_error_reporting' );
}

// Register course module
add_action('admin_menu', 'tc_add_menu');
add_shortcode('tccourseinfo', 'tc_courseinfo_shortcode');
add_shortcode('tccoursedocs', 'tc_coursedocs_shortcode');
add_shortcode('tccourselist', 'tc_courselist_shortcode');
add_shortcode('tcpost','tc_post_shortcode');
add_shortcode('tclist','tc_course_list_shortcode');

// Register custom rewrite rules
function tp_template_redirect() {
    $term = get_query_var('term');
    $course = get_query_var('course');
    $pagename = get_query_var('pagename');
    if ($term && $course && ($pagename == 'techcourses')) {
        // var_dump($term_id . " " . $course . " " . $pagename);

        // Load a custom template file
        include plugin_dir_path(__FILE__) . 'public/single-course.php';//locate_template('public/single-course.php'); // Adjust path to your custom template
        exit;
    }
}


add_action('init', 'tc_register_custom_rewrite_rule');
add_filter('query_vars', 'tc_register_query_vars');
// function tc_plugin_activate() {
//     add_action('init', 'tc_register_custom_rewrite_rule');
//     add_filter('query_vars', 'tc_register_query_vars');
// }
// register_activation_hook( __FILE__, 'tc_plugin_activate' );

add_action('template_redirect', 'tp_template_redirect');

// Hook into template_include to override the template for the 'Courses' page
function myplugin_courses_template($template) {
    // Check if this is the 'Courses' page by its slug
    // var_dump(is_page('teaching'));
    // var_dump(get_query_var("term"));
    // var_dump($template);
    // if (isset($vars['pagename']) && $vars['pagename'] === 'projects') {


    if (is_page('teaching')) {
        // Use the custom template function instead of loading a file
        return myplugin_virtual_courses_template();
    }
    return $template;
}
add_filter('template_include', 'myplugin_courses_template');



// Note: This could be a future alternative to the to implement the course as content type
// include_once(plugin_dir_path(__FILE__) . 'includes/register-post-type.php');