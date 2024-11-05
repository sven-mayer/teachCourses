<?php
/**
 * This file contains all functions for displaying the add_course page in admin menu
 * 
 * @package teachcorses
 * @subpackage admin
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Adds a help tab for add new courses page
 */
function tc_add_course_page_help () {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tc_add_course_help',
        'title'     => __('Create a new course','teachcorses'),
        'content'   => '<p><strong>' . __('Course name','teachcorses') . '</strong></p>
                        <p>' . __('For child courses: The name of the parent course will be add automatically.','teachcorses') . '</p>
                        <p><strong>' . __('Enrollments','teachcorses') . '</strong></p>
                        <p>' . __('If you have a course without enrollments, so add no dates in the fields start and end. teachCorses will be deactivate the enrollments automatically.','teachcorses') . ' ' . __('Please note, that your local time is not the same as the server time. The current server time is:','teachcorses') . ' <strong>' . current_time('mysql') . '</strong></p>
                        <p><strong>' . __('Strict sign up','teachcorses') . '</strong></p>
                        <p>' . __('This is an option only for parent courses. If you activate it, subscribing is only possible for one of the child courses and not in all. This option has no influence on waiting lists.','teachcorses') . '</p>
                        <p><strong>' . __('Terms and course types','teachcorses') . '</strong></p>
                        <p><a href="options-general.php?page=teachcorses/settings.php&amp;tab=courses">' . __('Add new course types and terms','teachcorses') . '</a></p>'
    ) );
    $screen->add_help_tab( array(
        'id'        => 'tc_add_course_help_2',
        'title'     => __('Visibility','teachcorses'),
        'content'   => '<p>' . __('You can choice between the following visibiltiy options','teachcorses') . ':</p>
                        <ul style="list-style:disc; padding-left:40px;">
                            <li><strong>' . __('normal','teachcorses') . ':</strong> ' . __('The course is visible at the enrollment pages, if enrollments are justified. If it is a parent course, the course is visible at the frontend semester overview.','teachcorses') . '</li>
                            <li><strong>' . __('extend','teachcorses') . ' (' . __('only for parent courses','teachcorses') . '):</strong> ' . __('The same as normal, but in the frontend semester overview all sub-courses will also be displayed.','teachcorses') . '</li>
                            <li><strong>' . __('invisible','teachcorses') . ':</strong> ' . __('The course is invisible.','teachcorses') . '</li></ul>'
    ) );
    $screen->add_help_tab( array(
        'id'        => 'tc_add_course_help_3',
        'title'     => __('Capabilities','teachcorses'),
        'content'   => '<p>' . __('You can choice between the following capability options','teachcorses') . ':</p>
                        <ul style="list-style:disc; padding-left:40px;">
                            <li><strong>' . __('global','teachcorses') . ':</strong> ' . __('All users, which have the minimum user role for using teachcorses, can see, edit or delete the course or course data.','teachcorses') . '</li>
                            <li><strong>' . __('local','teachcorses') . ':</strong> ' . __('You can select which users can see, edit or delete the course or course data.','teachcorses') . '</li>
                            </ul>'
    ) );
}

