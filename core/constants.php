<?php
/*
 * If you want, you can owerwrite this parameters in your wp-config.php
 */

global $wpdb;

if ( !defined('TEACHCOURSES_ARTEFACTS') ) {
    /**
     * This constant defines the table name for teachcorses_artefacts.
     * @since 5.0.0
    */
    define('TEACHCOURSES_ARTEFACTS', $wpdb->prefix . 'teachcorses_artefacts');}

if ( !defined('TEACHCOURSES_ASSESSMENTS') ) {
    /**
     * This constant defines the table name for teachcorses_assessments.
     * @since 5.0.0
    */
    define('TEACHCOURSES_ASSESSMENTS', $wpdb->prefix . 'teachcorses_assessments');}

if ( !defined('TEACHCOURSES_STUD') ) {
    /**
     * This constant defines the table name for teachcorses_stud.
     * @since 5.0.0
    */
    define('TEACHCOURSES_STUD', $wpdb->prefix . 'teachcorses_stud');}

if ( !defined('TEACHCOURSES_STUD_META') ) {
    /**
     * This constant defines the table name for teachcorses_stud_meta.
     * @since 5.0.0
    */
    define('TEACHCOURSES_STUD_META', $wpdb->prefix . 'teachcorses_stud_meta');}

if ( !defined('TEACHCOURSES_COURSES') ) {
    /**
     * This constant defines the table name for teachcorses_courses.
     * @since 5.0.0
    */
    define('TEACHCOURSES_COURSES', $wpdb->prefix . 'teachcorses_courses');}

if ( !defined('TEACHCOURSES_COURSE_META') ) {
    /**
     * This constant defines the table name for teachcorses_course_meta.
     * @since 5.0.0
    */
    define('TEACHCOURSES_COURSE_META', $wpdb->prefix . 'teachcorses_course_meta');}

if ( !defined('TEACHCOURSES_COURSE_CAPABILITIES') ) {
    /**
     * This constant defines the table name for teachcorses_course_cababilities.
     * @since 5.0.0
    */
    define('TEACHCOURSES_COURSE_CAPABILITIES', $wpdb->prefix . 'teachcorses_course_capabilities');}

if ( !defined('TEACHCOURSES_COURSE_DOCUMENTS') ) {
    /**
     * This constant defines the table name for teachcorses_course_documents.
     * @since 5.0.0
    */
    define('TEACHCOURSES_COURSE_DOCUMENTS', $wpdb->prefix . 'teachcorses_course_documents');}

if ( !defined('TEACHCOURSES_SIGNUP') ) {
    /**
     * This constant defines the table name for teachcorses_signups.
     * @since 5.0.0
    */
    define('TEACHCOURSES_SIGNUP', $wpdb->prefix . 'teachcorses_signup');}

if ( !defined('TEACHCOURSES_SETTINGS') ) {
    /**
     * This constant defines the table name for teachcorses_settings.
     * @since 5.0.0
    */
    define('TEACHCOURSES_SETTINGS', $wpdb->prefix . 'teachcorses_settings');}

if ( !defined('TEACHCOURSES_PUB') ) {
    /**
     * This constant defines the table name for teachcorses_pub.
     * @since 5.0.0
    */
    define('TEACHCOURSES_PUB', $wpdb->prefix . 'teachcorses_pub');}

if ( !defined('TEACHCOURSES_PUB_META') ) {
    /**
     * This constant defines the table name for teachcorses_pub_meta.
     * @since 5.0.0
    */
    define('TEACHCOURSES_PUB_META', $wpdb->prefix . 'teachcorses_pub_meta');}

if ( !defined('TEACHCOURSES_PUB_CAPABILITIES') ) {
    /**
     * This constant defines the table name for teachcorses_course_cababilites.
     * @since 6.0.0
    */
    define('TEACHCOURSES_PUB_CAPABILITIES', $wpdb->prefix . 'teachcorses_pub_capabilities');}

if ( !defined('TEACHCOURSES_PUB_DOCUMENTS') ) {
    /**
     * This constant defines the table name for teachcorses_course_documents.
     * @since 6.0.0
    */
    define('TEACHCOURSES_PUB_DOCUMENTS', $wpdb->prefix . 'teachcorses_pub_documents');}

if ( !defined('TEACHCOURSES_PUB_IMPORTS') ) {
    /**
     * This constant defines the table name for teachcorses_pub_imports.
     * @since 6.0.0
    */
    define('TEACHCOURSES_PUB_IMPORTS', $wpdb->prefix . 'teachcorses_pub_imports');}    
    
if ( !defined('TEACHCOURSES_TAGS') ) {
    /**
     * This constant defines the table name for teachcorses_tags.
     * @since 5.0.0
    */
    define('TEACHCOURSES_TAGS', $wpdb->prefix . 'teachcorses_tags');}

if ( !defined('TEACHCOURSES_RELATION') ) {
    /**
     * This constant defines the table name for teachcorses_relation. This is the relationship tags to publications.
     * @since 5.0.0
    */
    define('TEACHCOURSES_RELATION', $wpdb->prefix . 'teachcorses_relation');}

if ( !defined('TEACHCOURSES_USER') ) {
    /**
     * This constant defines the table name for teachcorses_user. This is the relationship publications to users.
     * @since 5.0.0
    */
    define('TEACHCOURSES_USER', $wpdb->prefix . 'teachcorses_user');}

if ( !defined('TEACHCOURSES_AUTHORS') ) {
    /**
     * This constant defines the table name for teachcorses_authors.
     * @since 5.0.0
    */
    define('TEACHCOURSES_AUTHORS', $wpdb->prefix . 'teachcorses_authors');}

if ( !defined('TEACHCOURSES_REL_PUB_AUTH') ) {
    /**
     * This constant defines the table name for teachcorses_rel_pub_auth. This is the relationship publications to authors.
     * @since 5.0.0
    */
    define('TEACHCOURSES_REL_PUB_AUTH', $wpdb->prefix . 'teachcorses_rel_pub_auth');}

if ( !defined('TEACHCOURSES_TIME_LIMIT') ) {
    /**
     * This value is used for PHP's set_time_limit(). The plugin sets this value before an import or export of publications
     * @since 5.0.0
    */
    define('TEACHCOURSES_TIME_LIMIT', 240);}

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
    
if ( !defined('TEACHCOURSES_LOAD_ACADEMICONS') ) {
    /**
     * This value defines if the URL for the DOI resolve service
     * @since 7.0
    */
    define('TEACHCOURSES_LOAD_ACADEMICONS', true);}
    
if ( !defined('TEACHCOURSES_LOAD_FONT_AWESOME') ) {
    /**
     * This value defines if the URL for the DOI resolve service
     * @since 7.0
    */
    define('TEACHCOURSES_LOAD_FONT_AWESOME', true);}
    
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

