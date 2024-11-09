<?php
/**
 * This file contains all functions for displaying the add_course page in admin menu
 * 
 * @package teachcourses
 * @subpackage admin
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */


 /**
 * This class contains all funcitons for the add_course_page
 * @since 5.0.0
 */
class TC_Add_Course_Page {

    public static function init() {
        // add_action('admin_menu', array(__CLASS__, 'tc_add_course_page_menu'));
        add_action('admin_head', array(__CLASS__, 'tc_add_course_page_help'));

        $data = get_tc_var_types();
        $data['action'] = isset( $_POST['action'] ) ? htmlspecialchars($_POST['action']) : '';
        $data['type'] = isset( $_POST['course_type'] ) ? htmlspecialchars($_POST['course_type']) : '';
        $data['name'] = isset( $_POST['post_title'] ) ? htmlspecialchars($_POST['post_title']) : '';
        $data['room'] = isset( $_POST['room'] ) ? htmlspecialchars($_POST['room']) : '';
        $data['lecturer'] = isset( $_POST['lecturer'] ) ? htmlspecialchars($_POST['lecturer']) : '';
        $data['date'] = isset( $_POST['date'] ) ? htmlspecialchars($_POST['date']) : '';
        $data['start'] = isset( $_POST['start'] ) ? htmlspecialchars($_POST['start']) : ''; 
        $data['start_hour'] = isset( $_POST['start_hour'] ) ? htmlspecialchars($_POST['start_hour']) : '';
        $data['start_minute'] = isset( $_POST['start_minute'] ) ? htmlspecialchars($_POST['start_minute']) : '';
        $data['end'] = isset( $_POST['end'] ) ? htmlspecialchars($_POST['end']) : '';
        $data['term_id'] = isset( $_POST['term_id'] ) ? htmlspecialchars($_POST['term_id']) : '';
        $data['comment'] = isset( $_POST['comment'] ) ? htmlspecialchars($_POST['comment']) : '';
        $data['visible'] = isset( $_POST['visible'] ) ? intval($_POST['visible']) : 1;
        $data['image_url'] = isset( $_POST['image_url'] ) ? htmlspecialchars($_POST['image_url']) : '';

        // Event Handler
        $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';
        $course_id = isset( $_GET['course_id'] ) ? htmlspecialchars($_GET['course_id']) : 0;

        if ($data["action"] === 'create' ) {
            $course_id = TC_Add_Course_Page::tc_save($data);
        } else if ($data["action"] === 'edit' ) {
            $course_id = TC_Add_Course_Page::tc_edit($course_id, $data);
        } 

        // Default vaulues
        if ( $course_id != 0 ) {
            $data = TC_Courses::get_course($course_id, ARRAY_A);
        }

        TC_Add_Course_Page::tc_add_course_page($data, $course_id);
    }


    static function tc_save($data){
        // Add new course
        $course_id = TC_Courses::add_course($data);
        $message = __('Course created successful.','teachcourses') . ' <a href="admin.php?page=teachcourses&amp;course_id=' . $course_id . '&amp;action=show&amp;search=&amp;sem=' . get_tc_option('sem') . '">' . __('Show course','teachcourses') . '</a> | <a href="admin.php?page=teachcourses/add_course.php">' . __('Add New Course','teachcourses') . '</a>';
        get_tc_message($message);
        return $course_id;
    }

    static function tc_edit($course_id, $data){
        // Saves changes
        TC_Courses::change_course($course_id, $data);
        $message = __('Saved');
        get_tc_message($message);
        return $course_id;
    }

    /**
     * Adds a help tab for add new courses page
     */
    public static function tc_add_course_page_help () {
        $screen = get_current_screen();  
        $screen->add_help_tab( array(
            'id'        => 'tc_add_course_help',
            'title'     => __('Create a new course','teachcourses'),
            'content'   => '<p><strong>' . __('Course name','teachcourses') . '</strong></p>
                            <p>' . __('For child courses: The name of the parent course will be add automatically.','teachcourses') . '</p>
                            <p><strong>' . __('Enrollments','teachcourses') . '</strong></p>
                            <p>' . __('If you have a course without enrollments, so add no dates in the fields start and end. teachcourses will be deactivate the enrollments automatically.','teachcourses') . ' ' . __('Please note, that your local time is not the same as the server time. The current server time is:','teachcourses') . ' <strong>' . current_time('mysql') . '</strong></p>
                            <p><strong>' . __('Strict sign up','teachcourses') . '</strong></p>
                            <p>' . __('This is an option only for parent courses. If you activate it, subscribing is only possible for one of the child courses and not in all. This option has no influence on waiting lists.','teachcourses') . '</p>
                            <p><strong>' . __('Terms and course types','teachcourses') . '</strong></p>
                            <p><a href="options-general.php?page=teachcourses/settings.php&amp;tab=courses">' . __('Add new course types and terms','teachcourses') . '</a></p>'
        ) );
        $screen->add_help_tab( array(
            'id'        => 'tc_add_course_help_2',
            'title'     => __('Visibility','teachcourses'),
            'content'   => '<p>' . __('You can choice between the following visibiltiy options','teachcourses') . ':</p>
                            <ul style="list-style:disc; padding-left:40px;">
                                <li><strong>' . __('normal','teachcourses') . ':</strong> ' . __('The course is visible at the enrollment pages, if enrollments are justified. If it is a parent course, the course is visible at the frontend semester overview.','teachcourses') . '</li>
                                <li><strong>' . __('extend','teachcourses') . ' (' . __('only for parent courses','teachcourses') . '):</strong> ' . __('The same as normal, but in the frontend semester overview all sub-courses will also be displayed.','teachcourses') . '</li>
                                <li><strong>' . __('invisible','teachcourses') . ':</strong> ' . __('The course is invisible.','teachcourses') . '</li></ul>'
        ) );
    }

