<?php
/**
 * This file contains all functions for updating a teachcourses database
 * 
 * @package teachcourses\core\update
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 4.2.0
 */

/**
 * This class contains all functions for updating a teachcourses database
 * @package teachcourses\core\update
 * @since 4.2.0
 */
class tc_Update {
    
    /**
     * Execute this function to start a database update
     * @since 4.2.0
     */
    public static function force_update () {
        global $wpdb;
        $db_version = get_tc_option('db-version');
        $software_version = get_tc_version();
        $update_level = '0';
        
        // Fallback for very old teachcourses systems
        if ( $db_version == '' ) {
            $db_version = $wpdb->get_var("SELECT `value` FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'db-version'");
        }
        
        // if is the current one
        if ( $db_version === $software_version ) {
            get_tc_message( __('An update is not necessary.','teachcourses') );
            return;
        }
        
        // charset & collate like WordPress
        $charset_collate = ( !empty($wpdb->charset) ) ? "CHARACTER SET $wpdb->charset" : "CHARACTER SET utf8";
        if ( ! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
        else {
            $charset_collate .= " COLLATE utf8_general_ci";
        }
        
        // set capabilities
        global $wp_roles;
        $role = $wp_roles->get_role('administrator');
        if ( !$role->has_cap('use_teachcourses') ) {
            $wp_roles->add_cap('administrator', 'use_teachcourses');
        }
        if ( !$role->has_cap('use_teachcourses_courses') ) {
            $wp_roles->add_cap('administrator', 'use_teachcourses_courses');
        }
        
        // Disable foreign key checks
        if ( TEACHCOURSES_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 0");
        }
        
        
        // Add teachcourses options
        tc_Update::add_options();
        
        // Enable foreign key checks
        if ( TEACHCOURSES_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 1");
        }
        
        tc_Update::finalize_update($software_version);
   }
    
    /**
     * Renames a table
     * @param string $oldname
     * @param string $newname
     * @return boolean
     * @since 8.0.0
     */
    private static function rename_table($oldname, $newname) {
        
        global $wpdb;
        // Check if the old table exists
        if( $wpdb->get_var("SHOW TABLES LIKE '" . esc_sql($oldname) . "'") == esc_sql($oldname) ) {
            $wpdb->query('RENAME TABLE ' . esc_sql($oldname) . ' TO ' . esc_sql($newname) . '');
            return true;
        }
        return false;
        
    }

    /**
     * Add possible missing options
     * @since 4.2.0
     */
    private static function add_options () {
        global $wpdb;
        // Stylesheet
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE variable = 'stylesheet' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (variable, value, category) VALUES ('stylesheet', '1', 'system')"); 
        }
        // Sign out
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE variable = 'sign_out' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . "(variable, value, category) VALUES ('sign_out', '0', 'system')"); 
        }
        // Login
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE variable = 'login' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (variable, value, category) VALUES ('login', 'std', 'system')"); 
        }
        // rel_page_courses
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE variable = 'rel_page_courses' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (variable, value, category) VALUES ('rel_page_courses', 'page', 'system')"); 
        }
        // rel_page_publications
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE variable = 'rel_page_publications' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (variable, value, category) VALUES ('rel_page_publications', 'page', 'system')"); 
        }
        
        /**** since version 4.2.0 ****/
        
        // rel_content_template
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'rel_content_template' AND `category` = 'system'") == '0') {
            $value = '[tpsingle [key]]<!--more-->' . "\n\n[tpabstract]\n\n[tplinks]\n\n[tpbibtex]";
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_template', '$value', 'system')"); 
        }
        // rel_content_auto
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'rel_content_auto' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_auto', '0', 'system')"); 
        }
        // rel_content_category
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'rel_content_category' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_category', '', 'system')"); 
        }
        // import_overwrite
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'import_overwrite' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('import_overwrite', '0', 'system')"); 
        }
        
        /**** since version 5.0.0 ****/
        // fix old entries
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'sem' AND `category` = ''") == '0') {
            $wpdb->query("UPDATE " . TEACHCOURSES_SETTINGS . " SET `category` = 'system' WHERE `variable` = 'sem'");
        }
        
        // convert_bibtex
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'convert_bibtex' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('convert_bibtex', '0', 'system')"); 
        }
        
        // course_of_studies
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'course_of_studies' AND `category` = 'teachcourses_stud'") == '0') {
            $value = 'name = {course_of_studies}, title = {' . __('Course of studies','teachcourses') . '}, type = {SELECT}, required = {false}, min = {false}, max = {false}, step = {false}, visibility = {admin}';
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('course_of_studies', '$value', 'teachcourses_stud')"); 
        }
        // semester_number
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'semester_number' AND `category` = 'teachcourses_stud'") == '0') {
            $value = 'name = {semester_number}, title = {' . __('Semester number','teachcourses') . '}, type = {INT}, required = {false}, min = {1}, max = {99}, step = {1}, visibility = {normal}';
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('semester_number', '$value', 'teachcourses_stud')"); 
        }
        /**** since version 5.0.3 ****/
        // Fix an installer bug (wrong template for related content)
        if ( get_tc_option('rel_content_template') == 'page' ) {
            tc_Options::change_option('rel_content_template', '[tpsingle [key]]<!--more-->' . "\n\n[tpabstract]\n\n[tplinks]\n\n[tpbibtex]");
        }
    }
    
    /**
     * Update version information in the database
     * @param string $version
     * @since 4.2.0
     */
    private static function finalize_update ($version) {
        global $wpdb;
        $version = htmlspecialchars( esc_sql( $version ) );
        $wpdb->query("UPDATE " . TEACHCOURSES_SETTINGS . " SET `value` = '$version', `category` = 'system' WHERE `variable` = 'db-version'");
        get_tc_message( __('Update successful','teachcourses') );
    }
}