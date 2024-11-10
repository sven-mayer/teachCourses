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