/** 
 * Add new courses
 *
 * GET parameters:
 * @param int $course_id
 * @param string $search
 * @param string $sem
 * @param string $ref
*/
function tc_add_course_page() {

   $current_user = wp_get_current_user();
   $fields = get_tc_options('teachcorses_courses','`setting_id` ASC', ARRAY_A);
   $course_types = get_tc_options('course_type', '`value` ASC');

   $data['type'] = isset( $_POST['course_type'] ) ? htmlspecialchars($_POST['course_type']) : '';
   $data['name'] = isset( $_POST['post_title'] ) ? htmlspecialchars($_POST['post_title']) : '';
   $data['room'] = isset( $_POST['room'] ) ? htmlspecialchars($_POST['room']) : '';
   $data['lecturer'] = isset( $_POST['lecturer'] ) ? htmlspecialchars($_POST['lecturer']) : '';
   $data['date'] = isset( $_POST['date'] ) ? htmlspecialchars($_POST['date']) : '';
   $data['places'] = isset( $_POST['places'] ) ? intval($_POST['places']) : 0;
   $data['start'] = isset( $_POST['start'] ) ? htmlspecialchars($_POST['start']) : ''; 
   $data['start_hour'] = isset( $_POST['start_hour'] ) ? htmlspecialchars($_POST['start_hour']) : '';
   $data['start_minute'] = isset( $_POST['start_minute'] ) ? htmlspecialchars($_POST['start_minute']) : '';
   $data['end'] = isset( $_POST['end'] ) ? htmlspecialchars($_POST['end']) : '';
   $data['end_hour'] = isset( $_POST['end_hour'] ) ? htmlspecialchars($_POST['end_hour']) : '';
   $data['end_minute'] = isset( $_POST['end_minute'] ) ? htmlspecialchars($_POST['end_minute']) : '';
   $data['semester'] = isset( $_POST['semester'] ) ? htmlspecialchars($_POST['semester']) : '';
   $data['comment'] = isset( $_POST['comment'] ) ? htmlspecialchars($_POST['comment']) : '';
   $data['rel_page'] = isset( $_POST['rel_page'] ) ? intval($_POST['rel_page']) : 0;
   $data['rel_page_alter'] = isset( $_POST['rel_page_alter'] ) ? intval($_POST['rel_page_alter']) : 0;
   $data['parent'] = isset( $_POST['parent2'] ) ? intval($_POST['parent2']) : 0;
   $data['visible'] = isset( $_POST['visible'] ) ? intval($_POST['visible']) : 1;
   $data['waitinglist'] = isset( $_POST['waitinglist'] ) ? intval($_POST['waitinglist']) : 0;
   $data['image_url'] = isset( $_POST['image_url'] ) ? htmlspecialchars($_POST['image_url']) : '';
   $data['strict_signup'] = isset( $_POST['strict_signup'] ) ? intval($_POST['strict_signup']) : 0;
   $data['use_capabilities'] = isset( $_POST['use_capabilities'] ) ? intval($_POST['use_capabilities']) : 0;
   
   $sub['type'] = isset( $_POST['sub_course_type'] ) ? htmlspecialchars($_POST['sub_course_type']) : '';
   $sub['number'] = isset( $_POST['sub_number'] ) ? intval($_POST['sub_number']) : 0;
   $sub['places'] = isset( $_POST['sub_places'] ) ? intval($_POST['sub_places']) : 0;

   // Handle that the activation of strict sign up is not possible for a child course
   if ( $data['parent'] != 0) { $data['strict_signup'] = 0; }

   $course_id = isset( $_REQUEST['course_id'] ) ? intval($_REQUEST['course_id']) : 0;
   $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
   $sem = isset( $_GET['sem'] ) ? htmlspecialchars($_GET['sem']) : '';
   $ref = isset( $_GET['ref'] ) ? htmlspecialchars($_GET['ref']) : '';
   $capability = ($course_id !== 0) ? tc_Courses::get_capability($course_id, $current_user->ID) : 'owner';
   
   // If the user has no permissions to edit this course
   if ( $course_id !== 0 && ( $capability !== 'owner' && $capability !== 'approved' ) ) {
       echo '<div class="wrap">';
       get_tc_message(__('You have no capabilities to edit this course','teachcorses'), 'red');
       echo '</div>';
       return;
   }
   
    echo '<div class="wrap">';
    echo '<h2>';
    if ($course_id == 0) {
        echo _e('Create a new course','teachcorses');
    } else {
        echo _e('Edit Course','teachcorses');
    }
    echo '</h2>';
   
        // Add new course
        if ( isset($_POST['create']) ) {
             $course_id = tc_Courses::add_course($data, $sub);
             tc_DB_Helpers::prepare_meta_data($course_id, $fields, $_POST, 'courses');
             $message = __('Course created successful.','teachcorses') . ' <a href="admin.php?page=teachcorses/teachcorses.php&amp;course_id=' . $course_id . '&amp;action=show&amp;search=&amp;sem=' . get_tc_option('sem') . '">' . __('Show course','teachcorses') . '</a> | <a href="admin.php?page=teachcorses/add_course.php">' . __('Add new','teachcorses') . '</a>';
             get_tc_message($message);
        }

        // Saves changes
        if ( isset($_POST['save']) ) {
             tc_Courses::delete_course_meta($course_id);
             tc_Courses::change_course($course_id, $data);
             tc_DB_Helpers::prepare_meta_data($course_id, $fields, $_POST, 'courses');
             $message = __('Saved');
             get_tc_message($message);
        }

        // Default vaulues
        if ( $course_id != 0 ) {
             $course_data = tc_Courses::get_course($course_id, ARRAY_A);
             $course_meta = tc_Courses::get_course_meta($course_id);
        }
        else {
             $course_data = get_tc_var_types('course_array');
             $course_meta = array ( array('meta_key' => '', 'meta_value' => '') );
        }
    
    echo '<form id="add_course" name="form1" method="post" action="'. esc_url($_SERVER['REQUEST_URI']) .'">';
    echo '<input name="page" type="hidden" value="';
    if ($course_id != 0) {
        echo 'teachcorses/teachcorses.php';
    } else {
        echo 'teachcorses/add_course.php';
    }
    echo '" />';
    echo '<input name="action" type="hidden" value="edit" />';
    echo '<input name="course_id" type="hidden" value="'. $course_id.'" />';
    echo '<input name="sem" type="hidden" value="'. $sem.'" />';
    echo '<input name="search" type="hidden" value="'. $search.'" />';
    echo '<input name="ref" type="hidden" value="'.$ref.'" />';
    echo '<input name="upload_mode" id="upload_mode" type="hidden" value="" />';
    echo '<div class="tc_postbody">';
    echo '<div class="tc_postcontent">';
    echo '<div id="post-body">';
    echo '<div id="post-body-content">';
    echo '<div id="titlediv" style="padding-bottom: 15px;">';
    echo '<div id="titlewrap">';
    echo '<label class="hide-if-no-js" style="display:none;" id="title-prompt-text" for="title">'._e('Course name','teachcorses').'</label>';
    echo '<input type="text" name="post_title" title="'._e('Course name','teachcorses').'" size="30" tabindex="1" placeholder="'._e('Course name','teachcorses').'" value="'.stripslashes($course_data["name"]).'" id="title" autocomplete="off" />';
    echo '</div></div>';
    tc_Add_Course::get_general_box ($course_id, $course_types, $course_data);

    if ( $course_id === 0 ) { 
    tc_Add_Course::get_subcourses_box($course_types, $course_data);
    }
    if ( count($fields) !== 0 ) { 
    tc_Admin::display_meta_data($fields, $course_meta);       
    } 
    echo '</div></div></div>';
    echo '<div class="tc_postcontent_right"';
    tc_Add_Course::get_meta_box ($course_id, $course_data, $capability);
    tc_Add_Course::get_enrollments_box ($course_id, $course_data);

    echo '</div>';
    echo '</div>';

    echo '</form>';    
    echo '<script type="text/javascript" charset="utf-8" src="'. plugins_url( 'js/admin_add_course.js', dirname( __FILE__ ) ).'"></script>';
    echo '</div>';
}

