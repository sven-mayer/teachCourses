<?php
/**
 * This file contains all functions for updating a teachcorses database
 * 
 * @package teachcorses\core\update
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 4.2.0
 */

/**
 * This class contains all functions for updating a teachcorses database
 * @package teachcorses\core\update
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
        
        // Fallback for very old teachCorses systems
        if ( $db_version == '' ) {
            $db_version = $wpdb->get_var("SELECT `value` FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'db-version'");
        }
        
        // if is the current one
        if ( $db_version === $software_version ) {
            get_tc_message( __('An update is not necessary.','teachcorses') );
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
        if ( !$role->has_cap('use_teachcorses') ) {
            $wp_roles->add_cap('administrator', 'use_teachcorses');
        }
        if ( !$role->has_cap('use_teachcorses_courses') ) {
            $wp_roles->add_cap('administrator', 'use_teachcorses_courses');
        }
        
        // Disable foreign key checks
        if ( TEACHCOURSES_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 0");
        }
        
        
        // Add teachCorses options
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
     * Checks if the table teachcorses_authors needs to be filled. Returns false if not.
     * @return boolean
     * @since 5.0.0
     */
    public static function check_table_authors () {
        global $wpdb;
        $test_pub = $wpdb->get_var("SELECT COUNT(`pub_id`) FROM " . TEACHCOURSES_PUB);
        $test_authors = $wpdb->get_var("SELECT COUNT(`author_id`) FROM " . TEACHCOURSES_AUTHORS);
        if ( $test_authors == 0 && $test_pub != 0 ) {
            return true;
        }
        return false;
    }
    
    /**
     * Checks if the table teachcorses_stud_meta needs to be filled. Returns false if not.
     * @return boolean
     * @since 5.0.0
     */
    public static function check_table_stud_meta () {
        global $wpdb;
        $test_stud = $wpdb->get_var("SELECT COUNT(`wp_id`) FROM " . TEACHCOURSES_STUD);
        $test_stud_meta = $wpdb->get_var("SELECT COUNT(`wp_id`) FROM " . TEACHCOURSES_STUD_META);
        if ( $test_stud != 0 && $test_stud_meta == 0 ) {
            return true;
        }
        return false;
    }
    
    /**
     * Prepares and Returns the statement for adding all author - publications relations in one SQL Query
     * Returns a string like: ('pub_id', 'author_id', 'is_author', 'is_editor'), ('pub_id', 'author_id', 'is_author', 'is_editor'),...
     * 
     * @param int $pub_id               The ID of the publication
     * @param string $input_string      A author / editor string
     * @param string $delimiter         default is ','
     * @param string $rel_type          authors or editors
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function prepare_relation ($pub_id, $input_string, $delimiter = ',', $rel_type = 'authors') {
        global $wpdb;
        $pub_id = intval($pub_id);
        $array = explode($delimiter, $input_string);
        $return = '';
        foreach($array as $element) {
            $element = trim($element);
            
            if ( $element === '' ) {
                continue;
            }
            
            $element = esc_sql( htmlspecialchars($element) );
            
            // check if element exists
            $check = $wpdb->get_var("SELECT `author_id` FROM " . TEACHCOURSES_AUTHORS . " WHERE `name` = '$element'");
            
            // if element not exists
            if ( $check === NULL ){
                $check = tc_Authors::add_author( $element, tc_Bibtex::get_lastname($element) );
            }
            
            // prepare relation
            $is_author = ( $rel_type === 'authors' ) ? 1 : 0;
            $is_editor = ( $rel_type === 'editors' ) ? 1 : 0;
            $check = intval($check);
            $return = ($return === '') ? "($pub_id, $check, $is_author, $is_editor)" : $return . ", ($pub_id, $check, $is_author, $is_editor)";
            
        }
        return $return;
    }

    /**
     * Use this function to fill up the table teachcorses_authors with data from teachcorses_pub
     * @param string $limit     A normal SQL limit like 0,500. By default this value is not set.
     * @since 5.0.0
     */
    public static function fill_table_authors ($limit = '') {
        global $wpdb;
        
        // Try to set the time limit for the script
        set_time_limit(TEACHCOURSES_TIME_LIMIT);
        
        if ( $limit !== '' ) {
            $limit = ' LIMIT ' . esc_sql($limit);
        }
        
        $relation = '';
        get_tc_message( __('Step 1: Read data and add authors','teachcorses') );
        $pubs = $wpdb->get_results("SELECT pub_id, author, editor FROM " . TEACHCOURSES_PUB . $limit, ARRAY_A);
        foreach ( $pubs as $row ) {
            if ( $row['author'] != '' ) {
                $relation .= self::prepare_relation($row['pub_id'], $row['author'], ' and ', 'authors') . ', ';
            }
            if ( $row['editor'] != '' ) {
                $relation .= self::prepare_relation($row['pub_id'], $row['editor'], ' and ', 'editors') . ', ';
            }
        }
        $relation = substr($relation, 0, -2);
        $relation = str_replace(', ,', ',', $relation);
        get_tc_message( __('Step 2: Add relations between authors and publications','teachcorses') );
        $wpdb->query("INSERT INTO " . TEACHCOURSES_REL_PUB_AUTH . " (`pub_id`, `author_id`, `is_author`, `is_editor`) VALUES $relation");
        get_tc_message( __('Update successful','teachcorses') );
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
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'course_of_studies' AND `category` = 'teachcorses_stud'") == '0') {
            $value = 'name = {course_of_studies}, title = {' . __('Course of studies','teachcorses') . '}, type = {SELECT}, required = {false}, min = {false}, max = {false}, step = {false}, visibility = {admin}';
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('course_of_studies', '$value', 'teachcorses_stud')"); 
        }
        // semester_number
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'semester_number' AND `category` = 'teachcorses_stud'") == '0') {
            $value = 'name = {semester_number}, title = {' . __('Semester number','teachcorses') . '}, type = {INT}, required = {false}, min = {1}, max = {99}, step = {1}, visibility = {normal}';
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('semester_number', '$value', 'teachcorses_stud')"); 
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
        get_tc_message( __('Update successful','teachcorses') );
    }
}