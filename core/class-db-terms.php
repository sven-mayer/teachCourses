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
 * @since 0.0.1
 */
class tc_Terms {

    /**
     * Returns all data of a single course
     * @param int $term_id            The course ID
     * @param string $output_type       OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * @return mixed
     * @since 0.0.1
     */
    public static function get_term ($term_id, $output_type = OBJECT) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM `" . TEACHCOURSES_TERMS . "` WHERE `term_id` = '" . intval($term_id) . "'", $output_type);
        return $result;
    }
    
    /**
     * Returns all data of one or more courses
     * 
     * possible values for the array $args:
     *      @type string semester         The semester/term of the courses
     *      @type string visibility       The visibility of the coures (1,2,3) separated by comma
     *      @type string parent           The term_id of the parent
     *      @type string search           A general search string
     *      @type string exclude          The course IDs you want to exclude
     *      @type string order            Default: semester DESC, name
     *      @type string limit            The sql search limit, ie: 0,30
     *      @type string output_type      OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * 
     * @param array $args
     * @return object|array
     * @since 0.0.1
     */
    public static function get_terms ( $args = array() ) {
        $defaults = array(
            'visibility'    => '',
            'slug'          => '',
            'exclude'       => '',
            'order'         => 'sequence DESC, name',
            'limit'         => '',
            'output_type'   => OBJECT
        ); 
        $atts = wp_parse_args( $args, $defaults );

        global $wpdb;

        // Define basics
        $sql = "SELECT term_id, name, slug, sequence, visible FROM " . TEACHCOURSES_TERMS;
        
        // WHERE clause
        $nwhere = array();
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['exclude'], "p.pub_id", "AND", "!=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['visibility'], "visible", "OR", "=");
        $nwhere[] = tc_DB_Helpers::generate_where_clause($atts['slug'], "slug", "AND", "=");
        
        $where = tc_DB_Helpers::compose_clause($nwhere);
        
        // LIMIT clause
        $limit = ( $atts['limit'] != '' ) ? 'LIMIT ' . esc_sql($atts['limit']) : '';

        // define order
        $order = esc_sql($atts['order']);
        if ( $order != '' ) {
            $order = " ORDER BY $order";
        }

        // var_dump($sql . $where . $order . $limit)  ;

        $result = $wpdb->get_results($sql . $where . $order . $limit, $atts['output_type']);
        return $result;
    }
    
    /**
     * Returns the course name under consideration of a possible parent course
     * @param int $term_id    The course ID
     * @return string
     * @since 5.0.6
     */
    public static function get_course_name ($term_id) {
        global $wpdb;
        $row = $wpdb->get_row("SELECT `name`, `parent` FROM " . TEACHCOURSES_TERMS . " WHERE `term_id` = '" . intval($term_id) . "'");
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
     * @since 0.0.1
    */
   public static function add_term($data) {
        global $wpdb;
        
        // prevent possible double escapes
        $data['name'] = stripslashes($data['name']);
        $data['slug'] = stripslashes($data['slug']);
        $data['sequence'] = stripslashes($data['sequence']);
    
        $wpdb->insert( 
                TEACHCOURSES_TERMS, 
                array(
                    'name'              => $data['name'],
                    'slug'              => $data['slug'],
                    'sequence'              => $data['sequence']), 
                array( '%s', '%s', '%s', '%s') );
        $term_id = $wpdb->insert_id;

        return $term_id;
    }
    
    /** 
     * Changes course data. Returns false if errors, or the number of rows affected if successful.
     * @param int $term_id    course ID
     * @param array $data       An associative array of couse data (name, places, type, room, ...)
     * @return int|false
     * @since 0.0.1
    */ 
   public static function change_term ($term_id, $data){
        global $wpdb;
        $term_id = intval($term_id);
        global $current_user;
        $old_places = TC_Courses::get_course_data ($term_id, 'places');
        
        // prevent possible double escapes
        $data['name'] = stripslashes($data['name']);
        $data['slug'] = stripslashes($data['slug']);
        $data['sequence'] = stripslashes($data['sequence']);

        return $wpdb->update( 
                TEACHCOURSES_TERMS, 
                array( 
                    'name'              => $data['name'], 
                    'slug'              => $data['slug'],
                    'sequence'          => $data['sequence'],
                    'visible'           => $data['visible'], 
                    ), 
                array( 'term_id' => $term_id ), 
                array( '%s', '%s', '%s'), 
                array( '%d' ) );
    }
    
    /**
     * Delete term
     * @param int   $user_ID    The ID of the current user
     * @param array $checkbox   IDs of the term
     * @since 0.0.1
     */
    public static function delete_term($user_ID, $checkbox){
        global $wpdb;
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0");
        for( $i = 0; $i < count( $checkbox ); $i++ ) { 
            $checkbox[$i] = intval($checkbox[$i]);
            
            $wpdb->query( "DELETE FROM " . TEACHCOURSES_TERMS . " WHERE `term_id` = $checkbox[$i]" );
        }
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1");
    }
}