/**
 * This class contains all funcitons for the add_course_page
 * @since 5.0.0
 */
class tc_Add_Course {
    
    /**
     * Gets the enrollment box
     * @param int $course_id
     * @param array $course_data
     * @since 5.0.0
     */
    public static function get_enrollments_box ($course_id, $course_data) {
        ?>
        <div class="postbox">
             <h3 class="tc_postbox"><span><?php _e('Enrollments','teachcorses'); ?></span></h3>
             <div class="inside">
                 <p><label for="start" title="<?php _e('The start date for the enrollment','teachcorses'); ?>"><strong><?php _e('Start','teachcorses'); ?></strong></label></p>
                <?php 
                 if ($course_id === 0) {
                    $meta = 'value="' . __('JJJJ-MM-TT','teachcorses') . '" onblur="if(this.value==' . "'" . "'" . ') this.value=' . "'" . __('JJJJ-MM-TT','teachcorses') . "'" . ';" onfocus="if(this.value==' . "'" . __('JJJJ-MM-TT','teachcorses') . "'" . ') this.value=' . "'" . "'" . ';"';
                    $hour = '00';
                    $minute = '00';
                 }	
                 else {
                    $date1 = tc_datesplit($course_data["start"]);
                    $meta = 'value="' . $date1[0][0] . '-' . $date1[0][1] . '-' . $date1[0][2] . '"';
                    $hour = $date1[0][3];
                    $minute = $date1[0][4]; 
                 }	
                 ?>
                 <input name="start" type="text" id="start" title="<?php _e('Date','teachcorses'); ?>" tabindex="14" size="15" <?php echo $meta; ?>/> <input name="start_hour" type="text" title="<?php _e('Hours','teachcorses'); ?>" value="<?php echo $hour; ?>" size="2" tabindex="15" /> : <input name="start_minute" type="text" title="<?php _e('Minutes','teachcorses'); ?>" value="<?php echo $minute; ?>" size="2" tabindex="16" />
                 <p><label for="end" title="<?php _e('The end date for the enrollment','teachcorses'); ?>"><strong><?php _e('End','teachcorses'); ?></strong></label></p>
                <?php 
                 if ($course_id === 0) {
                      // same as for start
                 }
                 else {
                    $date1 = tc_datesplit($course_data["end"]);
                    $meta = 'value="' . $date1[0][0] . '-' . $date1[0][1] . '-' . $date1[0][2] . '"';
                    $hour = $date1[0][3];
                    $minute = $date1[0][4];
                 }
                 ?>
                 <input name="end" type="text" id="end" title="<?php _e('Date','teachcorses'); ?>" tabindex="17" size="15" <?php echo $meta; ?>/> <input name="end_hour" type="text" title="<?php _e('Hours','teachcorses'); ?>" value="<?php echo $hour; ?>" size="2" tabindex="18" /> : <input name="end_minute" type="text" title="<?php _e('Minutes','teachcorses'); ?>" value="<?php echo $minute; ?>" size="2" tabindex="19" />
              <p><strong><?php _e('Options','teachcorses'); ?></strong></p>
               <?php
                 $check = $course_data["waitinglist"] == 1 ? 'checked="checked"' : '';
                 ?>
                  <p><input name="waitinglist" id="waitinglist" type="checkbox" value="1" tabindex="26" <?php echo $check; ?>/> <label for="waitinglist" title="<?php _e('Waiting list','teachcorses'); ?>"><?php _e('Waiting list','teachcorses'); ?></label></p>
                <p>
                <?php 
                 if ($course_data["parent"] != 0) {
                    $parent_data_strict = tc_Courses::get_course_data($course_data["parent"], 'strict_signup'); 
                    $check = $parent_data_strict == 1 ? 'checked="checked"' : '';
                    ?>
                    <input name="strict_signup_2" id="strict_signup_2" type="checkbox" value="1" tabindex="27" <?php echo $check; ?> disabled="disabled" /> <label for="strict_signup_2" title="<?php _e('This is a child course. You can only change this option in the parent course','teachcorses'); ?>"><?php _e('Strict sign up','teachcorses'); ?></label></p>
           <?php } else {
                    $check = $course_data["strict_signup"] == 1 ? 'checked="checked"' : '';
                    ?>
                 <input name="strict_signup" id="strict_signup" type="checkbox" value="1" tabindex="27" <?php echo $check; ?> /> <label for="strict_signup" title="<?php _e('This is an option only for parent courses. If you activate it, subscribing is only possible for one of the child courses and not in all. This option has no influence on waiting lists.','teachcorses'); ?>"><?php _e('Strict sign up','teachcorses'); ?></label></p>
         <?php } ?>
             </div>
         </div>
        <?php
    }

