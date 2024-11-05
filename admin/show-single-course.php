<?php 
/**
 * This file contains all functions for displaying the show_single_course page in admin menu
 * 
 * @package teachcorses
 * @subpackage admin
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/** 
 * Single course overview
 * 
 * $_GET parameters:
 * @param int $course_id    course ID
 * @param string $sem       semester, from show_courses.php
 * @param string $search    search string, from show_courses.php
*/
function tc_show_single_course_page() {
    
    $current_user = wp_get_current_user();

    // form
    $checkbox = ( isset( $_POST['checkbox'] ) ) ?  $_POST['checkbox'] : '';
    $waiting = isset( $_POST['waiting'] ) ?  $_POST['waiting'] : '';
    $reg_action = isset( $_POST['reg_action'] ) ?  $_POST['reg_action'] : '';
    $course_id = intval($_GET['course_id']);
    
    $link_parameter['sem'] = htmlspecialchars($_GET['sem']);
    $link_parameter['redirect'] = isset( $_GET['redirect'] ) ?  intval($_GET['redirect']) : 0;
    $link_parameter['sort'] = isset ( $_GET['sort'] ) ? $_GET['sort'] : 'asc';
    $link_parameter['search'] = htmlspecialchars($_GET['search']);
    $link_parameter['order'] = isset ( $_GET['order'] ) ? $_GET['order'] : 'name';
    $action = isset( $_GET['action'] ) ?  $_GET['action'] : 'show';
    
    // Get screen options
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $link_parameter['per_page'] = get_user_meta($current_user->ID, $screen_option, true);
    if ( empty ( $link_parameter['per_page'] ) || $link_parameter['per_page'] < 1 ) {
        $link_parameter['per_page'] = $screen->get_option( 'per_page', 'default' );
    }
    
    // Handle limits
    if ( isset($_GET['limit']) ) {
        $link_parameter['curr_page'] = intval($_GET['limit']);
        if ( $link_parameter['curr_page'] <= 0 ) {
            $link_parameter['curr_page'] = 1;
        }
        $link_parameter['entry_limit'] = ( $link_parameter['curr_page'] - 1 ) * $link_parameter['per_page'];
    }
    else {
        $link_parameter['entry_limit'] = 0;
        $link_parameter['curr_page'] = 1;
    }
    
    // course data
    $course_data = tc_Courses::get_course($course_id, ARRAY_A);
    $parent = tc_Courses::get_course($course_data["parent"], ARRAY_A);
    $capability = tc_Courses::get_capability($course_id, $current_user->ID);

    echo '<div class="wrap">';
    tc_Single_Course_Actions::do_actions($course_id, $_POST, $current_user, $waiting, $checkbox, $reg_action, $capability);
    
    echo '<form id="einzel" name="einzel" action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
    echo '<input name="page" type="hidden" value="teachcorses/teachcorses.php">';
    echo '<input name="action" type="hidden" value="' . $action . '" />';
    echo '<input name="course_id" type="hidden" value="' . $course_id . '" />';
    echo '<input name="sem" type="hidden" value="' . $link_parameter['sem'] . '" />';
    echo '<input name="search" type="hidden" value="' . $link_parameter['search'] . '" />';
    echo '<input name="redirect" type="hidden" value="' . $link_parameter['redirect'] . '" />';
    echo '<input name="sort" type="hidden" value="' . $link_parameter['sort'] . '" />';
    echo '<input name="order" type="hidden" value="' . $link_parameter['order'] . '" />';
    
    echo tc_Single_Course_Page::get_back_button($link_parameter);
    echo tc_Single_Course_Page::get_course_headline($course_id, $course_data, $parent, $link_parameter, true);
    echo tc_Single_Course_Page::get_menu($course_id, $link_parameter, $action, $capability);
    
    echo '<div style="width:100%; float:left; margin-top: 12px;">';
    
    // Show tab content
    if ( $action === 'capabilities' && $capability === 'owner' ) {
        tc_Single_Course_Page::get_capability_tab($course_data);
    }
    else if ( $action === 'documents' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tc_Single_Course_Page::get_documents_tab($course_id);
    }
    else {
        tc_Single_Course_Page::get_info_tab($course_id, $course_data);
    }
    
    echo '</form>';
    echo '</div>';
    echo '</div>';
    
}