    /** 
     * Add new courses
     *
     * GET parameters:
     * @param int $course_id
     * @param string $search
     * @param string $sem
    */
    public static function tc_add_course_page($data, $course_id = 0) {
        $current_user = wp_get_current_user();
        $fields = get_tc_options('teachcourses_courses','`setting_id` ASC', ARRAY_A);
        $course_types = get_tc_options('course_type', '`value` ASC');    

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        if ($course_id == 0) {
            _e('Create a new course','teachcourses');
        } else {
            _e('Edit Course','teachcourses');
        }
        echo '</h1>';
        
        echo '<form id="add_course" name="form1" method="post" action="'. esc_url($_SERVER['REQUEST_URI']) .'&action=save">';
        echo '<input name="page" type="hidden" value="teachcourses-add" />';
        echo '<input name="action" type="hidden" value="';
        if ($course_id == 0) {
            echo 'create';
        } else {
            echo 'edit';
        }
        echo '" />';
        echo '<input name="course_id" type="hidden" value="'. $course_id.'" />';
        echo '<input name="upload_mode" id="upload_mode" type="hidden" value="" />';
        echo '<div class="tc_postbody">';
        echo '<div class="tc_postcontent">';
        echo '<div id="post-body">';
        echo '<div id="post-body-content">';
        echo '<div id="titlediv" style="padding-bottom: 15px;">';
        echo '<div id="titlewrap">';
        echo '<label class="hide-if-no-js" style="display:none;" id="title-prompt-text" for="title">'.__('Course name','teachcourses').'</label>';
        echo '<input type="text" name="post_title" title="'.__('Course name','teachcourses').'" size="30" tabindex="1" placeholder="'.__('Course name','teachcourses').'" value="'.stripslashes($data["name"]).'" id="title" autocomplete="off" />';
        echo '</div></div>';
        TC_Add_Course_Page::get_general_box ($course_id, $course_types, $data);

        echo '</div></div></div>';

        echo '</div>';
        echo '<div class="tc_postcontent_right postbox-container">';
        echo '<div id="submitdiv" class="stuffbox">';
        echo '<h3>'.__('Save', 'teachcourses').'</h3>';
        echo '<div class="inside">';
        echo '<div class="misc-pub-section misc-pub-comment-status">';
        echo '<p><label for="visible" title="'.__('Here you can edit the visibility of a course in the enrollments.','teachcourses').'"><strong>'.__('Visibility','teachcourses').'</strong></label></p>';
        echo '<select name="visible" id="visible" tabindex="13">';
        echo '<option value="1" ';
        if ( $course_data["visible"] == 1 && $term_id != 0 ) {echo ' selected="selected"';}
        echo '>'.__('normal','teachcourses').'</option>';
        echo '<option value="2" ';
        if ( $course_data["visible"] == 2 && $term_id != 0 ) {echo ' selected="selected"';}
        echo '>'.__('extend','teachcourses').'</option>';
        echo '<option value="0" ';
        if ( $course_data["visible"] == 0 && $term_id != 0 ) {echo ' selected="selected"';;}
        echo '>'.__('invisible','teachcourses').'</option>';
        echo '</select></div>';
        echo '</div>';
        echo '<div id="major-publishing-actions"><div id="publishing-action"><input type="submit" name="speichern" id="save_publication_submit" value="Save" class="button-primary" title="'.__('Save', 'teachcourses').'"></div><div class="clear"></div></div>';
        echo '</div>';
        TC_Add_Course_Page::get_meta_box ($course_id, $data);

        echo '</div>';

        echo '</form>';    
        echo '<script type="text/javascript" charset="utf-8" src="'. plugins_url( 'js/admin_add_course.js', dirname( __FILE__ ) ).'"></script>';
        echo '</div>';
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
        $semester = TC_Terms::get_terms();
        ?>
        <div class="postbox">
        <h2 class="tc_postbox"><?php _e('General','teachcourses'); ?></h2>
        <div class="inside">
            <p><label for="course_type" title="<?php _e('The course type','teachcourses'); ?>"><strong><?php _e('Type'); ?></strong></label></p>
            <select name="course_type" id="course_type" title="<?php _e('The course type','teachcourses'); ?>" tabindex="2">
            <?php 
                foreach ($course_types as $row) {
                    $check = $course_data["type"] == $row->value ? ' selected="selected"' : '';
                    echo '<option value="' . stripslashes($row->value) . '"' . $check . '>' . stripslashes($row->value) . '</option>';
                } ?>
            </select>
            <p><label for="semester" title="<?php _e('The term where the course will be happening','teachcourses'); ?>"><strong><?php _e('Term','teachcourses'); ?></strong></label></p>
            <select name="semester" id="semester" title="<?php _e('The term where the course will be happening','teachcourses'); ?>" tabindex="3">
            <?php
            foreach ($semester as $sem) { 
                if ($sem->term_id == $selected_sem && $course_id === 0) {
                    $current = 'selected="selected"' ;
                }
                elseif ($sem->term_id == $course_data["term_id"] && $course_id != 0) {
                    $current = 'selected="selected"' ;
                }
                else {
                    $current = '' ;
                }
                echo '<option value="' . stripslashes($sem->term_id) . '" ' . $current . '>' . stripslashes($sem->name) . '</option>';
            }?> 
            </select>
            <?php
            // lecturer
            echo tc_Admin::get_form_field(
                array(
                    'name' => 'lecturer',
                    'title' => __('The lecturer(s) of the course','teachcourses'),
                    'label' => __('Lecturer','teachcourses'),
                    'type' => 'input',
                    'value' => $course_data['lecturer'],
                    'tabindex' => 4,
                    'display' => 'block', 
                    'style' => 'width:95%;') );
        
            
            ?>
            
            <p><label for="comment" title="<?php _e('For parent courses the comment is showing in the overview and for child courses in the enrollments system.','teachcourses'); ?>"><strong><?php _e('Description','teachcourses'); ?></strong></label></p>
            <textarea name="comment" rows="50" id="comment" title="<?php _e('For parent courses the comment is showing in the overview and for child courses in the enrollments system.','teachcourses'); ?>" tabindex="9" style="width:95%;"><?php echo stripslashes($course_data["comment"]); ?></textarea>
        </div>
    <?php
    }
    