    /**
     * Gets the general box
     * @param int $course_id
     * @param array $course_types
     * @param array $course_data
     * @since 5.0.0
     */
    public static function get_general_box ($course_id, $course_types, $course_data) {
        $post_type = get_tc_option('rel_page_courses');
        $selected_sem = ( $course_id === 0 ) ? get_tc_option('sem') : 0;
        $semester = get_tc_options('semester', '`setting_id` DESC');
        ?>
        <div class="postbox">
        <h3 class="tc_postbox"><span><?php _e('General','teachcorses'); ?></span></h3>
        <div class="inside">
            <p><label for="course_type" title="<?php _e('The course type','teachcorses'); ?>"><strong><?php _e('Type'); ?></strong></label></p>
            <select name="course_type" id="course_type" title="<?php _e('The course type','teachcorses'); ?>" tabindex="2">
            <?php 
                foreach ($course_types as $row) {
                    $check = $course_data["type"] == $row->value ? ' selected="selected"' : '';
                    echo '<option value="' . stripslashes($row->value) . '"' . $check . '>' . stripslashes($row->value) . '</option>';
                } ?>
            </select>
            <p><label for="semester" title="<?php _e('The term where the course will be happening','teachcorses'); ?>"><strong><?php _e('Term','teachcorses'); ?></strong></label></p>
            <select name="semester" id="semester" title="<?php _e('The term where the course will be happening','teachcorses'); ?>" tabindex="3">
            <?php
            foreach ($semester as $sem) { 
                if ($sem->value == $selected_sem && $course_id === 0) {
                    $current = 'selected="selected"' ;
                }
                elseif ($sem->value == $course_data["semester"] && $course_id != 0) {
                    $current = 'selected="selected"' ;
                }
                else {
                    $current = '' ;
                }
                echo '<option value="' . stripslashes($sem->value) . '" ' . $current . '>' . stripslashes($sem->value) . '</option>';
            }?> 
            </select>
            <?php
            // lecturer
            echo tc_Admin::get_form_field(
                array(
                    'name' => 'lecturer',
                    'title' => __('The lecturer(s) of the course','teachcorses'),
                    'label' => __('Lecturer','teachcorses'),
                    'type' => 'input',
                    'value' => $course_data['lecturer'],
                    'tabindex' => 4,
                    'display' => 'block', 
                    'style' => 'width:95%;') );
            
            // date
            echo tc_Admin::get_form_field(
                array(
                    'name' => 'date',
                    'title' => __('The date(s) for the course','teachcorses'),
                    'label' => __('Date','teachcorses'),
                    'type' => 'input',
                    'value' => $course_data['date'],
                    'tabindex' => 5,
                    'display' => 'block', 
                    'style' => 'width:95%;') );
            
            // room
            echo tc_Admin::get_form_field(
                array(
                    'name' => 'room',
                    'title' => __('The room or place for the course','teachcorses'),
                    'label' => __('Room','teachcorses'),
                    'type' => 'input',
                    'value' => $course_data['room'],
                    'tabindex' => 6,
                    'display' => 'block', 
                    'style' => 'width:95%;') );
            
            ?>
            <p><label for="places" title="<?php _e('The number of available places.','teachcorses'); ?>"><strong><?php _e('Number of places','teachcorses'); ?></strong></label></p>
            <input name="places" type="text" id="places" title="<?php _e('The number of available places.','teachcorses'); ?>" style="width:70px;" tabindex="7" value="<?php echo $course_data["places"]; ?>" />
            <?php 
            if ($course_id != 0) {
                $free_places = tc_Courses::get_free_places($course_data["course_id"], $course_data["places"]);
                echo ' | ' . __('free places','teachcorses') . ': ' . $free_places;
            } 
            tc_Add_Course::get_parent_select_field($course_id, $course_data);
            ?>
            
            <p><label for="comment" title="<?php _e('For parent courses the comment is showing in the overview and for child courses in the enrollments system.','teachcorses'); ?>"><strong><?php _e('Comment or Description','teachcorses'); ?></strong></label></p>
            <textarea name="comment" rows="3" id="comment" title="<?php _e('For parent courses the comment is showing in the overview and for child courses in the enrollments system.','teachcorses'); ?>" tabindex="9" style="width:95%;"><?php echo stripslashes($course_data["comment"]); ?></textarea>
            <p><label for="rel_page" title="<?php _e('If you will connect a course with a page (it is used as link in the courses overview) so you can do this here','teachcorses'); ?>"><strong><?php _e('Related content','teachcorses'); ?></strong></label></p>

            <div id="rel_page_alternative" style="display:none;">
                <?php _e('Select draft','teachcorses');?>: 
                <select name="rel_page_alter" id="rel_page_alter" title="<?php _e('If you will connect a course with a post or page (it is used as link in the courses overview) so you can do this here','teachcorses'); ?>" tabindex="10">
                    <?php
                    get_tc_wp_drafts($post_type, 'draft', 'post_date', 'DESC');
                    ?>
                </select>
                <a onclick="javascript:teachcorses_switch_rel_page_container();" style="cursor:pointer;"><?php _e('Use existing content','teachcorses');?></a>
            </div>
            <div id="rel_page_original" style="display:block;">
                <?php _e('Select related content','teachcorses');?>: 
                <select name="rel_page" id="rel_page" title="<?php _e('If you will connect a course with a post or page (it is used as link in the courses overview) so you can do this here','teachcorses'); ?>" tabindex="10">
                    <?php 
                    get_tc_wp_pages("menu_order","ASC",$course_data["rel_page"],$post_type,0,0); 
                    ?>
                </select>
                <a onclick="javascript:teachcorses_switch_rel_page_container();" style="cursor:pointer;"><?php _e('Create from draft','teachcorses');?></a>
            </div>
        </div>
    </div>
    <?php
    }
    
