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

define('TEACHPRESS_GLOBAL_PATH', plugin_dir_path(__FILE__));

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
include_once('core/class-mail.php');
include_once('core/class-db-helpers.php');
include_once('core/class-db-options.php');
include_once('core/deprecated.php');
include_once('core/feeds.php');
include_once('core/general.php');
include_once('core/shortcodes.php');
include_once('core/courses/class-db-artefacts.php');
include_once('core/courses/class-db-assessments.php');
include_once('core/courses/class-db-courses.php');
include_once('core/courses/class-db-documents.php');
include_once('core/courses/class-db-students.php');
include_once('core/courses/enrollments.php');


// Admin menus
if ( is_admin() ) {
    include_once('admin/class-authors-page.php');
    include_once('admin/class-tags-page.php');
    include_once('admin/add-course.php');
    include_once('admin/add-students.php');
    include_once('admin/create-lists.php');
    include_once('admin/edit-student.php');
    
    include_once('admin/mail.php');
    include_once('admin/settings.php');
    include_once('admin/show-courses.php');
    include_once('admin/show-single-course.php');
    include_once('admin/show-students.php');
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
    $pos = TEACHPRESS_MENU_POSITION;

    $logo = (version_compare($wp_version, '3.8', '>=')) ? plugins_url( 'images/logo_small.png', __FILE__ ) : plugins_url( 'images/logo_small_black.png', __FILE__ );

    $tc_admin_show_courses_page = add_menu_page(
            __('Course','teachcorses'), 
            __('Course','teachcorses'),
            'use_teachcorses_courses', 
            __FILE__, 
            'tc_show_courses_page', 
            $logo, 
            $pos);
    $tc_admin_add_course_page = add_submenu_page(
            'teachcorses/teachcorses.php',
            __('Add new','teachcorses'), 
            __('Add new', 'teachcorses'),
            'use_teachcorses_courses',
            'teachcorses/add_course.php',
            'tc_add_course_page');
    add_submenu_page(
            'teachcorses/teachcorses.php',
            __('Students','teachcorses'), 
            __('Students','teachcorses'),
            'use_teachcorses_courses', 
            'teachcorses/students.php', 
            'tc_students_page');
    add_action("load-$tc_admin_add_course_page", 'tc_add_course_page_help');
    add_action("load-$tc_admin_show_courses_page", 'tc_show_course_page_help');
    add_action("load-$tc_admin_show_courses_page", 'tc_show_course_page_screen_options');
}

/**
 * Add option screen
 * @since 4.2.0
 */