/**
 * This class contains all functions for single course actions like add an artefact, add a capability...
 * @package teachcorses
 * @subpackage courses
 * @since 5.0.0
 */
class tc_Single_Course_Actions {
    
    /**
     * Adds an artefact
     * @param int $course_id
     * @param array $post
     * @since 5.0.0
     * @access private
     */
    private static function add_artefact($course_id, $post) {
        $data = array('parent_id' => intval($post['artefact_parent']), 
                      'course_id' => $course_id, 
                      'title' => htmlspecialchars($post['artefact_name']), 
                      'scale' => '', 
                      'passed' => '', 
                      'max_value' => '');
        tc_Artefacts::add_artefact($data);
        get_tc_message( __('Artefact added','teachcorses') );
    }
    
    /**
     * Adds an assessment
     * @param int $course_id        The course ID
     * @param array $post           The $_POST array
     * @since 5.0.0
     * @access private
     */
    private static function add_assessment($course_id, $post) {
        $assessment_target = intval($post['assessment_target']);
        $assessment_passed = ( isset($post['assessment_passed']) ) ? 1 : 0;
        $artefact_id = ( $assessment_target !== 0 ) ? intval($post['assessment_target']) : NULL;
        $course = ( $assessment_target === 0 ) ? $course_id : NULL;
        $data = array('artefact_id' => $artefact_id, 
                      'course_id' => $course, 
                      'wp_id' => intval($post['assessment_participant']), 
                      'value' => htmlspecialchars($_POST['assessment_value']), 
                      'max_value' => '',
                      'type' => htmlspecialchars($_POST['assessment_value_type']),
                      'examiner_id' => get_current_user_id(),
                      'exam_date' => date('Y-m-d H:i:s'), 
                      'comment' => htmlspecialchars($_POST['assessment_comment']), 
                      'passed' =>  $assessment_passed );
        tc_Assessments::add_assessment($data);
        get_tc_message( __('Assessment added','teachcorses') );
    }
    
    /**
     * Adds a capability
     * @param int $course_id        The course ID
     * @param int $user_id          The user ID
     * @param array $post           The $_POST array
     * @since 5.0.0
     * @access private
     */
    private static function add_capability($course_id, $user_id, $post) {
        $cap_user = $post['cap_user'];
        if ( tc_Courses::has_capability($course_id, $user_id, 'owner') ) {
            $ret = tc_Courses::add_capability($course_id, $cap_user, 'approved');
            if ( $ret !== false ) {
                get_tc_message( __('Capability added','teachcorses') );
            }
        }
        else {
            get_tc_message( __('Access denied','teachcorses'), 'red' );
        }
    }
    
    /**
     * Deletes an artefact
     * @param int $artefact_id
     * @since 5.0.0
     * @access private
     */
    private static function delete_artefact($artefact_id) {
        if ( tc_Artefacts::has_assessments($artefact_id) === true ) {
            get_tc_message( __('Removing not possible. Delete the assessments first.','teachcorses'), 'red' );
            return;
        }
        tc_Artefacts::delete_artefact($artefact_id);
        get_tc_message( __('Removing successful','teachcorses') );
    }
    
    /**
     * Deletes an assessment
     * @param array $post
     * @since 5.0.0
     * @access private
     */
    private static function delete_assessment($post) {
        $assessment_id = isset ( $post['tc_assessment_id'] ) ? intval($post['tc_assessment_id']) : 0;
        tc_Assessments::delete_assessment($assessment_id);
        get_tc_message( __('Removing successful','teachcorses') );
    }
    
    /**
     * Deletes signups
     * @param array $post
     * @param array $checkbox
     * @param array $waiting
     * @since 5.0.0
     * @access private
     */
    private static function delete_signup($post, $checkbox, $waiting) {
        $move_up = isset( $post['move_up'] ) ? true : false;
        tc_Courses::delete_signup($checkbox, $move_up);
        tc_Courses::delete_signup($waiting, $move_up);
        get_tc_message( __('Removing successful','teachcorses') );	
    }
    
