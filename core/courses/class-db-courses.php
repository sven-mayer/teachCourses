<?php
/**
 * This file contains the database access class for courses
 * @package teachcorses
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Contains functions for getting, adding and deleting of courses
 * @package teachcorses
 * @subpackage database
 * @since 5.0.0
 */
class tc_Courses {
    
    /**
     * Returns the capability ("owner" or "approved") of an user for a course. For courses with no capabilities "owner" is returned.
     * @param string $course_id     The course ID
     * @param string $wp_id         WordPress user ID
     * @return string
     * @since 5.0.0
     */
    public static function get_capability ($course_id, $wp_id){
        global $wpdb;
        $test = $wpdb->get_var("SELECT `use_capabilities` FROM " . TEACHCOURSES_COURSES . " WHERE `course_id` = '" . intval($course_id) . "'");
        
        if ( intval($test) === 1 ){
            return $wpdb->get_var("SELECT `capability` FROM " . TEACHCOURSES_COURSE_CAPABILITIES . " WHERE `course_id` = '" . intval($course_id) . "' AND `wp_id` = '" . intval($wp_id) . "'");
        }
        
        // Return owner if the course has no capabilities
        return 'owner';
    }

    /**
    * Get course capabilities
    * @param int $course_id         The course ID
    * @param string $output_type    OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
    * @return array|object
    * @since 5.0.0
    */
   public static function get_capabilities ($course_id, $output_type = ARRAY_A) {
       global $wpdb;
       return $wpdb->get_results("SELECT * FROM " . TEACHCOURSES_COURSE_CAPABILITIES . " WHERE `course_id` = '" . intval($course_id) . "'",$output_type);
   }

   /**
    * Delete course capability
    * @param int $cap_id    The capability ID
    * @since 5.0.0
    * @todo unused
    */
   public static function delete_capability ($cap_id) {
       global $wpdb;
       $wpdb->query("DELETE FROM " . TEACHCOURSES_COURSE_CAPABILITIES . " WHERE `cap_id` = '" . intval($cap_id) . "'");
   }
   
   /**
    * Checks if a user has a cap in the selected course
    * @param int $course_id         ID of a course
    * @param int $wp_id             WordPress user ID
    * @param string $capability     "owner" or "approved"
    * @return boolean
    * @since 5.0.0
    */
   public static function has_capability ($course_id, $wp_id, $capability) {
       global $wpdb;
       $where = '';
       
       if ( $capability !== '' ) {
           $where = "AND `capability` = '" . esc_sql($capability). "'";
       }
       
       $test = $wpdb->query("SELECT `wp_id` FROM " . TEACHCOURSES_COURSE_CAPABILITIES . " WHERE `course_id` = '" . intval($course_id) . "' AND `wp_id` = '" . intval($wp_id) . "' $where");
       
       if ( $test === 1 ) {
           return true;
       }
       
       return false;
   }
   
   /**
    * Checks if there is an owner of the selected course. If not, the function returns false, if yes, the user_id is returned.
    * @param int $course_id     The course ID
    * @return boolean|int
    * @since 5.0.0
    */
   public static function is_owner ($course_id) {
       global $wpdb;
       $test = $wpdb->get_var("SELECT `wp_id` FROM " . TEACHCOURSES_COURSE_CAPABILITIES . " WHERE `course_id` = '" . intval($course_id) . "' AND `capability` = 'owner'");
       
       if ( $test === NULL ){
           return false;
       }
       
       return intval($test);
       
   }
   
   /**
    * Checks if a post is used as related content for a course. If is true, the course ID will be returned otherwise it's false. 
    * @param int $post_id
    * @return int|boolean   Returns the course_id or false
    * @since 5.0.0
    */
   public static function is_used_as_related_content($post_id) {
       global $wpdb;
       $post_id = intval($post_id);
       
       if ( $post_id === 0 ) {
           return false;
       }
       
       return $wpdb->get_var("SELECT `course_id` FROM `" . TEACHCOURSES_COURSES . "` WHERE `rel_page` = '$post_id' ");
   }

