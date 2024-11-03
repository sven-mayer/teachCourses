<?php
/**
 * This file contains all deprecated functions
 * 
 * @package teachcorses\core\deprecated
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/** 
 * Displays information about a single course and his childs
 * @param array $attr
 * @return string
 * @since 0.20
 * @deprecated since version 5.0.0
 * @todo Delete function
*/
function tc_date_shortcode($attr) {
    trigger_error( __('The shortcode [tpdate] is deprecated since teachcorses 5.0.0. Use [tpcourseinfo] instead.','teachcorses') );
    return tc_courseinfo_shortcode($attr);
}

/** 
 * teachCorses Admin Page Menu
 * @param int $number_entries       Number of all available entries
 * @param int $entries_per_page     Number of entries per page
 * @param int $current_page         current displayed page
 * @param int $entry_limit          SQL entry limit
 * @param string $page_link         the name of the page you will insert the menu
 * @param string $link_attributes   the url attributes for get parameters
 * @param string $type              top or bottom, default: top
 * @return string
 * @deprecated since version 5.0.0
 * @todo Delete function
*/
function tc_admin_page_menu ($number_entries, $entries_per_page, $current_page, $entry_limit, $page_link = '', $link_attributes = '', $type = 'top') {
    trigger_error( __('The function tc_admin_page_menu() is deprecated since teachcorses 5.0.0. Use tc_page_menu() instead.','teachcorses') );
    return tc_page_menu(array('number_entries' => $number_entries,
                              'entries_per_page' => $entries_per_page,
                              'current_page' => $current_page,
                              'entry_limit' => $entry_limit,
                              'page_link' => $page_link,
                              'link_attributes' => $link_attributes,
                              'mode' => $type));
}

/**
 * This function is deprecated. Please use tc_courses::get_course() instead.
 * @param int $id
 * @param string $output_type
 * @return mixed
 * @since 3.1.7
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tc_course ( $id, $output_type = OBJECT) {
    trigger_error( __('get_tc_course() is deprecated since teachcorses 5.0.0. Use tc_courses::get_course() instead.','teachcorses') );
    return tc_Courses::get_course($id, $output_type);
}

/**
 * This function is deprecated. Please use tc_courses::get_courses() instead.
 * @param array $args
 * @return mixed
 * @since 4.0.0
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tc_courses ( $args = array() ) {
    trigger_error( __('get_tc_courses() is deprecated since teachcorses 5.0.0. Use tc_courses::get_courses() instead.','teachcorses') );
    return tc_Courses::get_courses($args);
}

/**
 * This function is deprecated. Please use tc_courses::get_free_places() instead.
 * @param int $course_id    --> ID of the course
 * @param int $places       --> Number of places
 * @return int
 * @since 3.1.7
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tc_course_free_places($course_id, $places) {
    trigger_error( __('get_tc_course_free_places() is deprecated since teachcorses 5.0.0. tc_courses::get_free_places() instead.','teachcorses') );
    return tc_Courses::get_free_places($course_id, $places);
}

/**
 * This function is deprecated. Please use tc_tags::get_tags() instead.
 * @param array $args
 * @return array|object
 * @since 4.0.0
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tc_tags( $args = array() ) {
    trigger_error( __('get_tc_tags() is deprecated since teachcorses 5.0.0. Use tc_tags::get_tags() instead.','teachcorses') );
    return tc_Tags::get_tags($args);
}

/**
 * This function is deprecated. Please use tc_tags::get_tag_cloud() instead.
 * @param array $args
 * @since 4.0.0
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tc_tag_cloud ( $args = array() ) {
    trigger_error( __('get_tc_tag_cloud() is deprecated since teachcorses 5.0.0. Use tc_tags::get_tag_cloud() instead.','teachcorses') );
    return tc_Tags::get_tag_cloud($args);
}

/**
 * This function is deprecated. Please use tc_publications::get_publication() instead.
 * @param int $id
 * @param string $output_type
 * @since 3.1.7
 * @return mixed
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tc_publication ($id, $output_type = OBJECT) {
    trigger_error( __('get_tc_publication() is deprecated since teachcorses 5.0.0. Use tc_publications::get_publication() instead.','teachcorses') );
    return tc_Publications::get_publication($id, $output_type);
}

/**
 * This function is deprecated. Please use tc_publications::get_publications() instead.
 * @param array $args
 * @param boolean $count    set to true of you only need the number of rows
 * @return array|object|int
 * @since 3.1.8
 * @deprecated since version 5.0.0
 * @todo Delete function
*/
function get_tc_publications($args = array(), $count = false) {
    trigger_error( __('get_tc_publications() is deprecated since teachcorses 5.0.0. Use tc_publications::get_publications() instead.','teachcorses') );
    return tc_Publications::get_publications($args, $count);
}

/**
 * This function is deprecated. Please use tc_is_student_subscribed() instead.
 * @param integer $course_id
 * @param boolean $consider_childcourses
 * @return boolean
 * @since 3.1.7
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function tc_is_user_subscribed ($course_id, $consider_childcourses = false) {
    trigger_error( __('tc_is_user_subscribed() is deprecated since teachcorses 5.0.0. Use tc_courses::is_student_subscribed() instead.','teachcorses') );
    return tc_Courses::is_student_subscribed($course_id, $consider_childcourses);
}

/**
 * This function is deprecated. Please use tc_bookmarks::bookmark_exists() instead.
 * Check if an user has bookmarked a publication
 * @param int $pub_id
 * @param int $user_id
 * @return boolean
 * @since 4.0.0
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function tc_check_bookmark ($pub_id, $user_id) {
    trigger_error( __('tc_check_bookmark() is deprecated since teachcorses 5.0.0. Use tc_bookmarks::bookmark_exists() instead.','teachcorses') );
    return tc_Bookmarks::bookmark_exists($pub_id, $user_id);
}