    /**
     * Changes an artefact
     * @param array $post
     * @since 5.0.0
     * @access private
     */
    private static function change_artefact($post) {
        $artefact_id = isset( $post['tc_artefact_id'] ) ? intval($post['tc_artefact_id']) : 0;
        $artefact_title = isset( $post['tc_artefact_title'] ) ? htmlspecialchars($post['tc_artefact_title']) : '';
        if ( $artefact_id === 0 ) {
            return;
        }
        tc_Artefacts::change_artefact_title($artefact_id, $artefact_title);
        get_tc_message( __('Saved') );
    }
    
    /**
     * Changes an assessment
     * @param array $post
     * @since 5.0.0
     * @access private
     */
    private static function change_assessment($post) {
        $assessment_id = isset ( $post['tc_assessment_id'] ) ? intval($post['tc_assessment_id']) : 0;
        $data = array('value' => isset ( $post['tc_value'] ) ? htmlspecialchars($post['tc_value']) : '', 
                      'type' => isset ( $post['tc_type'] ) ? htmlspecialchars($post['tc_type']) : '',
                      'examiner_id' => get_current_user_id(),
                      'exam_date' => date('Y-m-d H:i:s'), 
                      'comment' => isset ( $post['tc_comment'] ) ? htmlspecialchars($post['tc_comment']) : '', 
                      'passed' =>  isset ( $post['tc_passed'] ) ? htmlspecialchars($post['tc_passed']) : '' );
        if ( $assessment_id === 0 ) {
            return;
        }
        tc_Assessments::change_assessment($assessment_id, $data);
        get_tc_message( __('Saved') );
    }
    
    /**
     * Moves a signup
     * @param array $post
     * @param array $checkbox
     * @param array $waiting
     * @since 5.0.0
     * @access private
     */
    private static function move_signup($post, $checkbox, $waiting) {
        tc_Courses::move_signup($checkbox, intval($post['tc_rel_course']) );
        tc_Courses::move_signup($waiting, intval($post['tc_rel_course']) );
        get_tc_message( __('Participant moved','teachcorses') );
    }