    /**
     * Gets the meta box
     * @param int $course_id
     * @param array $course_data
     * @param array $capability
     * @since 5.0.0
     */
    public static function get_meta_box ($course_id, $course_data, $capability) {
        ?>
        <div class="postbox">
             <h3 class="tc_postbox"><span><?php _e('Meta','teachcorses'); ?></span></h3>
             <div class="inside">
                <?php if ($course_data["image_url"] != '') {
                    echo '<p><img name="tc_pub_image" src="' . $course_data["image_url"] . '" alt="' . $course_data["name"] . '" title="' . $course_data["name"] . '" style="max-width:100%;"/></p>';
                } ?>
                <p><label for="image_url" title="<?php _e('With the image field you can add an image to a course.','teachcorses'); ?>"><strong><?php _e('Image URL','teachcorses'); ?></strong></label></p>
                <input name="image_url" id="image_url" class="upload" type="text" title="<?php _e('Image URL','teachcorses'); ?>" style="width:90%;" tabindex="12" value="<?php echo $course_data["image_url"]; ?>"/>
        <a class="upload_button_image" title="<?php _e('Add image','teachcorses'); ?>" style="cursor:pointer;"><img src="images/media-button-image.gif" alt="<?php _e('Add Image','teachcorses'); ?>" /></a>
                <p><label for="visible" title="<?php _e('Here you can edit the visibility of a course in the enrollments.','teachcorses'); ?>"><strong><?php _e('Visibility','teachcorses'); ?></strong></label></p>
                <select name="visible" id="visible" title="<?php _e('Here you can edit the visibility of a course in the enrollments.','teachcorses'); ?>" tabindex="13">
                    <option value="1"<?php if ( $course_data["visible"] == 1 && $course_id != 0 ) {echo ' selected="selected"'; } ?>><?php _e('normal','teachcorses'); ?></option>
                    <option value="2"<?php if ( $course_data["visible"] == 2 && $course_id != 0 ) {echo ' selected="selected"'; } ?>><?php _e('extend','teachcorses'); ?></option>
                    <option value="0"<?php if ( $course_data["visible"] == 0 && $course_id != 0 ) {echo ' selected="selected"'; } ?>><?php _e('invisible','teachcorses'); ?></option>
                </select>
                <?php
                $readonly = 'disabled="disabled"';
                if ( $capability === 'owner' || $capability === 'approved' ) {
                    $readonly = '';
                }
                ?>
                <p><label for="use_capabilities"><strong><?php _e('Capabilities','teachcorses'); ?></strong></label></p>
                <select name="use_capabilities" <?php echo $readonly; ?>>
                    <option value="0"<?php if ( $course_data["use_capabilities"] == 0 && $course_id != 0 ) {echo ' selected="selected"'; } ?>><?php _e('global','teachcorses'); ?></option>
                    <option value="1"<?php if ( $course_data["use_capabilities"] == 1 && $course_id != 0 ) {echo ' selected="selected"'; } ?>><?php _e('local','teachcorses'); ?></option>
                </select>
             </div>
             <div id="major-publishing-actions">
                 <div style="text-align: center;">
                    <?php if ($course_id != 0) {?>
                        <input name="save" type="submit" id="teachcorses_create" onclick="teachcorses_validateForm('title','','R','lecturer','','R','platz','','NisNum');return document.teachcorses_returnValue" value="<?php _e('Save'); ?>" class="button-primary"/>
                    <?php } else { ?>
                        <input type="reset" name="Reset" value="<?php _e('Reset','teachcorses'); ?>" class="button-secondary" style="padding-right: 30px;"/> 
                        <input name="create" type="submit" id="teachcorses_create" onclick="teachcorses_validateForm('title','','R','lecturer','','R','platz','','NisNum');return document.teachcorses_returnValue" value="<?php _e('Create','teachcorses'); ?>" class="button-primary"/>
                    <?php } ?>
                 </div>
             </div>
         </div>
        <?php
    }
    
