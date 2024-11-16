<?php
/**
 * This file contains the database access class for courses
 * @package teachcourses
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Contains functions for getting, adding and deleting of courses
 * @package teachcourses
 * @subpackage database
 * @since 5.0.0
 */
class tc_Courses {

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
            'slug' => '',
            'term_id'       => '',
            'term'       => '',
            'visibility'    => '',
            'type'        => '',
            'search'        => '',
            'exclude'       => '',
            'order'         => 'sequence, name',
            'limit'         => '',
            'output_type'   => OBJECT
        ); 
        $atts = wp_parse_args( $args, $defaults );

        // var_dump($atts);
        // var_dump('<br><br>');

        global $wpdb;

        // Define basics
        $sql = "SELECT c.course_id AS course_id, c.slug AS slug, c.name AS name, c.type AS type, c.lecturer AS lecturer, c.assistant AS assistant, c.credits AS credits, c.hours AS hours, c.module AS module, c.language AS language, c.term_id AS term_id, c.visible AS visible, c.description AS description, t.name AS term, t.slug AS term_slug, t.sequence AS sequence FROM " . TEACHCOURSES_COURSES.  " c LEFT JOIN " . TEACHCOURSES_TERMS .  " t ON c.term_id = t.term_id "; //( SELECT t.course_id AS course_id, t.name AS name, t.type AS type, t.lecturer AS lecturer, t.date AS date, t.room As room, t.places AS places, t.start AS start, t.end As end, t.semester AS semester, t.parent As parent, t.visible AS visible, t.rel_page AS rel_page, t.comment AS comment, t.image_url AS image_url, t.strict_signup AS strict_signup, t.use_capabilities AS use_capabilities, p.name AS parent_name FROM " . TEACHCOURSES_COURSES . " t LEFT JOIN " . TEACHCOURSES_COURSES . " p ON t.parent = p.course_id ) AS temp";
        
        // define global search
        $search = esc_sql(htmlspecialchars(stripslashes($atts['search'])));
        if ( $search != '' ) {
            $search = "`name` like '%$search%' OR `lecturer` like '%$search%' OR `date` like '%$search%' OR `room` like '%$search%' OR `course_id` = '$search'";
        }
        
        // WHERE clause
        $nwhere = array();
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['term_id'], "t.term_id", "AND", "=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['type'], "c.type", "AND", "=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['visibility'], "c.visible", "OR", "=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['slug'], "c.slug", "AND", "=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['term'], "t.slug", "AND", "=");
        $nwhere[] = ( $search != '') ? $search : null;
        
        $where = tc_DB_Helpers::compose_clause($nwhere);
        
        // LIMIT clause
        $limit = ( $atts['limit'] != '' ) ? 'LIMIT ' . esc_sql($atts['limit']) : '';

        // define order
        $order = esc_sql($atts['order']);
        if ( $order != '' ) {
            $order = ' ORDER BY ' . $order;
        }
        
        // var_dump($sql . $where . $order . $limit . '<br><br>');
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
            $parent = TC_Courses::get_course_data($row->parent, 'name');
            $row->name = ( $row->name != $parent ) ? $parent . ' ' . $row->name : $row->name;
        }
        return $row->name;
    }
    
    /** 
     * Add a new course
     * @param array $data       An associative array with data of the course
     * @return int              ID of the new course
     * @since 5.0.0
    */
   public static function add_course($data) {
        global $wpdb;
        
        // prevent possible double escapes
        $data['name'] = stripslashes($data['name']);
        $data['slug'] = stripslashes($data['slug']);
        $data['type'] = stripslashes($data['type']);
        $data['term_id'] = stripslashes($data['term_id']);
        $data['lecturer'] = stripslashes($data['lecturer']);
        $data['assistant'] = stripslashes($data['assistant']);
        $data['credits'] = stripslashes($data['credits']);
        $data['hours'] = stripslashes($data['hours']);
        $data['module'] = stripslashes($data['module']);
        $data['language'] = stripslashes($data['language']);
        $data['links'] = stripslashes($data['links']);
        $data['description'] = stripslashes($data['description']);
        $data['visible'] = stripslashes($data['visible']);
        $data['image_url'] = stripslashes($data['image_url']);
    
        $wpdb->insert( 
                TEACHCOURSES_COURSES, 
                array( 
                    'name'              => $data['name'], 
                    'slug'              => $data['slug'], 
                    'type'              => $data['type'], 
                    'term_id'           => $data['term_id'], 
                    'lecturer'          => $data['lecturer'], 
                    'assistant'        => $data['assistant'],
                    'credits'           => $data['credits'],
                    'hours'             => $data['hours'],
                    'module'            => $data['module'],
                    'language'          => $data['language'],
                    'links'             => $data['links'],
                    'description'       => $data['description'],
                    'visible'           => $data['visible'],
                    'image_url'         => $data['image_url'],
                ),
                array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ) );
        $course_id = $wpdb->insert_id;

        return $course_id;
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
        
        // prevent possible double escapes
        $data['name'] = stripslashes($data['name']);
        $data['slug'] = stripslashes($data['slug']);
        $data['type'] = stripslashes($data['type']);
        // $data['term_id'] = stripslashes($data['term_id']);
        $data['lecturer'] = stripslashes($data['lecturer']);
        $data['assistant'] = stripslashes($data['assistant']);
        $data['credits'] = stripslashes($data['credits']);
        $data['hours'] = stripslashes($data['hours']);
        $data['module'] = stripslashes($data['module']);
        $data['language'] = stripslashes($data['language']);
        $data['links'] = stripslashes($data['links']);
        $data['description'] = stripslashes($data['description']);
        $data['visible'] = stripslashes($data['visible']);

        return $wpdb->update( 
                TEACHCOURSES_COURSES, 
                array(
                    'name'              => $data['name'],
                    'slug'              => $data['slug'],
                    'type'              => $data['type'],
                    'term_id'           => $data['term_id'],  
                    'lecturer'          => $data['lecturer'],
                    'assistant'        => $data['assistant'],
                    'credits'           => $data['credits'],
                    'hours'             => $data['hours'],
                    'module'            => $data['module'],
                    'language'          => $data['language'],
                    'links'             => $data['links'],
                    'description'       => $data['description'],
                    'visible'           => $data['visible'],
                    'image_url'         => $data['image_url'],
                ), 
                array( 'course_id' => $course_id ), 
                array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ), 
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
            
            $wpdb->query( "DELETE FROM " . TEACHCOURSES_COURSES . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHCOURSES_COURSE_DOCUMENTS . " WHERE `course_id` = $checkbox[$i]" );
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
