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
        
        // force updates to reach structure of teachCorses 2.0.0
        if ( $db_version[0] === '0' || $db_version[0] === '1' ) {
            tc_Update::upgrade_table_teachcorses_ver($charset_collate);
            tc_Update::upgrade_table_teachcorses_beziehung($charset_collate);
            tc_Update::upgrade_table_teachcorses_kursbelegung($charset_collate);
            tc_Update::upgrade_table_teachcorses_einstellungen($charset_collate);
            tc_Update::upgrade_table_teachcorses_stud_to_20($charset_collate);
            tc_Update::upgrade_table_teachcorses_pub_to_04($charset_collate);
            tc_Update::upgrade_table_teachcorses_pub_to_20($charset_collate);
            $update_level = '2';
        }
        
        // force updates to reach structure of teachCorses 3.0.0
        if ( $db_version[0] === '2' || $update_level === '2' ) {
            tc_Update::upgrade_to_30();
            $update_level = '3';
        }
        
        // force updates to reach structure of teachCorses 3.1.0
        if ( $db_version[0] === '3' || $update_level === '3' ) {
            tc_Update::upgrade_to_31($charset_collate);
            $update_level = '4';
        }
        
        // force updates to reach structure of teachCorses 4.2.0
        if ( $db_version[0] === '4' || $update_level === '4' ) {
            tc_Update::upgrade_to_40($charset_collate);
            tc_Update::upgrade_to_41();
            tc_Update::upgrade_to_42($charset_collate);
            $update_level = '5';
        }
        
        // force updates to reach structure of teachCorses 5.0.0
        if ( $db_version[0] === '5' || $update_level === '5' ) {
            tc_Update::upgrade_to_50($charset_collate);
            $update_level = '6';
        }
        
        // force updates to reach structure of teachCorses 6.0.0
        if ( $db_version[0] === '6' || $update_level === '6' ) {
            tc_Update::upgrade_to_60($charset_collate);
            $update_level = '7';
        }
        
        // force updates to reach structure of teachCorses 7.0.0
        if ( $db_version[0] === '7' || $update_level === '7' ) {
            tc_Update::upgrade_to_70();
            tc_Update::upgrade_to_71();
            $update_level = '8';
        }
        
        // force updates to reach structure of teachCorses 7.0.0
        if ( $db_version[0] === '8' || $update_level === '8' ) {
            tc_Update::upgrade_to_80();
            tc_Update::upgrade_to_81($charset_collate);
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
    * Replace the old table "teachcorses_ver" with "teachcorses_courses" and copy all data
    * @param string $charset_collate
    * @since 4.2.0
    */
   private static function upgrade_table_teachcorses_ver ($charset_collate) {
        global $wpdb;
        $teachcorses_ver = $wpdb->prefix . 'teachcorses_ver';

        if ($wpdb->query("SHOW COLUMNS FROM $teachcorses_ver LIKE 'veranstaltungs_id'") == '1') {
            // create new table teachcorses_courses
            if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_COURSES . "'") != TEACHCOURSES_COURSES) {
                $sql = "CREATE TABLE " . TEACHCOURSES_COURSES . " ( `course_id` INT UNSIGNED AUTO_INCREMENT, `name` VARCHAR(100), `type` VARCHAR(100), `room` VARCHAR(100), `lecturer` VARCHAR (100), `date` VARCHAR(60), `places` INT(4), `start` DATETIME, `end` DATETIME, `semester` VARCHAR(100), `comment` VARCHAR(500), `rel_page` INT, `parent` INT, `visible` INT(1), `waitinglist` INT(1), `image_url` VARCHAR(400), `strict_signup` INT(1), PRIMARY KEY (course_id)
                ) $charset_collate;";			
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachcorses_ver");
            foreach ($row as $row) {
                $sql = "INSERT INTO " . TEACHCOURSES_COURSES . " (`course_id`, `name`, `type`, `room`, `lecturer`, `date`, `places`, `start`, `end`, `semester`, `comment`, `rel_page`, `parent`, `visible`, `waitinglist`) VALUES('$row->veranstaltungs_id', '$row->name', '$row->vtyp', '$row->raum', '$row->dozent', '$row->termin', '$row->plaetze', '$row->startein', '$row->endein', '$row->semester', '$row->bemerkungen', '$row->rel_page', '$row->parent', '$row->sichtbar', '$row->warteliste')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachcorses_ver");
        }
    }
    
    /**
     * Replace the old table "teachcorses_beziehung" with "teachcorses_relation" and copy all data
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachcorses_beziehung ($charset_collate) {
        global $wpdb;
        $teachcorses_beziehung = $wpdb->prefix . 'teachcorses_beziehung';
        if ($wpdb->query("SHOW COLUMNS FROM $teachcorses_beziehung LIKE 'belegungs_id'") == '1') {
            // create new table teachcorses_relation
            if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_RELATION . "'") != TEACHCOURSES_RELATION) {
                $sql = "CREATE TABLE " . TEACHCOURSES_RELATION . " ( `con_id` INT UNSIGNED AUTO_INCREMENT, `pub_id` INT UNSIGNED, `tag_id` INT UNSIGNED, FOREIGN KEY (pub_id) REFERENCES " . TEACHCOURSES_PUB . " (pub_id), FOREIGN KEY (tag_id) REFERENCES " . TEACHCOURSES_TAGS . " (tag_id), PRIMARY KEY (con_id) ) $charset_collate;";
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachcorses_beziehung");
            foreach ($row as $row) {
                $sql = "INSERT INTO " . TEACHCOURSES_RELATION . " (`con_id`, `pub_id`, `tag_id`) VALUES('$row->belegungs_id', '$row->pub_id', '$row->tag_id')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachcorses_beziehung");
        }
    }
    
    /**
     * Replace the old table "teachcorses_kursbelegung" with "teachcorses_signup" and copy all data
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachcorses_kursbelegung ($charset_collate) {
        global $wpdb;
        $teachcorses_kursbelegung = $wpdb->prefix . 'teachcorses_kursbelegung';
        if ($wpdb->query("SHOW COLUMNS FROM $teachcorses_kursbelegung LIKE 'belegungs_id'") == '1') {
            // create new table teachcorses_signup
            if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_SIGNUP . "'") != TEACHCOURSES_SIGNUP) {
                $sql = "CREATE TABLE " . TEACHCOURSES_SIGNUP . " (`con_id` INT UNSIGNED AUTO_INCREMENT, `course_id` INT UNSIGNED, `wp_id` INT UNSIGNED, `waitinglist` INT(1) UNSIGNED, `date` DATETIME, FOREIGN KEY (course_id) REFERENCES " . TEACHCOURSES_COURSES . " (course_id), FOREIGN KEY (wp_id) REFERENCES " . TEACHCOURSES_STUD . " (wp_id), PRIMARY KEY (con_id) ) $charset_collate;";
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachcorses_kursbelegung");
            foreach ($row as $row) {
                $sql = "INSERT INTO " . TEACHCOURSES_SIGNUP . " (`con_id`, `course_id`, `wp_id`, `waitinglist`, `date`) VALUES('$row->belegungs_id', '$row->veranstaltungs_id', '$row->wp_id', '$row->warteliste', '$row->datum')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachcorses_kursbelegung");
        }
    }
    
    /**
     * Replace the old table "teachcorses_einstellungen" with "teachcorses_settings" and copy all data
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachcorses_einstellungen ($charset_collate) {
        global $wpdb;
        $teachcorses_einstellungen = $wpdb->prefix . 'teachcorses_einstellungen';
        if ($wpdb->query("SHOW COLUMNS FROM $teachcorses_einstellungen LIKE 'einstellungs_id'") == '1') {
            // create new table teachcorses_settings
            if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHCOURSES_SETTINGS . "'") != TEACHCOURSES_SETTINGS) {
                $sql = "CREATE TABLE " . TEACHCOURSES_SETTINGS . " ( `setting_id` INT UNSIGNED AUTO_INCREMENT, `variable` VARCHAR (100), `value` VARCHAR (400), `category` VARCHAR (100), PRIMARY KEY (setting_id) ) $charset_collate;";				
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachcorses_einstellungen");
            foreach ($row as $row) {
                if ($row->category == 'studiengang') {
                    $row->category = 'course_of_studies';
                }
                if ($row->category == 'veranstaltungstyp') {
                    $row->category = 'course_type';
                }
                $sql = "INSERT INTO " . TEACHCOURSES_SETTINGS . " (`setting_id`, `variable`, `value`, `category`) VALUES('$row->einstellungs_id', '$row->variable', '$row->wert', '$row->category')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachcorses_einstellungen");
        }
    }
    
    /**
     * Upgrade table "teachCorses_stud" to teachCorses 2.x structure
     * @param string $charset_collate
     */
    private static function upgrade_table_teachcorses_stud_to_20 ($charset_collate) {
        global $wpdb;
        // rename column vorname to firstname
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_STUD . " LIKE 'vorname'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . " CHANGE `vorname` `firstname` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column nachname to lastname
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_STUD . " LIKE 'nachname'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . " CHANGE `nachname` `lastname` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column studiengang to course_of_studies
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_STUD . " LIKE 'studiengang'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . " CHANGE `studiengang` `course_of_studies` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column urzkurz to userlogin
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_STUD . " LIKE 'urzkurz'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . " CHANGE `urzkurz` `userlogin` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column gebdat to birthday
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_STUD . " LIKE 'gebdat'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . " CHANGE `gebdat` `birthday` DATE $charset_collate NULL DEFAULT NULL");
        }
        // rename column fachsemester to semesternumber
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_STUD . " LIKE 'fachsemester'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . "CHANGE `fachsemester` `semesternumber` INT(2) NULL DEFAULT NULL");
        }
        // rename column matrikel to matriculation_number
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_STUD . " LIKE 'matrikel'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . " CHANGE `matrikel` `matriculation_number` INT NULL DEFAULT NULL");
        }
    }
    
    /**
     * Upgrade table "teachCorses_pub" to teachCorses 0.40 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachcorses_pub_to_04 ($charset_collate) {
        global $wpdb;
        // add column image_url
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'image_url'") == '0' ) { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `image_url` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `comment`");
        }
        // add colum rel_page
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'rel_page'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `rel_page` INT NULL AFTER `image_url`");
        }
        // add column is_isbn
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'is_isbn'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `is_isbn` INT(1) NULL DEFAULT NULL AFTER `rel_page`");
        }
    }
    
    /**
     * Upgrade table "teachCorses_pub" to teachCorses 2.x structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachcorses_pub_to_20 ($charset_collate) {
        global $wpdb;
        // Rename sort to date
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'sort'") == '1' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE  `sort`  `date` DATE NULL DEFAULT NULL");
        }
        // Rename typ to type
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . "LIKE 'typ'") == '1' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `typ`  `type` VARCHAR( 50 ) $charset_collate NULL DEFAULT NULL");
            // remane publication types
            $row = $wpdb->get_results("SELECT pub_id, type  FROM " . TEACHCOURSES_PUB . "");
            foreach ($row as $row) {
                if ($row->type === 'Buch') {
                    $wpdb->query("UPDATE " . TEACHCOURSES_PUB . " SET type = 'book' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Chapter in book') {
                    $wpdb->query("UPDATE " . TEACHCOURSES_PUB . " SET type = 'inbook' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Conference paper') {
                    $wpdb->query("UPDATE " . TEACHCOURSES_PUB . " SET type = 'proceedings' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Journal article') {
                    $wpdb->query("UPDATE " . TEACHCOURSES_PUB . " SET type = 'article' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Vortrag') {
                    $wpdb->query("UPDATE " . TEACHCOURSES_PUB . " SET type = 'presentation' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Bericht') {
                    $wpdb->query("UPDATE " . TEACHCOURSES_PUB . " SET type = 'techreport' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Sonstiges') {
                    $wpdb->query("UPDATE " . TEACHCOURSES_PUB . " SET type = 'misc' WHERE pub_id = '$row->pub_id'");
                }
            }
        }
        // Rename autor to author
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'autor'") == '1' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `autor` `author` VARCHAR( 500 ) $charset_collate NULL DEFAULT NULL");
        }
        // Drop column jahr
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'jahr'") == '1' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " DROP `jahr`");
        }
        // insert column bibtex
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'bibtex'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `bibtex` VARCHAR(50) $charset_collate NULL DEFAULT NULL AFTER `type`");
        }
        // insert column editor
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'editor'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `editor` VARCHAR(500) $charset_collate NULL DEFAULT NULL AFTER `author`");
        }
        // insert column booktitle
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'booktitle'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `booktitle` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `date`");
        }
        // insert column journal
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'journal'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `journal` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `booktitle`");
        }
        // insert column volume
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'volume'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `volume` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `journal`");
        }
        // insert column number
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'number'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `number` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `volume`");
        }
        // insert column pages
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'pages'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `pages` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `number`");
        }
        // insert column publisher
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'publisher'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `publisher` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `pages`");
        }
        // insert column address
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'address'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `address` VARCHAR(300) $charset_collate NULL DEFAULT NULL AFTER `publisher`");
        }
        // insert column edition
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'edition'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `edition` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `address`");
        }
        // insert column chapter
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'chapter'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `chapter` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `edition`");
        }
        // insert column institution
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'institution'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `institution` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `chapter`");
        }
        // insert column organization
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'organization'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `organization` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `institution`");
        }
        // insert column school
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'school'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `school` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `organization`");
        }
        // insert column series
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'series'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `series` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `school`");
        }
        // insert column crossref
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'crossref'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `crossref` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `series`");
        }
        // insert column abstract
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'abstract'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `abstract` TEXT $charset_collate NULL DEFAULT NULL AFTER `crossref`");
        }
        // insert column howpublished
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'howpublished'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `howpublished` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `abstract`");
        }
        // insert column key
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'key'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `key` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `howpublished`");
        }
        // insert column techtype
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'techtype'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `techtype` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `key`");
        }
        // insert column note
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'note'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `note` TEXT $charset_collate NULL DEFAULT NULL AFTER `comment`");
        }
        // drop column verlag
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'verlag'") == '1') {
            $row = $wpdb->get_results("SELECT pub_id, verlag  FROM " . TEACHCOURSES_PUB . "");
            foreach ($row as $row) {
                $wpdb->query("UPDATE " . TEACHCOURSES_PUB . " SET `publisher` = '$row->verlag' WHERE `pub_id` = '$row->pub_id'");
            }
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " DROP `verlag`");
        }
    }
    
    /**
     * Upgrade table "teachcorses_courses" to teachCorses 3.0 structure
     * @since 4.2.0
     */
    private static function upgrade_to_30 () {
        global $wpdb;
        
        // teachcorses_courses
        // change type in column start
        $wpdb->get_results("SELECT `start` FROM " . TEACHCOURSES_COURSES);
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `" . TEACHCOURSES_COURSES . "` CHANGE  `start`  `start` DATETIME NULL DEFAULT NULL");
        }
        // change type in column end
        $wpdb->get_results("SELECT `end` FROM " . TEACHCOURSES_COURSES);
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `" . TEACHCOURSES_COURSES . "` CHANGE  `end`  `end` DATETIME NULL DEFAULT NULL");
        }
        // insert column strict_signup
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_COURSES . " LIKE 'strict_signup'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSES . " ADD `strict_signup` INT( 1 ) NULL DEFAULT NULL");
        }
        
        // teachcorses_signup
        // Change type in column date
        $wpdb->get_results("SELECT `date` FROM " . TEACHCOURSES_SIGNUP);
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `" . TEACHCOURSES_SIGNUP . "` CHANGE `date` `date` DATETIME NULL DEFAULT NULL");
        }
    }
    
    /**
     * Database upgrade to teachCorses 3.1.3 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_31 ($charset_collate) {
        global $wpdb;
        // change type in column url
        $wpdb->get_results("SELECT `url` FROM " . TEACHCOURSES_PUB);
        if ($wpdb->get_col_info('type', 0) == 'string') {
            $wpdb->query("ALTER TABLE `" . TEACHCOURSES_PUB . "` CHANGE `url` `url` TEXT $charset_collate NULL DEFAULT NULL");
        }
        // drop table teachcorses_log
        $table_name = $wpdb->prefix . 'teachcorses_log';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $wpdb->query("DROP TABLE " . $table_name . "");
        }
        // Drop column fplaces
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_COURSES . " LIKE 'fplaces'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSES . " DROP `fplaces`");
        }
        // Change type in column birthday
        // Fixed a bug with the installer in teachcorses versions 2.0.0 to 2.1.0
        $wpdb->get_results("SELECT `birthday` FROM " . TEACHCOURSES_STUD);
        if ($wpdb->get_col_info('type', 0) != 'date') {
            $wpdb->query("ALTER TABLE `" . TEACHCOURSES_STUD . "` CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL");
        }
        // Change database engine
        $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHCOURSES_SETTINGS . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHCOURSES_TAGS . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSES . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHCOURSES_SIGNUP . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHCOURSES_RELATION . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHCOURSES_USER . " ENGINE = INNODB");
    }
    
    /**
     * Database upgrade to teachCorses 4.0.0 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_40 ($charset_collate) {
        global $wpdb;
        // rename column name to title
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'name'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `name` `title` VARCHAR( 500 ) $charset_collate NULL DEFAULT NULL");
        }
        // add column urldate
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'urldate'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `urldate` DATE NULL DEFAULT NULL AFTER `date`");
        }
    }
    
    /**
     * Database upgrade to teachCorses 4.1.0 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_41 () {
        global $wpdb;
        // add column urldate
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'issuetitle'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `issuetitle` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `booktitle`");
        }
    }
    
    /**
     * Database upgrade to teachCorses 4.2.0 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_42 ($charset_collate) {
        global $wpdb;
        // expand char limit for tc_settings::value
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_SETTINGS . " LIKE 'value'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_SETTINGS . " CHANGE `value` `value` TEXT $charset_collate NULL DEFAULT NULL");
        }
    }
    
    /**
     * Database upgrade to teachCorses 5.0.0 structure
     * @param string $charset_collate
     * @since 5.0.0
     */
    private static function upgrade_to_50 ($charset_collate){
        global $wpdb;
        $charset = tc_Tables::get_charset();
        // add new tables
        tc_Tables::add_table_artefacts($charset);
        tc_Tables::add_table_assessments($charset);
        tc_Tables::add_table_course_capabilities($charset);
        tc_Tables::add_table_course_documents($charset);
        tc_Tables::add_table_course_meta($charset);
        tc_Tables::add_table_authors($charset);
        tc_Tables::add_table_rel_pub_auth($charset);
        tc_Tables::add_table_stud_meta($charset);
        tc_Tables::add_table_pub_meta($charset);
        
        // add column use_capabilities to table teachcorses_courses
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_COURSES . " LIKE 'use_capabilities'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSES . " ADD `use_capabilities` INT( 1 ) NULL DEFAULT NULL AFTER `strict_signup`");
        }
        
        // add column doi to table teachcorses_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'doi'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `doi` VARCHAR( 100 ) NULL DEFAULT NULL AFTER `image_url`");
        }
        
        // add column status to table teachcorses_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'status'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `status` VARCHAR( 100 ) NULL DEFAULT 'published' AFTER `rel_page`");
        }
        
        // add column added to table teachcorses_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'added'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `added` DATETIME NULL DEFAULT NULL AFTER `status`");
        }
        
        // add column modified to table teachcorses_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'modified'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `modified` DATETIME NULL DEFAULT NULL AFTER `added`");
        }
        
        // add column size to table teachcorses_course_documents
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_COURSE_DOCUMENTS . " LIKE 'size'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSE_DOCUMENTS . " ADD `size` BIGINT NULL DEFAULT NULL AFTER `added`");
        }
        
        // add column sort_name to table teachcorses_authors
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_AUTHORS . " LIKE 'sort_name'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_AUTHORS . " ADD `sort_name` VARCHAR( 500 ) NULL DEFAULT NULL AFTER `name`");
        }
        
        // expand char limit for tc_settings::value
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_SETTINGS . " LIKE 'value'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_SETTINGS . " CHANGE `value` `value` LONGTEXT $charset_collate NULL DEFAULT NULL");
        }
        
        // expand char limit for tc_publications::author
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'author'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `author` `author` VARCHAR (3000) $charset_collate NULL DEFAULT NULL");
        }
        
        // expand char limit for tc_publications::editor
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'editor'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `editor` `editor` VARCHAR (3000) $charset_collate NULL DEFAULT NULL");
        }
        
        // expand char limit for tc_publications::institution
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'institution'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `institution` `institution` VARCHAR (500) $charset_collate NULL DEFAULT NULL");
        }
        
        // expand char limit for tc_publications::organization
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'organization'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `organization` `organization` VARCHAR (500) $charset_collate NULL DEFAULT NULL");
        }
        
    }
    
    /**
     * Database upgrade to teachCorses 6.0.0 structure
     * @param string $charset_collate
     * @since 6.0.0
     */
    private static function upgrade_to_60 ($charset_collate){
        global $wpdb;
        $charset = tc_Tables::get_charset();
        
        // add new tables
        tc_Tables::add_table_pub_capabilities($charset);
        tc_Tables::add_table_pub_documents($charset);
        tc_Tables::add_table_pub_imports($charset);
        
        // add column use_capabilities to table teachcorses_courses
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'use_capabilities'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `use_capabilities` INT( 1 ) NULL DEFAULT NULL AFTER `modified`");
        }
        
        // add column import_id to table teachcorses_courses
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'import_id'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `import_id` INT NULL DEFAULT NULL AFTER `use_capabilities`");
        }
        
        // expand char limit for tc_publications::booktitle
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'booktitle'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `booktitle` `booktitle` VARCHAR (1000) $charset_collate NULL DEFAULT NULL");
        }
        
    }
    
    /**
     * Database upgrade to teachCorses 7.0.0 structure
     * @param string $charset_collate
     * @since 7.0.0
     */
    private static function upgrade_to_70 () {
        global $wpdb;
        
        // Try to raise the time limit
        set_time_limit(TEACHCOURSES_TIME_LIMIT);
        
        // ADD index to Table TEACHCOURSES_ARTEFACTS
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_ARTEFACTS . " WHERE key_name = 'ind_course_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_ARTEFACTS . " ADD INDEX `ind_course_id` (`course_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_ASSESSMENTS
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_ASSESSMENTS . " WHERE key_name = 'ind_course_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_ASSESSMENTS . " ADD INDEX `ind_course_id` (`course_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_ASSESSMENTS . " WHERE key_name = 'ind_artefact_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_ASSESSMENTS . " ADD INDEX `ind_artefact_id` (`artefact_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_ASSESSMENTS . " WHERE key_name = 'ind_wp_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_ASSESSMENTS . " ADD INDEX `ind_wp_id` (`wp_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_AUTHORS
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_AUTHORS . " WHERE key_name = 'ind_sort_name'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_AUTHORS . " ADD INDEX `ind_sort_name` (`sort_name`)");
        }
        
        // ADD index to Table TEACHCOURSES_COURSES
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_COURSES . " WHERE key_name = 'ind_semester'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSES . " ADD INDEX `ind_semester` (`semester`)");
        }
        
        // ADD index to Table TEACHCOURSES_COURSE_CAPABILITIES
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_COURSE_CAPABILITIES . " WHERE key_name = 'ind_course_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSE_CAPABILITIES . " ADD INDEX `ind_course_id` (`course_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_COURSE_CAPABILITIES . " WHERE key_name = 'ind_wp_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSE_CAPABILITIES . " ADD INDEX `ind_wp_id` (`wp_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_COURSE_DOCUMENTS
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_COURSE_DOCUMENTS . " WHERE key_name = 'ind_course_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSE_DOCUMENTS . " ADD INDEX `ind_course_id` (`course_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_COURSE_META
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_COURSE_META . " WHERE key_name = 'ind_course_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSE_META . " ADD INDEX `ind_course_id` (`course_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_PUB
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB . " WHERE key_name = 'ind_type'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD INDEX `ind_type` (`type`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB . " WHERE key_name = 'ind_date'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD INDEX `ind_date` (`date`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB . " WHERE key_name = 'ind_import_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD INDEX `ind_import_id` (`import_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB . " WHERE key_name = 'ind_key'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD INDEX `ind_key` (`key`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB . " WHERE key_name = 'ind_bibtex_key'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD INDEX `ind_bibtex_key` (`bibtex`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB . " WHERE key_name = 'ind_status'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD INDEX `ind_status` (`status`)");
        }
        
        // ADD index to Table TEACHCOURSES_PUB_CAPABILITIES
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB_CAPABILITIES . " WHERE key_name = 'ind_pub_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB_CAPABILITIES . " ADD INDEX `ind_pub_id` (`pub_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB_CAPABILITIES . " WHERE key_name = 'ind_wp_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB_CAPABILITIES . " ADD INDEX `ind_wp_id` (`wp_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_PUB_DOCUMENTS
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB_DOCUMENTS . " WHERE key_name = 'ind_pub_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB_DOCUMENTS . " ADD INDEX `ind_pub_id` (`pub_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_PUB_META
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_PUB_META . " WHERE key_name = 'ind_pub_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB_META . " ADD INDEX `ind_pub_id` (`pub_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_RELATION
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_RELATION . " WHERE key_name = 'ind_pub_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_RELATION . " ADD INDEX `ind_pub_id` (`pub_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_RELATION . " WHERE key_name = 'ind_tag_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_RELATION . " ADD INDEX `ind_tag_id` (`tag_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_REL_PUB_AUTH
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_REL_PUB_AUTH . " WHERE key_name = 'ind_pub_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_REL_PUB_AUTH . " ADD INDEX `ind_pub_id` (`pub_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_REL_PUB_AUTH . " WHERE key_name = 'ind_author_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_REL_PUB_AUTH . " ADD INDEX `ind_author_id` (`author_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_SIGNUP
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_SIGNUP . " WHERE key_name = 'ind_course_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_SIGNUP . " ADD INDEX `ind_course_id` (`course_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_SIGNUP . " WHERE key_name = 'ind_wp_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_SIGNUP . " ADD INDEX `ind_wp_id` (`wp_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_SIGNUP . " WHERE key_name = 'ind_date'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_SIGNUP . " ADD INDEX `ind_date` (`date`)");
        }
        
        // ADD index to Table TEACHCOURSES_STUD
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_STUD . " WHERE key_name = 'ind_userlogin'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD . " ADD INDEX `ind_userlogin` (`userlogin`)");
        }
        
        // ADD index to Table TEACHCOURSES_STUD_META
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_STUD_META . " WHERE key_name = 'ind_wp_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_STUD_META . " ADD INDEX `ind_wp_id` (`wp_id`)");
        }
        
        // ADD index to Table TEACHCOURSES_TAGS
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_TAGS . " WHERE key_name = 'ind_tag_name'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_TAGS . " ADD INDEX `ind_tag_name` (`name`)");
        }
        
        // ADD index to Table TEACHCOURSES_USER
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_USER . " WHERE key_name = 'ind_pub_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_USER . " ADD INDEX `ind_pub_id` (`pub_id`)");
        }
        if ($wpdb->query("SHOW INDEX FROM " . TEACHCOURSES_USER . " WHERE key_name = 'ind_user'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_USER . " ADD INDEX `ind_user` (`user`)");
        }
    }
    
    /**
     * Database upgrade to teachCorses 7.1.0 structure
     * @since 7.1.0
     */
    private static function upgrade_to_71() {
        global $wpdb;
        
        // add column image_target to table teachcorses_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'image_target'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `image_target` VARCHAR (100) NULL DEFAULT NULL AFTER `image_url`");
        }
        
        // add column image_ext to table teachcorses_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'image_ext'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `image_ext` VARCHAR (400) NULL DEFAULT NULL AFTER `image_target`");
        }
    }
    
    /**
     * Database upgrade to teachCorses 8.0.0 structure
     * @since 8.0.0
     */
    private static function upgrade_to_80() {
        global $wpdb; 
        // Rename teachcorses_course_capabilites to teachcorses_course_capabilities
        self::rename_table($wpdb->prefix . 'teachcorses_course_capabilites', TEACHCOURSES_COURSE_CAPABILITIES);
        
        // Rename teachcorses_course_capabilites to teachcorses_course_capabilities
        self::rename_table($wpdb->prefix . 'teachcorses_pub_capabilites', TEACHCOURSES_PUB_CAPABILITIES);
        
        // rename column TEACHCOURSES_PUB.use_capabilites to use_capabilities
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'use_capabilites'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `use_capabilites` `use_capabilities` INT(1) NULL DEFAULT NULL");
        }
        
        // rename column TEACHCOURSES_COURSES.use_capabilites to use_capabilities
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_COURSES . " LIKE 'use_capabilites'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_COURSES . " CHANGE `use_capabilites` `use_capabilities` INT(1) NULL DEFAULT NULL");
        }
        
    }
    
    /**
     * Database upgrade to teachCorses 8.1.0 structure
     * @param string $charset_collate
     */
    private static function upgrade_to_81( $charset_collate ) {
        global $wpdb;
        // expand char limit for teachcorses_pub::bibtex
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " LIKE 'bibtex'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " CHANGE `bibtex` `bibtex` VARCHAR (100) $charset_collate NULL DEFAULT NULL");
        }
        // add column issue to table teachcorses_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHCOURSES_PUB . " WHERE Field = 'issue'") == '0') { 
            $wpdb->query("ALTER TABLE " . TEACHCOURSES_PUB . " ADD `issue` VARCHAR(40) $charset_collate NULL DEFAULT NULL AFTER `journal`");
        }
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
     * Use this function to transfer all data from no longer used columns of teachcorses_stud to teachcorses_stud_meta
     * @since 5.0.0
     */
    public static function fill_table_stud_meta () {
        global $wpdb;
        // Try to set the time limit for the script
        set_time_limit(TEACHCOURSES_TIME_LIMIT);
        $relation = '';
        get_tc_message( __('Step 1: Read and prepare data','teachcorses') );
        $students = $wpdb->get_results("SELECT wp_id, course_of_studies, birthday, semesternumber, matriculation_number FROM " . TEACHCOURSES_STUD, ARRAY_A);
        foreach ( $students as $row ) {
            $relation .= "(" . $row['wp_id'] . ", 'course_of_studies', '" . $row['course_of_studies'] . "'), ";
            $relation .= "(" . $row['wp_id'] . ", 'birthday', '" . $row['birthday'] . "'), ";
            $relation .= "(" . $row['wp_id'] . ", 'semester_number', '" . $row['semesternumber'] . "'), ";
            $relation .= "(" . $row['wp_id'] . ", 'matriculation_number', '" . $row['matriculation_number'] . "'), ";
        }
        
        $relation = substr($relation, 0, -2);
        get_tc_message( __('Step 2: Insert data','teachcorses') );
        $wpdb->query("INSERT INTO " . TEACHCOURSES_STUD_META . " (`wp_id`, `meta_key`, `meta_value`) VALUES $relation");
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
        // birthday
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'birthday' AND `category` = 'teachcorses_stud'") == '0') {
            $value = 'name = {birthday}, title = {' . __('Birthday','teachcorses') . '}, type = {DATE}, required = {false}, min = {false}, max = {false}, step = {false}, visibility = {normal}';
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('birthday', '$value', 'teachcorses_stud')"); 
        }
        // semester_number
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'semester_number' AND `category` = 'teachcorses_stud'") == '0') {
            $value = 'name = {semester_number}, title = {' . __('Semester number','teachcorses') . '}, type = {INT}, required = {false}, min = {1}, max = {99}, step = {1}, visibility = {normal}';
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('semester_number', '$value', 'teachcorses_stud')"); 
        }
        // matriculation_number
        if ($wpdb->query("SELECT value FROM " . TEACHCOURSES_SETTINGS . " WHERE `variable` = 'matriculation_number' AND `category` = 'teachcorses_stud'") == '0') {
            $value = 'name = {matriculation_number}, title = {' . __('Matriculation number','teachcorses') . '}, type = {INT}, required = {false}, min = {1}, max = {1000000}, step = {1}, visibility = {admin}';
            $wpdb->query("INSERT INTO " . TEACHCOURSES_SETTINGS . " (`variable`, `value`, `category`) VALUES ('matriculation_number', '$value', 'teachcorses_stud')");
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