    /**
     * Gets a select field for the parent course
     * @param int $course_id
     * @param array $course_data
     * @since 5.0.0
     */
    private static function get_parent_select_field ($course_id, $course_data) {
        $semester = get_tc_options('semester', '`setting_id` DESC');
        ?>
        <p><label for="parent2" title="<?php _e('Here you can connect a course with a parent one. With this function you can create courses with an hierarchical order.','teachcorses'); ?>"><strong><?php _e('Parent course','teachcorses'); ?></strong></label></p>
            <select name="parent2" id="parent2" title="<?php _e('Here you can connect a course with a parent one. With this function you can create courses with an hierarchical order.','teachcorses'); ?>" onchange="teachcorses_courseFields();" tabindex="8">
                <option value="0"><?php _e('none','teachcorses'); ?></option>
                <?php
                foreach ( $semester as $row ) {
                    $courses = tc_Courses::get_courses( array('parent' => 0, 'semester' => $row->value) );
                    if ( count($courses) !== 0 ) {
                        echo '<optgroup label="' . $row->value . '">';
                    }
                    foreach ( $courses as $course ) {
                        if ( $course->course_id == $course_id ) {
                            continue;
                        }
                        $current = ( $course->course_id == $course_data["parent"] ) ? 'selected="selected"' : '';
                        echo '<option value="' . $course->course_id . '" ' . $current . '>' . $course->course_id . ' - ' . stripslashes($course->name) . '</option>';
                    }
                    if ( count($courses) !== 0 ) {
                        echo '</optgroup>';
                    }
                }
                ?>
                
            </select>
        <?php
    }

