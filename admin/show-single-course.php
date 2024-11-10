<?php 
/**
 * This file contains all functions for displaying the show_single_course page in admin menu
 * 
 * @package teachcourses
 * @subpackage admin
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/** 
 * Single course overview
 * 
 * $_GET parameters:
 * @param int $course_id    course ID
 * @param string $term_id       semester, from show_courses.php
 * @param string $search    search string, from show_courses.php
*/
function tc_show_single_course_page() {
    
    $current_user = wp_get_current_user();

    // form
    $checkbox = ( isset( $_POST['checkbox'] ) ) ?  $_POST['checkbox'] : '';
    $waiting = isset( $_POST['waiting'] ) ?  $_POST['waiting'] : '';
    $reg_action = isset( $_POST['reg_action'] ) ?  $_POST['reg_action'] : '';
    $course_id = intval($_GET['course_id']);
    
    $link_parameter['term_id'] = htmlspecialchars($_GET['term_id']);
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
    $course_data = TC_Courses::get_course($course_id, ARRAY_A);

    echo '<div class="wrap">';
    tc_Single_Course_Actions::do_actions($course_id, $_POST);
    
    echo '<form id="einzel" name="einzel" action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
    echo '<input name="page" type="hidden" value="teachcourses.php">';
    echo '<input name="action" type="hidden" value="' . $action . '" />';
    echo '<input name="course_id" type="hidden" value="' . $course_id . '" />';
    echo '<input name="sem" type="hidden" value="' . $link_parameter['term_id'] . '" />';
    echo '<input name="search" type="hidden" value="' . $link_parameter['search'] . '" />';
    echo '<input name="redirect" type="hidden" value="' . $link_parameter['redirect'] . '" />';
    echo '<input name="sort" type="hidden" value="' . $link_parameter['sort'] . '" />';
    echo '<input name="order" type="hidden" value="' . $link_parameter['order'] . '" />';
    
    echo tc_Single_Course_Page::get_course_headline($course_id, $course_data, $link_parameter);
    echo tc_Single_Course_Page::get_menu($course_id, $link_parameter, $action);
    
    echo '<div style="width:100%; float:left; margin-top: 12px;">';
    
    // Show tab content
    if ( $action === 'documents') {
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
 * @package teachcourses
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
        $data = array('course_id' => $course_id, 
                      'title' => htmlspecialchars($post['artefact_name']), 
                      'scale' => '', 
                      'passed' => '', 
                      'max_value' => '');
        tc_Artefacts::add_artefact($data);
        get_tc_message( __('Artefact added','teachcourses') );
    }
    
    /**
     * Deletes an artefact
     * @param int $artefact_id
     * @since 5.0.0
     * @access private
     */
    private static function delete_artefact($artefact_id) {
        if ( tc_Artefacts::has_assessments($artefact_id) === true ) {
            get_tc_message( __('Removing not possible. Delete the assessments first.','teachcourses'), 'red' );
            return;
        }
        tc_Artefacts::delete_artefact($artefact_id);
        get_tc_message( __('Removing successful','teachcourses') );
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
     * Handles all database actions for the single course page
     * @param int $course_id
     * @param array $post
     * @param array $waiting
     * @param string $reg_action
     */
    public static function do_actions($course_id, $post) {
        // Add artefact
        if ( isset( $post['add_artefact'] ) ) {
            self::add_artefact($course_id, $post);
        }
        // Edit artefact
        if ( isset( $post['tc_save_artefact'] ) ) {
            self::change_artefact($post);
        }
        // Delete artefact
        if ( isset( $_GET['delete_artefact'] ) ) {
            self::delete_artefact($_GET['delete_artefact']);
            
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
        echo '<div id="tc_add_artefact_form" class="teachcourses_message" style="display:none;">';
        echo '<p class="teachcourses_message_headline">' . __('Add artefact','teachcourses') . '</p>';

        echo '<p><label for="artefact_name">' . __('Title','teachcourses') . '</label></p>';
        echo '<input name="artefact_name" id="artefact_name" type="text" style="width:50%;"/>';
        

        echo '<p><input name="add_artefact" type="submit" class="button-primary" value="' . __('Add','teachcourses') . '"/> <a onclick="teachcourses_showhide(' . "'tc_add_artefact_form'" . ');" class="button-secondary" style="cursor:pointer;">' . __('Cancel', 'teachcourses') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Returns the page headline
     * @param int $course_id
     * @param array $course_data
     * @param array $link_parameter
     * @param string $edit_link
     * @return string
     * @since 5.0.0
     */
    public static function get_course_headline($course_id, $course_data, $link_parameter) {
        $link = '<a class="page-title-action" href="admin.php?page=teachcourses-add&amp;action=edit&amp;course_id=' . $course_id . '&amp;term_id=' . $link_parameter['term_id'] . '&amp;search=' . $link_parameter['search'] . '" class="teachcourses_link" style="cursor:pointer;">' . __('Edit','teachcourses') . '</a>';

        return '<h1 class="wp-heading-inline">' . stripslashes($course_data["name"]) . ' ' . $link . '</h1>';
    }
    
    /**
     * Returns the page menu
     * @param int $course_id
     * @param array $link_parameter
     * @param string $action
     * @return string
     * @since 5.0.0
     */
    public static function get_menu($course_id, $link_parameter, $action){
        $assessment_tab = '';
        $documents_tab = '';
        
        $set_info_tab = ( $action === 'show' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $info_tab = '<a href="admin.php?page=teachcourses&amp;course_id=' . $course_id . '&amp;term_id=' . $link_parameter['term_id'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show" class="' . $set_info_tab . '">' . __('Info','teachcourses') . '</a> ';
        
        
        $set_documents_tab = ( $action === 'documents' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $documents_tab = '<a href="admin.php?page=teachcourses&amp;course_id=' . $course_id . '&amp;term_id=' . $link_parameter['term_id'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=documents" class="' . $set_documents_tab . '">' . __('Documents','teachcourses') . '</a> ';
        
        
        return '<h3 class="nav-tab-wrapper">' . $info_tab . $documents_tab . $assessment_tab . '</h3>';
    }
    
    /**
     * Shows the info tab for show_single_course page
     * @param int $course_id    The ID of the course
     * @param array $cours_data An associative array with course data
     * @since 5.0.0
     */
    public static function get_info_tab ($course_id, $cours_data) {
        $fields = get_tc_options('teachcourses_courses','`setting_id` ASC', ARRAY_A);
        $course_meta = TC_Courses::get_course($course_id);
        ?>
        <div style="width:100%">
           <div class="postbox">
               <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('General','teachcourses'); ?></span></h3>
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
                        <td><strong><?php _e('Visibility','teachcourses'); ?></strong></td>
                        <td>
                         <?php 
                            if ( $cours_data["visible"] == 1 ) {
                                 _e('normal','teachcourses');
                            }
                            elseif ( $cours_data["visible"] == 2 ) {
                                 _e('extend','teachcourses');
                            }
                            else {
                                 _e('invisible','teachcourses');
                            } 
                         ?></td> 
                      </tr>
                      <tr>
                        <td><strong><?php _e('Date','teachcourses'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["date"]); ?></td>
                      </tr>
                      <tr>
                          <td><strong><?php _e('Room','teachcourses'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["room"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Lecturer','teachcourses'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["lecturer"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Comment','teachcourses'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["comment"]); ?></td>
                      </tr>
                </table>
               </div>
           </div>
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