<?php
/**
 * This file contains all functions for creating a database for teachcorses
 * 
 * @package teachcorses\core\installation
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * This class contains all functions for creating a database for teachcorses
 * @package teachcorses\core\installation
 * @since 5.0.0
 */
class tc_Tables {
    
    /**
     * Install teachCorses database tables
     * @since 5.0.0
     */
    public static function create() {
        global $wpdb;
        self::add_capabilities();
        
        $charset_collate = self::get_charset();
        
        // Disable foreign key checks
        if ( TEACHCOURSES_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 0");
        }
        
        // Settings
        self::add_table_settings($charset_collate);
        
        // Courses
        self::add_table_courses($charset_collate);
        self::add_table_course_meta($charset_collate);
        self::add_table_course_capabilities($charset_collate);
        self::add_table_course_documents($charset_collate);
        self::add_table_signup($charset_collate);
        self::add_table_artefacts($charset_collate);
        self::add_table_assessments($charset_collate);
        
        // Enable foreign key checks
        if ( TEACHCOURSES_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 1");
        }
    }
    
      /**
     * Remove teachCorses database tables
     * @since 5.0.0
     */
    public static function remove() {
        global $wpdb;
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0");
        $wpdb->query("DROP TABLE `" . TEACHCOURSES_ARTEFACTS . "`, 
                                `" . TEACHCOURSES_AUTHORS . "`, 
                                `" . TEACHCOURSES_COURSES . "`, 
                                `" . TEACHCOURSES_COURSE_CAPABILITIES . "`, 
                                `" . TEACHCOURSES_COURSE_DOCUMENTS . "`, 
                                `" . TEACHCOURSES_COURSE_META . "`, 
                                `" . TEACHCOURSES_RELATION ."`,
                                `" . TEACHCOURSES_REL_PUB_AUTH . "`, 
                                `" . TEACHCOURSES_SETTINGS ."`, 
                                `" . TEACHCOURSES_SIGNUP ."`, 
                                `" . TEACHCOURSES_TAGS . "`, 
                                `" . TEACHCOURSES_USER . "`");
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1");
    }
    
    /**
     * Returns an associative array with table status informations (Name, Engine, Version, Rows,...)
     * @param string $table
     * @return array
     * @since 5.0.0
     */
    public static function check_table_status($table){
        global $wpdb;
        return $wpdb->get_row("SHOW TABLE STATUS FROM " . DB_NAME . " WHERE `Name` = '$table'", ARRAY_A);
    }
    
    /**
     * Tests if the engine for the selected table is InnoDB. If not, the function changes the engine.
     * @param string $table
     * @since 5.0.0
     * @access private
     */
    private static function change_engine($table){
        global $wpdb;
        $db_info = self::check_table_status($table);
        if ( $db_info['Engine'] != 'InnoDB' ) {
            $wpdb->query("ALTER TABLE " . $table . " ENGINE = INNODB");
        }
    }

    /**
     * Create table teachcorses_courses
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_courses($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_COURSES . "'") == TEACHCOURSES_COURSES ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta("CREATE TABLE " . TEACHCOURSES_COURSES . " (
                    `course_id` INT UNSIGNED AUTO_INCREMENT,
                    `name` VARCHAR(100),
                    `type` VARCHAR (100),
                    `room` VARCHAR(100),
                    `lecturer` VARCHAR (100),
                    `date` VARCHAR(60),
                    `places` INT(4),
                    `start` DATETIME,
                    `end` DATETIME,
                    `semester` VARCHAR(100),
                    `comment` VARCHAR(500),
                    `rel_page` INT,
                    `parent` INT,
                    `visible` INT(1),
                    `waitinglist` INT(1),
                    `image_url` VARCHAR(400),
                    `strict_signup` INT(1),
                    `use_capabilities` INT(1),
                    PRIMARY KEY (`course_id`),
                    KEY `semester` (`semester`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHCOURSES_COURSES);
    }
    
    /**
     * Create table course_capabilities
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_course_capabilities($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_COURSE_CAPABILITIES . "'") == TEACHCOURSES_COURSE_CAPABILITIES ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta("CREATE TABLE " . TEACHCOURSES_COURSE_CAPABILITIES . " (
                    `cap_id` INT UNSIGNED AUTO_INCREMENT,
                    `wp_id` INT UNSIGNED,
                    `course_id` INT UNSIGNED,
                    `capability` VARCHAR(100),
                    PRIMARY KEY (`cap_id`),
                    KEY `ind_course_id` (`course_id`),
                    KEY `ind_wp_id` (`wp_id`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHCOURSES_COURSE_CAPABILITIES);
    }
    
    /**
     * Create table course_documents
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_course_documents($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_COURSE_DOCUMENTS . "'") == TEACHCOURSES_COURSE_DOCUMENTS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta("CREATE TABLE " . TEACHCOURSES_COURSE_DOCUMENTS . " (
                    `doc_id` INT UNSIGNED AUTO_INCREMENT,
                    `name` VARCHAR(500),
                    `path` VARCHAR(500),
                    `added` DATETIME,
                    `size` BIGINT,
                    `sort` INT,
                    `course_id` INT UNSIGNED,
                    PRIMARY KEY (doc_id),
                    KEY `ind_course_id` (`course_id`)
                ) $charset_collate;");
         
        // test engine
        self::change_engine(TEACHCOURSES_COURSE_DOCUMENTS);
    }
    
    /**
     * Create table teachcorses_course_meta
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_course_meta($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_COURSE_META . "'") == TEACHCOURSES_COURSE_META ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHCOURSES_COURSE_META . " (
                    `meta_id` INT UNSIGNED AUTO_INCREMENT,
                    `course_id` INT UNSIGNED,
                    `meta_key` VARCHAR(255),
                    `meta_value` TEXT,
                    PRIMARY KEY (meta_id),
                    KEY `ind_course_id` (`course_id`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHCOURSES_COURSE_META);
    }
    
    /**
     * Create table teachcorses_signup
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_signup($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_SIGNUP ."'") == TEACHCOURSES_SIGNUP ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHCOURSES_SIGNUP ." (
                    `con_id` INT UNSIGNED AUTO_INCREMENT,
                    `course_id` INT UNSIGNED,
                    `wp_id` INT UNSIGNED,
                    `waitinglist` INT(1) UNSIGNED,
                    `date` DATETIME,
                    PRIMARY KEY (con_id),
                    KEY `ind_course_id` (`course_id`),
                    KEY `ind_wp_id` (`wp_id`),
                    KEY `ind_date` (`date`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHCOURSES_SIGNUP);
    }
    
    /**
     * Create table teachcorses_artefacts
     * @param string $charset_collate
     * @since 5.0.0
     */
    


    
    public static function add_table_artefacts($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_ARTEFACTS . "'") == TEACHCOURSES_ARTEFACTS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHCOURSES_ARTEFACTS . " (
                    `artefact_id` INT UNSIGNED AUTO_INCREMENT,
                    `parent_id` INT UNSIGNED,
                    `course_id` INT UNSIGNED,
                    `title` VARCHAR(500),
                    `scale` TEXT,
                    `passed` INT(1),
                    `max_value` VARCHAR(50),
                    PRIMARY KEY (artefact_id),
                    KEY `ind_course_id` (`course_id`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHCOURSES_ARTEFACTS);
    }
    