    /**
     * Gets the meta box
     * @param int $course_id
     * @param array $course_data
     * @since 5.0.0
     */
    public static function get_meta_box ($course_id, $course_data) {
        ?>
        <div class="postbox">
             <h3 class="tc_postbox"><span><?php _e('Meta','teachcourses'); ?></span></h3>
             <div class="inside">
                <?php if ($course_data["image_url"] != '') {
                    echo '<p><img name="tc_pub_image" src="' . $course_data["image_url"] . '" alt="' . $course_data["name"] . '" title="' . $course_data["name"] . '" style="max-width:100%;"/></p>';
                } ?>
                <p><label for="image_url" title="<?php _e('With the image field you can add an image to a course.','teachcourses'); ?>"><strong><?php _e('Image URL','teachcourses'); ?></strong></label></p>
                <input name="image_url" id="image_url" class="upload" type="text" title="<?php _e('Image URL','teachcourses'); ?>" style="width:90%;" tabindex="12" value="<?php echo $course_data["image_url"]; ?>"/>
        <a class="upload_button_image" title="<?php _e('Add image','teachcourses'); ?>" style="cursor:pointer;"><img src="images/media-button-image.gif" alt="<?php _e('Add Image','teachcourses'); ?>" /></a>
             </div>
             <div id="major-publishing-actions">
                 <div style="text-align: center;">
                    <?php if ($course_id != 0) {?>
                        <input name="save" type="submit" id="teachcourses_create" onclick="teachcourses_validateForm('title','','R','lecturer','','R','platz','','NisNum');return document.teachcourses_returnValue" value="<?php _e('Save'); ?>" class="button-primary"/>
                    <?php } else { ?>
                        <input type="reset" name="Reset" value="<?php _e('Reset','teachcourses'); ?>" class="button-secondary" style="padding-right: 30px;"/> 
                        <input name="create" type="submit" id="teachcourses_create" onclick="teachcourses_validateForm('title','','R','lecturer','','R','platz','','NisNum');return document.teachcourses_returnValue" value="<?php _e('Create','teachcourses'); ?>" class="button-primary"/>
                    <?php } ?>
                 </div>
             </div>
         </div>
        <?php
    }

}