    /**
     * Handles all database actions for the single course page
     * @param int $course_id
     * @param array $post
     * @param array $current_user
     * @param array $waiting
     * @param array $checkbox
     * @param string $reg_action
     * @param string $capability
     */
    public static function do_actions($course_id, $post, $current_user, $waiting, $checkbox, $reg_action, $capability) {
        // change signup
        if ( $reg_action == 'signup' && ( $capability === 'owner' || $capability === 'approved' ) ) {
            tc_Courses::change_signup_status($waiting, 'course');
            get_tc_message( __('Participant added','teachcorses') );
        }
        if ( $reg_action == 'signout' && ( $capability === 'owner' || $capability === 'approved' ) ) {
            tc_Courses::change_signup_status($checkbox, 'waitinglist');
            get_tc_message( __('Participant moved','teachcorses') );
        }
        // add signup
        if ( isset( $post['add_signup'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            if ( isset( $post['tc_add_reg_student'] ) ) {
                $students = $post['tc_add_reg_student'];
                foreach ($students as $row) {
                    tc_Courses::add_signup($row, $course_id);
                }
                get_tc_message( __('Participant added','teachcorses') );
            }
        }
        // move signup
        if ( isset( $post['move_ok'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::move_signup($post, $checkbox, $waiting);
        }
        // Delete functions
        if ( isset( $post['delete_ok'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::delete_signup($post, $checkbox, $waiting);
        }
        // Add artefact
        if ( isset( $post['add_artefact'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::add_artefact($course_id, $post);
        }
        // Edit artefact
        if ( isset( $post['tc_save_artefact'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::change_artefact($post);
        }
        // Delete artefact
        if ( isset( $_GET['delete_artefact'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::delete_artefact($_GET['delete_artefact']);
            
        }
        // Add assessment
        if ( isset( $post['add_assessment'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::add_assessment($course_id, $post);
        }
        // Edit assessment
        if ( isset( $post['tc_save_assessment'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::change_assessment($post);
        }
        // Delete assessment
        if ( isset( $post['tc_delete_assessment'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::delete_assessment($post);
        }
        // Add capability
        if ( isset( $post['cap_submit'] ) ) {
            self::add_capability($course_id, $current_user->ID, $post);
        }
    }
}

/**
 * This class contains function for generating the single_course admin pages
 * @since 5.0.0
 */
class tc_Single_Course_Page {
    
    /**
     * Shows the add_artefact_form for show_single_course page
     * @since 5.0.0
     */
    public static function get_artefact_form() {
        echo '<div id="tc_add_artefact_form" class="teachcorses_message" style="display:none;">';
        echo '<p class="teachcorses_message_headline">' . __('Add artefact','teachcorses') . '</p>';

        echo '<p><label for="artefact_name">' . __('Title','teachcorses') . '</label></p>';
        echo '<input name="artefact_name" id="artefact_name" type="text" style="width:50%;"/>';
        
        echo '<input name="artefact_parent" type="hidden" value="0"/>';

        echo '<p><input name="add_artefact" type="submit" class="button-primary" value="' . __('Add','teachcorses') . '"/> <a onclick="teachcorses_showhide(' . "'tc_add_artefact_form'" . ');" class="button-secondary" style="cursor:pointer;">' . __('Cancel', 'teachcorses') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Return the back_to button
     * @param array $link_parameter
     * @return string
     * @since 5.0.0
     */
    public static function get_back_button ($link_parameter){
        $save = isset( $_POST['save'] ) ?  $_POST['save'] : '';
        if ( $save == __('Save') ) {
            return;
        }
        if ( $link_parameter['redirect'] != 0 ) {
            return '<p><a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $link_parameter['redirect'] . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show" class="button-secondary" title="' . __('Back','teachcorses') . '">&larr; ' . __('Back','teachcorses') . '</a></p>';
        }
        else {
             return '<p><a href="admin.php?page=teachcorses/teachcorses.php&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '" class="button-secondary" title="' . __('Back','teachcorses') . '">&larr; ' . __('Back','teachcorses') . '</a></p>';
        }
        
    }

    /**
     * Returns the page headline
     * @param int $course_id
     * @param array $course_data
     * @param array $parent_data
     * @param array $link_parameter
     * @param string $edit_link
     * @return string
     * @since 5.0.0
     */
    public static function get_course_headline($course_id, $course_data, $parent_data, $link_parameter, $edit_link = true) {
        $link = '';
        $parent_name = '';
        
        if ($course_data["parent"] != 0) {
            if ($parent_data["course_id"] == $course_data["parent"]) {
                $parent_name = '<a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $parent_data["course_id"] . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show&amp;redirect=' . $course_id . '" title="' . stripslashes($parent_data["name"]) . '" style="color:#464646">' . stripslashes($parent_data["name"]) . '</a> &rarr; ';
            }
        }
        
        if ( $edit_link === true ) {
            $link = '<small><a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=edit" class="teachcorses_link" style="cursor:pointer;">' . __('Edit','teachcorses') . '</a></small>';
        }

        return '<h1 style="padding-top:5px;">' . $parent_name . stripslashes($course_data["name"]) . ' ' . $course_data["semester"] . ' <span class="tc_break">|</span> ' . $link . '</h1>';
    }
    
    /**
     * Returns the page menu
     * @param int $course_id
     * @param array $link_parameter
     * @param string $action
     * @param strin $capability
     * @return string
     * @since 5.0.0
     */
    public static function get_menu($course_id, $link_parameter, $action, $capability){
        $enrollments_tab = '';
        $assessment_tab = '';
        $capability_tab = '';
        $documents_tab = '';
        
        $set_info_tab = ( $action === 'show' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $info_tab = '<a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show" class="' . $set_info_tab . '">' . __('Info','teachcorses') . '</a> ';
        
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $set_documents_tab = ( $action === 'documents' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $documents_tab = '<a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=documents" class="' . $set_documents_tab . '">' . __('Documents','teachcorses') . '</a> ';
        }
        
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $set_enrollments_tab = ( $action === 'enrollments' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $enrollments_tab = '<a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=enrollments" class="' . $set_enrollments_tab . '">' . __('Enrollments','teachcorses') . '</a> ';
        }
        
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $set_assessment_tab = ( $action === 'assessments' || $action === 'add_assessments' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $assessment_tab = '<a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=assessments" class="' . $set_assessment_tab . '">' . __('Assessments','teachcorses') . '</a> ';
        }
        
        if ( $capability === 'owner' ) {
            $set_capability_tab = ( $action === 'capabilities' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $capability_tab = '<a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=capabilities" class="' . $set_capability_tab . '">' . __('Capabilities','teachcorses') . '</a> ';
        }
        
        return '<h3 class="nav-tab-wrapper">' . $info_tab . $documents_tab . $enrollments_tab. $assessment_tab . $capability_tab . '</h3>';
    }
    
    /**
     * Gets the add_students_form for the enrollments tab
     * @since 5.0.0
     * @access private
     */
    private static function get_add_students_form() {
        echo '<div class="teachcorses_message" id="tc_add_signup_form" style="display: none;">';
        echo '<p class="teachcorses_message_headline">' . __('Add students manually','teachcorses') . '</p>';
        echo '<select name="tc_add_reg_student[]" id="tc_add_reg_student" size="10" multiple>';
        $row1 = tc_Students::get_students();
        $zahl = 0;
        $notice = array();
        foreach($row1 as $row1) {
            if ($zahl != 0 && $notice[0] != $row1->lastname[0]) {
                echo '<option>----------</option>';
            }
            echo '<option value="' . $row1->wp_id . '">' . stripslashes($row1->lastname) . ', ' . stripslashes($row1->firstname) . ' (' . $row1->userlogin . ')</option>';
            $notice = $row1->lastname;
            $zahl++;
        }
        echo '</select>';
        echo '<p><i>' . __('Use &lt;Ctrl&gt; key to select more than one student','teachcorses') . '</i></p>';
        echo '<p>
               <input type="submit" name="add_signup" class="button-primary" value="' . __('Add', 'teachcorses') . '" />
               <a onclick="teachcorses_showhide(' . "'" . 'tc_add_signup_form' . "'" . ');" class="button-secondary" style="cursor:pointer;">' . __('Cancel', 'teachcorses') . '</a>
             </p>';
        echo '</div>';   
    }
    
    /**
     * Gets the move_to_a_course_form for the enrollments tab
     * @param int $course_id            The ID of the course
     * @param array $cours_data         An associative array of the course_data
     * @param array $link_parameter     An associative array of link parameters
     * @since 5.0.0
     * @access private
     */
    private static function get_move_to_a_course_form($course_id, $cours_data, $link_parameter) {
        $p = $cours_data['parent'] != 0 ? $cours_data['parent'] : $cours_data['course_id'];
        $related_courses = tc_Courses::get_courses( array('parent' => $p ) );
        if ( count($related_courses) === 0 ) {
            get_tc_message(__('Error: There are no related courses.','teachcorses'));
            return;
        }
        echo '<div class="teachcorses_message" id="tc_move_to_course">';
        echo '<p class="teachcorses_message_headline">' . __('Move to a related course','teachcorses') . '</p>';
        echo '<p>' . __('If you move a signup to an other course the signup status will be not changed. So a waitinglist will be a waitinglist entry.','teachcorses') . '</p>';
        echo '<select name="tc_rel_course" id="tc_rel_course">';
        foreach ( $related_courses as $rel ) {
            $selected = $rel->course_id == $cours_data['course_id'] ? ' selected="selected"' : '';
            echo '<option value="' . $rel->course_id . '"' . $selected . '>' . $rel->course_id . ' - ' . $rel->name . '</option>';
        }
        echo ' </select>';
        echo '<p><input name="move_ok" type="submit" class="button-primary" value="' . __('Move','teachcorses') . '"/>
                    <a href="admin.php?page=teachcorses/teachcorses.php&course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;order=' . $link_parameter['order'] . '&amp;sort=' . $link_parameter['sort'] . '&amp;action=enrollments" class="button-secondary">' . __('Cancel','teachcorses') . '</a></p>';    
        echo '</div>';
    }
    
    
    /**
     * Shows the capabilities tab for show_single_course page
     * @param array $course_data
     * @since 5.0.0
     */
    public static function get_capability_tab ($course_data) {
        if ( $course_data['use_capabilities'] != 1 ) {
            get_tc_message( __("You can't set user capabilities here, because you are using global capabilities for this course.",'teachcorses'), 'orange' );
            return;
        }
        echo '<div class="tc_actions"><a id="teachcorses_add_capability" class="button-secondary" onclick="javascript:teachcorses_showhide(' . "'add_capability'" .');"><i class="fas fa-user-plus"></i> ' . __('Add new','teachcorses') . '</a></div>';
        echo '<div id="add_capability" class="teachcorses_message" style="display:none;">';
        echo '<form name="add_cap" method=""post>';
        echo '<p class="teachcorses_message_headline">' . __('Add capability for user','teachcorses') . '</p>';
        echo '<label for="cap_user">' . __('Username', 'teachcorses') . '</label> ';
        echo '<select id="cap_user" name="cap_user">';
        echo '<option>- ' . __('Select user','teachcorses') . ' -</option>';
        $capabilities = tc_Courses::get_capabilities($course_data['course_id']);
        $users = get_users();
        $array_caps = array();
        foreach ($capabilities as $row) {
            array_push($array_caps, $row['wp_id']);
        }
        foreach ($users as $user) {
            if (!in_array($user->ID, $array_caps) && user_can( $user->ID, 'use_teachcorses_courses' )  ) {
                echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
            }
        }
        echo '</select>';
        echo '<p><input name="cap_submit" type="submit" class="button-primary" value="' . __('Add','teachcorses') . '" /> <a class="button-secondary" onclick="javascript:teachcorses_showhide(' . "'add_capability'" .');">' . __('Cancel','teachcorses') . '</a></p>';
        echo '</form>';
        echo '</div>';
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="check-column"></th>';
        echo '<th>' . __('Username') . '</th>';
        echo '<th>' . __('Name','teachcorses') . '</th>';
        echo '<th>' . __('Capability','teachcorses') . '</th>';
        echo '</thead>';
        echo '</tr>';
        echo '<tbody>';
        $class_alternate = true;
        foreach ( $capabilities as $row ) {
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            $user = get_userdata( $row['wp_id'] );
            echo '<tr ' . $tr_class . '>';
            echo '<th class="check-column"></th>';
            echo '<td>';
            echo '<span style="float:left; margin-right:10px;">' . get_avatar($row['wp_id'], 35) . '</span> <strong>' . $user->user_login . '</strong>';
            if ( $row['capability'] !== 'owner' ) {
                echo '<div class="tc_row_actions"><a class="tc_row_delete" href="admin.php?page=teachcorses/teachcorses.php&course_id=6&sem=Example%20term&search=&action=capabilities" style="color:#a00;" title="' . __('Delete','teachcorses') . '">' . __('Delete','teachcorses') . '</a></div>';
            }
            echo '</td>';
            echo '<td>' . $user->display_name . '</td>';
            echo '<td>' . $row['capability'] . '</td>';
            echo '<tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Shows the info tab for show_single_course page
     * @param int $course_id    The ID of the course
     * @param array $cours_data An associative array with course data
     * @since 5.0.0
     */
    public static function get_info_tab ($course_id, $cours_data) {
        $fields = get_tc_options('teachcorses_courses','`setting_id` ASC', ARRAY_A);
        $course_meta = tc_Courses::get_course_meta($course_id);
        ?>
        <div style="width:24%; float:right; padding-left:1%; padding-bottom:1%;">
         <div class="postbox">
             <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('Enrollments','teachcorses'); ?></span></h3>
             <div class="inside">
                  <table cellpadding="8">
                    <?php 
                    if ($cours_data["start"] != '0000-00-00 00:00:00' && $cours_data["end"] != '0000-00-00 00:00:00') {
                        echo '<tr>';
                        echo '<td colspan="2"><strong>' . __('Start','teachcorses') . '</strong></td>';
                        echo '<td colspan="2">' . substr($cours_data["start"], 0, strlen( $cours_data["start"] ) - 3 ) . '</td>';
                        echo '</tr> ';
                        
                        echo '<tr>';
                        echo ' <td colspan="2"><strong>' . __('End','teachcorses') . '</strong></td>';
                        echo '<td colspan="2">' . substr($cours_data["end"], 0, strlen( $cours_data["end"] ) - 3 ) . '</td>';
                        echo '</tr>';
                        
                        $style = ( $free_places < 0 ) ? ' style="color:#ff6600; font-weight:bold;"' : '';
                        echo '<tr>';
                        echo '<td><strong>' . __('Places','teachcorses') . '</strong></th>';
                        echo '<td>' . $cours_data["places"] . '</td>';
                        echo '<td><strong>' . __('free places','teachcorses') . '</strong></td>';
                        echo '<td ' . $style . '></td>';
                        echo '</tr>';
                    } else {
                        echo '<tr>';
                        echo '<td colspan="4">' . __('none','teachcorses') . '</td>';
                        echo '</tr>';
                    } ?>  
                  </table>
             </div>
         </div>
       </div>
       <div style="width:75%; float:left; padding-bottom:10px;">
           <div class="postbox">
               <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('General','teachcorses'); ?></span></h3>
               <div class="inside">
                    <table cellpadding="8">
                      <tr>
                        <td width="230"><strong><?php _e('ID'); ?></strong></td>
                        <td><?php echo $cours_data["course_id"]; ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Type'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["type"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Visibility','teachcorses'); ?></strong></td>
                        <td>
                         <?php 
                            if ( $cours_data["visible"] == 1 ) {
                                 _e('normal','teachcorses');
                            }
                            elseif ( $cours_data["visible"] == 2 ) {
                                 _e('extend','teachcorses');
                            }
                            else {
                                 _e('invisible','teachcorses');
                            } 
                         ?></td> 
                      </tr>
                      <tr>
                        <td><strong><?php _e('Date','teachcorses'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["date"]); ?></td>
                      </tr>
                      <tr>
                          <td><strong><?php _e('Room','teachcorses'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["room"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Lecturer','teachcorses'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["lecturer"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Comment','teachcorses'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["comment"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Related content','teachcorses'); ?></strong></td>
                        <td><?php 
                            if ( $cours_data["rel_page"] != 0) {
                                echo '<a href="' . get_permalink( $cours_data["rel_page"] ) . '" target="_blank" class="teachcorses_link">' . get_permalink( $cours_data["rel_page"] ) . '</a>';
                            }
                            else { 
                                _e('none','teachcorses');
                            } ?></td>
                      </tr>
                </table>
               </div>
           </div>
           <?php if ( count($course_meta) > 0 ) { ?>
           <div class="postbox">
               <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('Custom meta data','teachcorses'); ?></span></h3>
               <div class="inside">
                   <table cellpadding="8">
                    <?php
                    foreach ($fields as $row) {
                        $col_data = tc_DB_Helpers::extract_column_data($row['value']);
                        $value = '';
                        foreach ( $course_meta as $row_meta ) {
                            if ( $row['variable'] === $row_meta['meta_key'] ) {
                                $value = $row_meta['meta_value'];
                                break;
                            }
                        }
                        echo '<tr>
                               <td width="230"><strong>' . stripslashes($col_data['title']) . '</strong></td>
                               <td> ' . stripslashes($value) . '</td>
                             </tr>';
                     }
                    ?>
                   </table>
               </div>
           </div>
           <?php
           }
           ?>
       </div>
    <?php
    }
    
    /**
     * Shows the documents tab for show_single_course page
     * @param int $course_id
     * @since 5.0.0
     */
    public static function get_documents_tab ($course_id) {
        tc_Document_Manager::init($course_id);
    }
    
}