    /**
     * Gets the subcourses box
     * @param int $course_types
     * @param array $course_data
     * @since 5.0.0
     */
    public static function get_subcourses_box ($course_types, $course_data) {
        ?>
        <div class="postbox">
            <h3 class="tc_postbox"><span><?php _e('Sub courses','teachcorses'); ?></span></h3>
            <div class="inside">
                <p><label for="sub_course_type" title="<?php _e('The course type','teachcorses'); ?>"><strong><?php _e('Type'); ?></strong></label></p>
                 <select name="sub_course_type" id="sub_course_type" title="<?php _e('The course type','teachcorses'); ?>" tabindex="17">
                 <?php 
                     foreach ($course_types as $row) {
                         $check = $course_data["type"] == $row->value ? ' selected="selected"' : '';
                         echo '<option value="' . stripslashes($row->value) . '"' . $check . '>' . stripslashes($row->value) . '</option>';
                     } ?>
                 </select>
                 <?php
                // number of subcourses
                echo tc_Admin::get_form_field(
                    array(
                        'name' => 'sub_number',
                        'title' => __('Number of sub courses','teachcorses'),
                        'label' => __('Number of sub courses','teachcorses'),
                        'type' => 'input',
                        'value' => '0',
                        'tabindex' => 18,
                        'display' => 'block', 
                        'style' => 'width:70px;') );
              
                // places
                echo tc_Admin::get_form_field(
                    array(
                        'name' => 'sub_places',
                        'title' => __('Number of places per course','teachcorses'), 
                        'label' => __('Number of places per course','teachcorses'),
                        'type' => 'input',
                        'value' => '0',
                        'tabindex' => 19,
                        'display' => 'block', 
                        'style' => 'width:70px;') );
             ?>
            </div>
         </div>
        <?php
    }
}
