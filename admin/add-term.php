<?php
/**
 * This file contains all functions for displaying the add_term page in admin menu
 * 
 * @package teachcourses
 * @subpackage admin
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */


 /**
 * This class contains all funcitons for the add_term_page
 * @since 5.0.0
 */
class TC_Add_Term_Page {

    public static function init() {

        $data = get_tc_var_types();
        $data['action'] = isset( $_POST['action'] ) ? htmlspecialchars($_POST['action']) : '';
        $data['name'] = isset( $_POST['post_title'] ) ? htmlspecialchars($_POST['post_title']) : '';
        $data['slug'] = isset( $_POST['slug'] ) ? htmlspecialchars($_POST['slug']) : '';
        $data['sequence'] = isset( $_POST['sequence'] ) ? htmlspecialchars($_POST['sequence']) : '';
        $data['visible'] = isset( $_POST['visible'] ) ? htmlspecialchars($_POST['visible']) : '';
        

        // Event Handler
        $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';
        $term_id = isset( $_GET['term_id'] ) ? htmlspecialchars($_GET['term_id']) : 0;

        if ($data["action"] === 'create' ) {
            $term_id = TC_Add_Term_Page::tc_save($data);
        } else if ($data["action"] === 'edit' ) {
            $term_id = TC_Add_Term_Page::tc_edit($term_id, $data);
        } 

        // Default vaulues
        if ( $term_id != 0 ) {
            $data = TC_Terms::get_term($term_id, ARRAY_A);
        }

        TC_Add_Term_Page::TC_Add_Term_Page($data, $term_id);
    }


    static function tc_save($data){
        // Add new term
        $term_id = TC_Terms::add_term($data);
        $message = __('Term created successful.','teachcourses');
        get_tc_message($message);
        return $term_id;
    }

    static function tc_edit($term_id, $data){
        // Saves changes
        TC_Terms::change_term($term_id, $data);
        $message = __('Saved');
        get_tc_message($message);
        return $term_id;
    }

    /** 
     * Add new term
     *
     * GET parameters:
     * @param int $term_id
     * @param string $search
     * @param string $sem
    */
    public static function TC_Add_Term_Page($data, $term_id = 0) {
        $current_user = wp_get_current_user();
        $fields = get_tc_options('teachcourses_courses','`setting_id` ASC', ARRAY_A);

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        if ($term_id == 0) {
            _e('Create a New Term','teachcourses');
        } else {
            _e('Edit Term','teachcourses');
        }
        echo '</h1>';
        
        echo '<form id="add_course" name="form1" method="post" action="admin.php?page=teachcourses-term&action=save">';
        echo '<input name="action" type="hidden" value="';
        if ($term_id == 0) {
            echo 'create';
        } else {
            echo 'edit';
        }
        echo '" />';
        echo '<input name="term_id" type="hidden" value="'. $term_id.'" />';
        echo '<input name="upload_mode" id="upload_mode" type="hidden" value="" />';
        echo '<div class="tc_postbody">';
        echo '<div class="tc_postcontent">';
        echo '<div id="post-body">';
        echo '<div id="post-body-content">';
        echo '<div id="titlediv" style="padding-bottom: 15px;">';
        echo '<div id="titlewrap">';
        echo '<label class="hide-if-no-js" style="display:none;" id="title-prompt-text" for="title">'.__('Term name','teachcourses').'</label>';
        echo '<input type="text" name="post_title" title="'.__('Term name','teachcourses').'" size="30" tabindex="1" placeholder="'.__('Term name','teachcourses').'" value="'.stripslashes($data["name"]).'" id="title" autocomplete="off" />';
        echo '</div></div>';
        TC_Add_Term_Page::get_general_box ($term_id, $data);

        echo '</div></div></div>';

        echo '</div>';
        echo '<div class="tc_postcontent_right postbox-container">';
        echo '<div id="submitdiv" class="stuffbox">';
        echo '<h3>'.__('Save', 'teachcourses').'</h3>';
        echo '<div id="minor-publishing"><div id="misc-publishing-actions">';
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
        echo '</div></div></div>';
        echo '<div id="major-publishing-actions"><div id="publishing-action"><input type="submit" name="speichern" id="save_publication_submit" value="Save" class="button-primary" title="'.__('Save', 'teachcourses').'"></div>';
        echo '<div class="clear"></div></div>';
        echo '</div>';
        echo '</div>';
        echo '</form>';    
        echo '<script type="text/javascript" charset="utf-8" src="'. plugins_url( 'js/admin_add_course.js', dirname( __FILE__ ) ).'"></script>';
        echo '</div>';
    }

     
    /**
     * Gets the general box
     * @param int $term_id
     * @param array $course_types
     * @param array $course_data
     * @since 5.0.0
     */
    public static function get_general_box ($term_id, $course_data) {
        $post_type = get_tc_option('rel_page_courses');
        $selected_sem = ( $term_id === 0 ) ? get_tc_option('sem') : 0;
        $semester = get_tc_options('semester', '`setting_id` DESC');
        ?>
        <div class="postbox">
        <h2 class="tc_postbox"><?php _e('General','teachcourses'); ?></h2>
        <div class="inside">

            <p><label for="slug" title="<?php _e('The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.','teachcourses'); ?>"><strong><?php _e('Slug','teachcourses'); ?></strong></label></p>
            <input name="slug" id="slug" type="text" title="<?php _e('The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.','teachcourses'); ?>" tabindex="4" value="<?php echo $course_data["slug"]; ?>" style="width:95%;"/>
            <p><label for="sequence" title="<?php _e('The sequence is used for sorting the courses in the overview.','teachcourses'); ?>"><strong><?php _e('Sequence','teachcourses'); ?></strong></label></p>
            <input name="sequence" id="sequence" type="text" title="<?php _e('The sequence is used for sorting the courses in the overview.','teachcourses'); ?>" tabindex="5" value="<?php echo $course_data["sequence"]; ?>" style="width:95%;"/>
        </div>
    <?php
    }

}
