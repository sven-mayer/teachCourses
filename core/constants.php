<?php
/*
 * If you want, you can owerwrite this parameters in your wp-config.php
 */

global $wpdb;

if ( !defined('TEACHCOURSES_ARTEFACTS') ) {
    /**
     * This constant defines the table name for teachcourses_artefacts.
     * @since 5.0.0
    */
    define('TEACHCOURSES_ARTEFACTS', $wpdb->prefix . 'teachcourses_artefacts');}

if ( !defined('TEACHCOURSES_COURSES') ) {
    /**
     * This constant defines the table name for teachcourses_courses.
     * @since 5.0.0
    */
    define('TEACHCOURSES_COURSES', $wpdb->prefix . 'teachcourses_courses');}

if ( !defined('TEACHCOURSES_COURSE_DOCUMENTS') ) {
    /**
     * This constant defines the table name for teachcourses_course_documents.
     * @since 5.0.0
    */
    define('TEACHCOURSES_COURSE_DOCUMENTS', $wpdb->prefix . 'teachcourses_course_documents');}

if ( !defined('TEACHCOURSES_SETTINGS') ) {
    /**
     * This constant defines the table name for teachcourses_settings.
     * @since 5.0.0
    */
    define('TEACHCOURSES_SETTINGS', $wpdb->prefix . 'teachcourses_settings');}

if ( !defined('TEACHCOURSES_AUTHORS') ) {
    /**
     * This constant defines the table name for teachcourses_authors.
     * @since 5.0.0
    */
    define('TEACHCOURSES_AUTHORS', $wpdb->prefix . 'teachcourses_authors');}

if ( !defined('TEACHCOURSES_FILE_LINK_CSS_CLASS') ) {
    /**
     * This value defines the CSS classes for file links which are inserted via the tinyMCE plugin
     * @since 5.0.0
    */
    define('TEACHCOURSES_FILE_LINK_CSS_CLASS', 'linksecure tc_file_link');}

if ( !defined('TEACHCOURSES_ERROR_REPORTING') ) {
    /**
     * This value defines if the error reporting is active or not
     * @since 5.0.13
    */
    define('TEACHCOURSES_ERROR_REPORTING', false);}

if ( !defined('TEACHCOURSES_FOREIGN_KEY_CHECKS') ) {
    /**
     * This value defines if foreign key checks are enabled or disabled, while adding database tables
     * @since 5.0.16
    */
    define('TEACHCOURSES_FOREIGN_KEY_CHECKS', true);}

if ( !defined('TEACHCOURSES_TEMPLATE_PATH') ) {
    /**
     * This value defines the template path
     * @since 6.0.0
    */
    define('TEACHCOURSES_TEMPLATE_PATH', TEACHCOURSES_GLOBAL_PATH . 'templates/');}

if ( !defined('TEACHCOURSES_TEMPLATE_URL') ) {
    /**
     * This value defines the template url
     * @since 6.0.0
    */
    define('TEACHCOURSES_TEMPLATE_URL', plugins_url( 'templates/', dirname( __FILE__ ) ) );}

if ( !defined('TEACHCOURSES_ALTMETRIC_SUPPORT') ) {
    /**
     * This value defines if the altmetric support is available (loads external sources)
     * @since 6.0.0
    */
    define('TEACHCOURSES_ALTMETRIC_SUPPORT', false);}
    
if ( !defined('TEACHCOURSES_DOI_RESOLVER') ) {
    /**
     * This value defines if the URL for the DOI resolve service
     * @since 6.1.1
    */
    define('TEACHCOURSES_DOI_RESOLVER', 'https://dx.doi.org/');}
    
if ( !defined('TEACHCOURSES_MENU_POSITION') ) {
    /**
     * This value defines the position in the admin menu. 
     * 
     * Options:
     * null         --> position at the end of the default menu
     * int [0..99]  --> individual position
     * For more see:
     * https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure
     * 
     * @since 7.0
    */
    define('TEACHCOURSES_MENU_POSITION', null);}
    
if ( !defined('TEACHCOURSES_DEBUG') ) { 
    /**
     * This value defines if the debug mode is active or not
     * @since 8.0.0
     */
    define('TEACHCOURSES_DEBUG', false);
}