function tc_add_menu_settings() {
    add_options_page(__('teachCorses Settings','teachcorses'),'teachCorses','administrator','teachcorses/settings.php', 'tc_show_admin_settings');
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
 * Returns the current teachCorses version
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

/**
 * Function for the integrated registration mode
 * @since 1.0.0
 */
function tc_advanced_registration() {
    $user = wp_get_current_user();
    global $wpdb;
    global $current_user;
    $test = $wpdb->query("SELECT `wp_id` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$current_user->ID'");
    if ($test == '0' && $user->ID != '0') {
        if ($user->user_firstname == '') {
            $user->user_firstname = $user->display_name;
        }
        $data = array (
            'firstname' => $user->user_firstname,
            'lastname' => $user->user_lastname,
            'userlogin' => $user->user_login,
            'email' => $user->user_email
        );
        tc_Students::add_student($user->ID, $data );
    }
}

/**
 * Adds the publication feeds
 * @since 6.0.0
 */
function tc_feed_init(){
    add_feed('tc_pub_rss', 'tc_pub_rss_feed_func');
    add_feed('tc_pub_bibtex', 'tc_pub_bibtex_feed_func');
    add_feed('tc_export', 'tc_export_feed_func');
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
    if ( $table === 'stud_meta' ) {
        tc_Update::fill_table_stud_meta();
    }
}

/**
 * teachCorses plugin activation
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
    file_put_contents(__DIR__.'/teachcorses_activation_errors.html', ob_get_contents());
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

    // the user need at least one of the teachcorses capabilities
    if ( !current_user_can( 'use_teachcorses' ) || !current_user_can( 'use_teachcorses_courses' ) ) {
        return;
    }

    add_filter('mce_buttons', 'tc_register_tinymce_buttons');
    add_filter('mce_external_plugins', 'tc_register_tinymce_js');
}

/**
 * Adds a tinyMCE button for teachCorses
 * @param array $buttons
 * @return array
 * @since 5.0.0
 */
function tc_register_tinymce_buttons ($buttons) {
    array_push($buttons, 'teachcorses_tinymce');
    return $buttons;
}

/**
 * Adds a teachCorses plugin to tinyMCE
 * @param array $plugins
 * @return array
 * @since 5.0.0
 */
function tc_register_tinymce_js ($plugins) {
    $plugins['teachcorses_tinymce'] = plugins_url( 'js/tinymce-plugin.js', __FILE__ );
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
    
    // Load scripts only, if it's a teachcorses page
    if ( strpos($page, 'teachcorses') === false && strpos($page, 'publications') === false ) {
        return;
    }
    
    wp_enqueue_style('teachcorses-print-css', plugins_url( 'styles/print.css', __FILE__ ), false, $version, 'print');
    wp_enqueue_script('teachcorses-standard', plugins_url( 'js/backend.js', __FILE__ ) );
    wp_enqueue_style('teachcorses.css', plugins_url( 'styles/teachcorses.css', __FILE__ ), false, $version);
    wp_enqueue_script('media-upload');
    add_thickbox();

    /* academicons v1.8.6 */
    if ( TEACHPRESS_LOAD_ACADEMICONS === true ) {
        wp_enqueue_style('academicons', plugins_url( 'includes/academicons/css/academicons.min.css', __FILE__ ) );
    }

    /* Font Awesome Free v5.10.1 */
    if (TEACHPRESS_LOAD_FONT_AWESOME === true) {
        wp_enqueue_style('font-awesome', plugins_url( 'includes/fontawesome/css/all.min.css', __FILE__ ) );
    }
    
    /* SlimSelect v1.27 */
    wp_enqueue_script('slim-select', plugins_url( 'includes/slim-select/slimselect.min.js', __FILE__ ) );
    wp_enqueue_style('slim-select.css', plugins_url( 'includes/slim-select/slimselect.min.css', __FILE__ ) );
    
    // Load jQuery + ui plugins + plupload
    wp_enqueue_script(array('jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-resizable', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery-ui-dialog', 'plupload'));
    wp_enqueue_style('teachcorses-jquery-ui.css', plugins_url( 'styles/jquery.ui.css', __FILE__ ) );
    wp_enqueue_style('teachcorses-jquery-ui-dialog.css', includes_url() . '/css/jquery-ui-dialog.min.css');
    
    // Languages for plugins
    $current_lang = ( version_compare( tc_get_wp_version() , '4.0', '>=') ) ? get_option('WPLANG') : WPLANG;
    $array_lang = array('de_DE','it_IT','es_ES', 'sk_SK');
    if ( in_array( $current_lang , $array_lang) ) {
        wp_enqueue_script('teachcorses-datepicker-de', plugins_url( 'js/datepicker/jquery.ui.datepicker-' . $current_lang . '.js', __FILE__ ) );
    }
}

/**
 * Frontend script loader
 */
function tc_frontend_scripts() {
    $type_attr = current_theme_supports( 'html5', 'script' ) ? '' : ' type="text/javascript"';
    $version   = get_tc_version();

    /* start */
    echo PHP_EOL . '<!-- teachCorses -->' . PHP_EOL;

    /* tp-frontend script */
    echo '<script' . $type_attr . ' src="' . plugins_url( 'js/frontend.js?ver=' . $version, __FILE__ ) . '"></script>' . PHP_EOL;

    /* tp-frontend style */
    $value = get_tc_option('stylesheet');
    if ($value == '1') {
        echo '<link type="text/css" href="' . plugins_url( 'styles/teachcorses_front.css?ver=' . $version, __FILE__ ) . '" rel="stylesheet" />' . PHP_EOL;
    }

    /* altmetric support */
    if ( TEACHPRESS_ALTMETRIC_SUPPORT === true ) {
        echo '<script' . $type_attr . ' src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>' . PHP_EOL;
    }

    /* academicons v1.8.6 */
    if ( TEACHPRESS_LOAD_ACADEMICONS === true ) {
        wp_enqueue_style('academicons', plugins_url( 'includes/academicons/css/academicons.min.css', __FILE__ ) );
    }

    /* Font Awesome Free 5.10.1 */
    if (TEACHPRESS_LOAD_FONT_AWESOME === true) {
        wp_enqueue_style('font-awesome', plugins_url( 'includes/fontawesome/css/all.min.css', __FILE__ ) );
    }

    /* END */
    echo '<!-- END teachCorses -->' . PHP_EOL;
}

/**
 * Load language files
 * @since 0.30
 */
function tc_language_support() {
    $domain = 'teachcorses';
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
        return array_merge($links, array( sprintf('<a href="options-general.php?page=teachcorses/settings.php">%s</a>', __('Settings') ) ));
    }
    return $links;
}

// Register WordPress-Hooks
register_activation_hook( __FILE__, 'tc_activation');
add_action('init', 'tc_language_support');
add_action('init', 'tc_feed_init');
add_action('init', 'tc_register_all_publication_types');
add_action('wp_ajax_teachcorses', 'tc_ajax_callback');
add_action('wp_ajax_teachcorsesdocman', 'tc_ajax_doc_manager_callback');
add_action('admin_menu', 'tc_add_menu_settings');
add_action('wp_head', 'tc_frontend_scripts');
add_action('admin_init','tc_backend_scripts');
add_filter('plugin_action_links','tc_plugin_link', 10, 2);
add_action('wp_ajax_tc_document_upload', 'tc_handle_document_uploads' );
add_filter( 'screen_settings', 'tc_show_screen_options', 10, 2 );

// Register tinyMCE Plugin
if ( version_compare( tc_get_wp_version() , '3.9', '>=') ) {
    add_action('admin_head', 'tc_add_tinymce_button');
    add_action('admin_head', 'tc_write_data_for_tinymce' );
 }

// Activation Error Reporting
if ( TEACHPRESS_ERROR_REPORTING === true ) {
    register_activation_hook( __FILE__, 'tc_activation_error_reporting' );
}

// Register course module
add_action('admin_menu', 'tc_add_menu');
add_shortcode('tpdate', 'tc_date_shortcode');  // Deprecated
add_shortcode('tpcourseinfo', 'tc_courseinfo_shortcode');
add_shortcode('tpcoursedocs', 'tc_coursedocs_shortcode');
add_shortcode('tpcourselist', 'tc_courselist_shortcode');
add_shortcode('tpenrollments', 'tc_enrollments_shortcode');
add_shortcode('tppost','tc_post_shortcode');