    /**
     * Create table teachcorses_assessments
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_assessments($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_ASSESSMENTS . "'") == TEACHCOURSES_ASSESSMENTS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHCOURSES_ASSESSMENTS . " (
                    `assessment_id` INT UNSIGNED AUTO_INCREMENT,
                    `artefact_id` INT UNSIGNED,
                    `course_id` INT UNSIGNED,
                    `wp_id` INT UNSIGNED,
                    `value` VARCHAR(50),
                    `max_value` VARCHAR(50),
                    `type` VARCHAR(50),
                    `examiner_id` INT,
                    `exam_date` DATETIME,
                    `comment` TEXT,
                    `passed` INT(1),
                    PRIMARY KEY (assessment_id),
                    KEY `ind_course_id` (`course_id`),
                    KEY `ind_artefact_id` (`artefact_id`),
                    KEY `ind_wp_id` (`wp_id`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHCOURSES_ASSESSMENTS);
    }
    
    /**
     * Create table teachcorses_settings
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_settings($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_SETTINGS . "'") == TEACHCOURSES_SETTINGS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHCOURSES_SETTINGS . " (
                    `setting_id` INT UNSIGNED AUTO_INCREMENT,
                    `variable` VARCHAR (100),
                    `value` LONGTEXT,
                    `category` VARCHAR (100),
                    PRIMARY KEY (setting_id)
                    ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHCOURSES_SETTINGS);
        
        // Add default values
        self::add_default_settings();
    }
    
    /**
     * Add default system settings
     * @since 5.0.0
     */
    public static function add_default_settings(){
        global $wpdb;
        $value = '[tpsingle [key]]<!--more-->' . "\n\n[tpabstract]\n\n[tplinks]\n\n[tpbibtex]";
        $version = get_tc_version();
        
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('sem', 'Example term', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('db-version', '$version', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('sign_out', '0', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('login', 'std', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('stylesheet', '1', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_page_courses', 'page', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_auto', '0', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_template', '$value', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_category', '', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('import_overwrite', '1', 'system')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('convert_bibtex', '0', 'system')");
        // Example values
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('Example term', 'Example term', 'semester')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('Example', 'Example', 'course_of_studies')");	
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . "(`variable`, `value`, `category`) VALUES ('Lecture', 'Lecture', 'course_type')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . "(`variable`, `value`, `category`) VALUES ('Practical', 'Practical', 'course_type')");
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . "(`variable`, `value`, `category`) VALUES ('Seminar', 'Seminar', 'course_type')");
        
        // Register example meta data fields
        // course_of_studies
        $value = 'name = {course_of_studies}, title = {' . __('Course of studies','teachcorses') . '}, type = {SELECT}, required = {false}, min = {false}, max = {false}, step = {false}, visibility = {admin}';
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('course_of_studies', '$value', 'teachcorses_stud')"); 
        // birthday
        $value = 'name = {birthday}, title = {' . __('Birthday','teachcorses') . '}, type = {DATE}, required = {false}, min = {false}, max = {false}, step = {false}, visibility = {normal}';
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('birthday', '$value', 'teachcorses_stud')"); 
        // semester_number
        $value = 'name = {semester_number}, title = {' . __('Semester number','teachcorses') . '}, type = {INT}, required = {false}, min = {1}, max = {99}, step = {1}, visibility = {normal}';
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('semester_number', '$value', 'teachcorses_stud')"); 
        // matriculation_number
        $value = 'name = {matriculation_number}, title = {' . __('Matriculation number','teachcorses') . '}, type = {INT}, required = {false}, min = {1}, max = {1000000}, step = {1}, visibility = {admin}';
        $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('matriculation_number', '$value', 'teachcorses_stud')"); 
       
    }
    
    /**
     * Add capabilities
     * @since 5.0.0
     */
    private static function add_capabilities() {
        // 
        global $wp_roles;
        $role = $wp_roles->get_role('administrator');
        if ( !$role->has_cap('use_teachcorses') ) {
            $wp_roles->add_cap('administrator', 'use_teachcorses');
        }
        if ( !$role->has_cap('use_teachcorses_courses') ) {
            $wp_roles->add_cap('administrator', 'use_teachcorses_courses');
        }
    }
    
    /**
     * charset & collate like WordPress
     * @since 5.0.0
     */
    public static function get_charset() {
        global $wpdb; 
        $charset_collate = '';
        if ( ! empty($wpdb->charset) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }	
        if ( ! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
        $charset_collate .= " ENGINE = INNODB";
        return $charset_collate;
    }
    
}