    /**
     * Returns all data of a single course
     * @param int $course_id            The course ID
     * @param string $output_type       OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * @return mixed
     * @since 5.0.0
     */
    public static function get_course ($course_id, $output_type = OBJECT) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM `" . TEACHCOURSES_COURSES . "` WHERE `course_id` = '" . intval($course_id) . "'", $output_type);
        return $result;
    }
    
    /**
     * Returns all data of one or more courses
     * 
     * possible values for the array $args:
     *      @type string semester         The semester/term of the courses
     *      @type string visibility       The visibility of the coures (1,2,3) separated by comma
     *      @type string parent           The course_id of the parent
     *      @type string search           A general search string
     *      @type string exclude          The course IDs you want to exclude
     *      @type string order            Default: semester DESC, name
     *      @type string limit            The sql search limit, ie: 0,30
     *      @type string output_type      OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * 
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_courses ( $args = array() ) {
        $defaults = array(
            'semester'      => '',
            'visibility'    => '',
            'parent'        => '',
            'search'        => '',
            'exclude'       => '',
            'order'         => 'semester DESC, name',
            'limit'         => '',
            'output_type'   => OBJECT
        ); 
        $atts = wp_parse_args( $args, $defaults );

        global $wpdb;

        // Define basics
        $sql = "SELECT course_id, name, type, lecturer, date, room, places, start, end, semester, parent, visible, rel_page, comment, image_url, strict_signup, use_capabilities, parent_name
                FROM ( SELECT t.course_id AS course_id, t.name AS name, t.type AS type, t.lecturer AS lecturer, t.date AS date, t.room As room, t.places AS places, t.start AS start, t.end As end, t.semester AS semester, t.parent As parent, t.visible AS visible, t.rel_page AS rel_page, t.comment AS comment, t.image_url AS image_url, t.strict_signup AS strict_signup, t.use_capabilities AS use_capabilities, p.name AS parent_name 
                FROM " . TEACHCOURSES_COURSES . " t 
                LEFT JOIN " . TEACHCOURSES_COURSES . " p ON t.parent = p.course_id ) AS temp";
        
        // define global search
        $search = esc_sql(htmlspecialchars(stripslashes($atts['search'])));
        if ( $search != '' ) {
            $search = "`name` like '%$search%' OR `parent_name` like '%$search%' OR `lecturer` like '%$search%' OR `date` like '%$search%' OR `room` like '%$search%' OR `course_id` = '$search'";
        }
        
        // WHERE clause
        $nwhere = array();
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['exclude'], "p.pub_id", "AND", "!=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['semester'], "semester", "OR", "=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['visibility'], "visible", "OR", "=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['parent'], "parent", "OR", "=");
        $nwhere[] = ( $search != '') ? $search : null;
        
        $where = tc_DB_Helpers::compose_clause($nwhere);
        
        // LIMIT clause
        $limit = ( $atts['limit'] != '' ) ? 'LIMIT ' . esc_sql($atts['limit']) : '';

        // define order
        $order = esc_sql($atts['order']);
        if ( $order != '' ) {
            $order = " ORDER BY $order";
        }
        $result = $wpdb->get_results($sql . $where . $order . $limit, $atts['output_type']);
        return $result;
    }
    
    /** 
     * Returns a single value of a course 
     * @param int $course_id    The course ID
     * @param string $col       The name of the column
     * @return string
     * @since 5.0.0
    */  
    public static function get_course_data ($course_id, $col) {
        global $wpdb;
        $result = $wpdb->get_var("SELECT `" . esc_sql($col) . "` FROM `" . TEACHCOURSES_COURSES . "` WHERE `course_id` = '" . intval($course_id) . "'");
        return $result;
    }
    
    /**
     * Returns the course name under consideration of a possible parent course
     * @param int $course_id    The course ID
     * @return string
     * @since 5.0.6
     */
    public static function get_course_name ($course_id) {
        global $wpdb;
        $row = $wpdb->get_row("SELECT `name`, `parent` FROM " . TEACHCOURSES_COURSES . " WHERE `course_id` = '" . intval($course_id) . "'");
        if ($row->parent != '0') {
            $parent = tc_Courses::get_course_data($row->parent, 'name');
            $row->name = ( $row->name != $parent ) ? $parent . ' ' . $row->name : $row->name;
        }
        return $row->name;
    }
    
    /**
     * Returns course meta data
     * @param int $course_id        The course ID
     * @param string $meta_key      The name of the meta field (optional)
     * @return array
     * @since 5.0.0
     */
    public static function get_course_meta($course_id, $meta_key = ''){
        global $wpdb;
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '" . esc_sql($meta_key) . "'";
        }
        $sql = "SELECT * FROM " . TEACHCOURSES_COURSE_META . " WHERE `course_id` = '" . intval($course_id) . "' $where";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Add course meta
     * @param int $course_id        The course ID
     * @param string $meta_key      The name of the meta field
     * @param string $meta_value    The value of the meta field
     * @since 5.0.0
     */
    public static function add_course_meta ($course_id, $meta_key, $meta_value) {
        global $wpdb;
        $wpdb->insert( TEACHCOURSES_COURSE_META, array( 'course_id' => $course_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ), array( '%d', '%s', '%s' ) );
    }
    
    /**
     * Deletes curse meta
     * @param int $course_id    The course ID
     * @param string $meta_key  The name of the meta field
     * @since 5.0.0
     */
    public static function delete_course_meta ($course_id, $meta_key = '') {
        global $wpdb;
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '" . esc_sql($meta_key) . "'";
        }
        $wpdb->query("DELETE FROM " . TEACHCOURSES_COURSE_META . " WHERE `course_id` = '" . intval($course_id) . "' $where");
    }
    
    /** 
     * Add a new course
     * @param array $data       An associative array with data of the course
     * @param array $sub        An associative array with data for the sub courses (type, places, number)
     * @return int              ID of the new course
     * @since 5.0.0
    */
   public static function add_course($data, $sub) {
        global $wpdb;
        
        // prevent possible double escapes
        $data['name'] = stripslashes($data['name']);
        $data['type'] = stripslashes($data['type']);
        $data['room'] = stripslashes($data['room']);
        $data['lecturer'] = stripslashes($data['lecturer']);
        $data['comment'] = stripslashes($data['comment']);
        $data['semester'] = stripslashes($data['semester']);
        
        $data['start'] = $data['start'] . ' ' . $data['start_hour'] . ':' . $data['start_minute'] . ':00';
        $data['end'] = $data['end'] . ' ' . $data['end_hour'] . ':' . $data['end_minute'] . ':00';
        $wpdb->insert( 
                TEACHCOURSES_COURSES, 
                array( 
                    'name'              => $data['name'], 
                    'type'              => $data['type'], 
                    'room'              => $data['room'], 
                    'lecturer'          => $data['lecturer'], 
                    'date'              => $data['date'], 
                    'places'            => $data['places'], 
                    'start'             => $data['start'], 
                    'end'               => $data['end'], 
                    'semester'          => $data['semester'], 
                    'comment'           => $data['comment'], 
                    'rel_page'          => $data['rel_page'], 
                    'parent'            => $data['parent'], 
                    'visible'           => $data['visible'], 
                    'image_url'         => $data['image_url'], 
                    'strict_signup'     => $data['strict_signup'], 
                    'use_capabilities'  => $data['use_capabilities'] ), 
                array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d' ) );
        $course_id = $wpdb->insert_id;
        // create rel_page
        if ($data['rel_page_alter'] !== 0 ) {
            $data['rel_page'] = tc_Courses::add_rel_page($course_id, $data);
            // Update rel_page
            $wpdb->update( 
                    TEACHCOURSES_COURSES, 
                    array( 'rel_page' => $data['rel_page'] ), 
                    array( 'course_id' => $course_id ), 
                    array( '%d', ), 
                    array( '%d' ) );
        }
        // test if creation was successful
        if ( $data['rel_page'] === false ) {
            get_tc_message(__('Error while adding new related content.','teachcorses'), 'red');
        }
        // create sub courses
        if ( $sub['number'] !== 0 ) {
            tc_Courses::add_sub_courses($course_id, $data, $sub);
        }
        return $course_id;
    }
    
    /**
     * Adds a new related content to WordPress
     * @param int $course_id    The ID of the course
     * @param array $data       An associative array of the course data
     * @return int or false
     * @since 5.0.0
     * @access private
     */
    private static function add_rel_page($course_id, $data) {
        $post = get_post($data['rel_page_alter']);
        $content = str_replace('[course_id]', 'id="' . $course_id . '"', $post->post_content );
        $postarr = array ( 
            'post_title'    => $data['name'],
            'post_content'  => $content,
            'post_type'     => $post->post_type,
            'post_author'   => $post->post_author,
            'post_status'   => 'publish'
        );
        return wp_insert_post($postarr);
    }
    
    /**
     * Adds sub courses to a course
     * @param int $course_id    The ID of the parent course
     * @param array $data       An associative array with data of the parent course
     * @param array $sub        An associative array with data for the sub courses (type, places, number)
     * @since 5.0.0
     * @access private
     */
    private static function add_sub_courses($course_id, $data, $sub) {
        $sub_data = $data;
        $sub_data['parent'] = $course_id;
        $sub_data['places'] = $sub['places'];
        $sub_data['type'] = $sub['type'];
        $sub_data['rel_page'] = 0;
        $sub_data['rel_page_alter'] = 0;
        $options = array('number' => 0);
        for ( $i = 1; $i <= $sub['number']; $i++ ) {
            $sub_data['name'] = $sub['type'] . ' ' . $i;
            tc_Courses::add_course($sub_data, $options);
        }
    }
    
    /** 
     * Changes course data. Returns false if errors, or the number of rows affected if successful.
     * @param int $course_id    course ID
     * @param array $data       An associative array of couse data (name, places, type, room, ...)
     * @return int|false
     * @since 5.0.0
    */ 
   public static function change_course($course_id, $data){
        global $wpdb;
        $course_id = intval($course_id);
        global $current_user;
        $old_places = tc_Courses::get_course_data ($course_id, 'places');
        
        // prevent possible double escapes
        $data['name'] = stripslashes($data['name']);
        $data['type'] = stripslashes($data['type']);
        $data['room'] = stripslashes($data['room']);
        $data['lecturer'] = stripslashes($data['lecturer']);
        $data['comment'] = stripslashes($data['comment']);
        $data['semester'] = stripslashes($data['semester']);

        $data['start'] = $data['start'] . ' ' . $data['start_hour'] . ':' . $data['start_minute'] . ':00';
        $data['end'] = $data['end'] . ' ' . $data['end_hour'] . ':' . $data['end_minute'] . ':00';
        return $wpdb->update( 
                TEACHCOURSES_COURSES, 
                array( 
                    'name'              => $data['name'], 
                    'type'              => $data['type'], 
                    'room'              => $data['room'], 
                    'lecturer'          => $data['lecturer'], 
                    'date'              => $data['date'], 
                    'places'            => $data['places'], 
                    'start'             => $data['start'], 
                    'end'               => $data['end'], 
                    'semester'          => $data['semester'], 
                    'comment'           => $data['comment'], 
                    'rel_page'          => $data['rel_page'], 
                    'parent'            => $data['parent'], 
                    'visible'           => $data['visible'], 
                    'image_url'         => $data['image_url'], 
                    'strict_signup'     => $data['strict_signup'], 
                    'use_capabilities'  => $data['use_capabilities'] ), 
                array( 'course_id' => $course_id ), 
                array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d' ), 
                array( '%d' ) );
    }
    
    /**
     * Delete courses
     * @param int   $user_ID    The ID of the current user
     * @param array $checkbox   IDs of the courses
     * @since 5.0.0
     */
    public static function delete_courses($user_ID, $checkbox){
        global $wpdb;
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0");
        for( $i = 0; $i < count( $checkbox ); $i++ ) { 
            $checkbox[$i] = intval($checkbox[$i]);
            
            // capability check
            $capability = tc_Courses::get_capability($checkbox[$i], $user_ID);
            if ($capability !== 'owner' ) {
                continue;
            }
            
            $wpdb->query( "DELETE FROM " . TEACHCOURSES_COURSES . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHCOURSES_COURSE_META . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHCOURSES_COURSE_CAPABILITIES . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHCOURSES_COURSE_DOCUMENTS . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHCOURSES_ARTEFACTS . " WHERE `course_id` = $checkbox[$i]" );
            // Check if there are parent courses, which are not selected for erasing, and set there parent to default
            $sql = "SELECT `course_id` FROM " . TEACHCOURSES_COURSES . " WHERE `parent` = $checkbox[$i]";
            $test = $wpdb->query($sql);
            if ($test == '0') {
                continue;
            }
            $row = $wpdb->get_results($sql);
            foreach ($row as $row) {
                if ( !in_array($row->course_id, $checkbox) ) {
                    $wpdb->update( TEACHCOURSES_COURSES, array( 'parent' => 0 ), array( 'course_id' => $row->course_id ), array('%d' ), array( '%d' ) );
                }
            }
        }
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1");
    }
}
