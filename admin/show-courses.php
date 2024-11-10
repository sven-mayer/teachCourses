<?php
/**
 * This file contains all functions for displaying the show_courses page in admin menu
 * 
 * @package teachcourses\admin\courses
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

 /**
  * This class contains all function for the show courses page
  * @since 5.0.0
  */
 class TC_Courses_Page {

    /**
     * Main controller for the show courses page and all single course pages
     * @since 5.0.0
     */
    public static function init() {
        
        tc_Admin::database_test('<div class="wrap">', '</div>');
        
        // Event Handler
        $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';

        if ( $action === 'edit' ) {
            TC_Add_Course_Page::tc_add_course_page();
        }
        elseif ( $action === 'show' || $action === 'documents' ) {
            tc_show_single_course_page();
        } else {
            TC_Courses_Page::get_tab();
        }

        // add_action("load-$tc_admin_show_courses_page", array(__CLASS__,'tc_show_course_page_help'));
        // add_action("load-$tc_admin_show_courses_page", array(__CLASS__,'tc_show_course_page_screen_options'));
        
    }

    /**
     * Add help tab for show courses page
     */
    public static function tc_show_course_page_help () {
        $screen = get_current_screen();  
        $screen->add_help_tab( array(
            'id'        => 'tc_show_course_help',
            'title'     => __('Display courses','teachcourses'),
            'content'   => '<p><strong>' . __('Shortcodes') . '</strong></p>
                            <p>' . __('You can use courses in a page or article with the following shortcodes:','teachcourses') . '</p>
                            <p>' . __('For course informations','teachcourses') . ': <strong>[tpcourseinfo id="x"]</strong> ' . __('x = Course-ID','teachcourses') . '</p>
                            <p>' . __('For course documents','teachcourses') . ': <strong>[tpcoursedocs id="x"]</strong> ' . __('x = Course-ID','teachcourses') . '</p>
                            <p>' . __('For the course list','teachcourses') . ': <strong>[tpcourselist]</strong></p>
                            <p><strong>' . __('More information','teachcourses') . '</strong></p>
                            <p><a href="https://github.com/winkm89/teachcourses/wiki#shortcodes" target="_blank" title="teachcourses Shortcode Reference (engl.)">teachcourses Shortcode Reference (engl.)</a></p>',
        ) );
    }

    /**
     * Add screen options for show courses page
     * @since 5.0.0
     */
    public static function tc_show_course_page_screen_options() {
        global $tc_admin_show_courses_page;
        $screen = get_current_screen();
    
        if( !is_object($screen) || $screen->id != $tc_admin_show_courses_page ) {
            return;
        }

        $args = array(
            'label' => __('Items per page', 'teachcourses'),
            'default' => 50,
            'option' => 'tc_courses_per_page'
        );
        add_screen_option( 'per_page', $args );
    }
        
    /**
     * Gets the show courses main page
     * @since 5.0.0
     * @access public
     */
    public static function get_tab() {
        global $current_user;
        $terms = TC_Terms::get_terms();
        $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
        $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
        $bulk = isset( $_GET['bulk'] ) ? $_GET['bulk'] : '';
        $copysem = isset( $_GET['copysem'] ) ? $_GET['copysem'] : '';
        $term_id = ( isset($_GET['term_id']) ) ? htmlspecialchars($_GET['term_id']) : 0; //get_tc_option('active_term');
    
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">'.esc_html__('Courses','teachcourses').'</h1><a href="admin.php?page=add_course.php" class="page-title-action">'.esc_html__('Add New Course','teachcourses').'</a>';
        echo '<hr class="wp-header-end">
        <ul class="subsubsub">
            <li class="all"><a href="edit.php?post_type=post" class="current" aria-current="page">All <span class="count">('.TC_Courses_Page::get_count_courses().')</span></a></li>
            <!-- <li class="publish"><a href="edit.php?post_status=publish&amp;post_type=post">Published <span class="count">(1)</span></a></li> -->
        </ul>';
        echo '<form id="showcourse" name="showcourse" method="get" action="'.esc_url($_SERVER['REQUEST_URI']).'">
        <div id="tc_searchbox"> 
            <p class="search-box">';

        if ($search != '') {
            echo '<a href="admin.php?page=teachcourses" class="tc_search_cancel" title="'.esc_html__('Cancel the search','teachcourses').'">X</a>';
        }
        echo '<input type="search" name="search" id="pub_search_field" value="'.stripslashes($search).'"/></td>
            <input type="submit" name="pub_search_button" id="pub_search_button" value="'.esc_html__('Search','teachcourses').'" class="button-secondary"/>
            </p>
        </div>';
        
        echo '<input name="page" type="hidden" value="teachcourses" />';
        // delete a course, part 1
        if ( $bulk === 'delete' ) {
                echo '<div class="teachcourses_message">
                <p class="teachcourses_message_headline">' . __('Do you want to delete the selected items?','teachcourses') . '</p>
                <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachcourses') . '"/>
                <a href="admin.php?page=teachcourses&sem=' . $sem . '&search=' . $search . '" class="button-secondary"> ' . __('Cancel','teachcourses') . '</a></p>
                </div>';
        }
        // delete a course, part 2
        if ( isset($_GET['delete_ok']) ) {
                TC_Courses::delete_courses($current_user->ID, $checkbox);
                $message = __('Removing successful','teachcourses');
                get_tc_message($message);
        }
        // copy a course, part 1
        if ( $bulk === "copy" ) { 
                TC_Courses_Page::get_copy_course_form($terms, $term_id, $search);
        }
        // copy a course, part 2
        if ( isset($_GET['copy_ok']) ) {
                tc_copy_course::init($checkbox, $copysem);
                $message = __('Copying successful','teachcourses');
                get_tc_message($message);
        }
        ?>
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk" id="bulk">
                        <option>- <?php _e('Bulk actions','teachcourses'); ?> -</option>
                        <option value="copy"><?php _e('copy','teachcourses'); ?></option>
                        <option value="delete"><?php _e('Delete','teachcourses'); ?></option>
                    </select>
                    <input type="submit" name="teachcourses_submit" id="doaction" value="<?php _e('apply','teachcourses'); ?>" class="button-secondary"/>
                </div>
                <div class="alignleft actions">
                    <select name="term_id" id="term_id">
                        <option value=""><?php _e('All terms','teachcourses'); ?></option>
                        <?php
                        foreach ($terms as $row) { 
                            $current = ( $row->term_id == $term_id) ? 'selected="selected"' : '';
                            echo '<option value="' . $row->term_id . '" ' . $current . '>' . stripslashes($row->name) . '</option>';
                        } ?> 
                    </select>
                <input type="submit" name="start" value="<?php _e('filter','teachcourses'); ?>" id="teachcourses_submit" class="button-secondary"/>
                </div>
                <div class="tablenav-pages one-page">
                    <span class="displaying-num"><?php echo TC_Courses_Page::get_count_courses(); ?> item</span>
                </div>
            </div>
            <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column"><input name="tc_check_all" id="tc_check_all" type="checkbox" value="" onclick="teachcourses_checkboxes('checkbox[]','tc_check_all');" /></td>
                <th scope="col" id="name" class="manage-column column-name column-primary sortable desc"><a><span><?php _e('Name','teachcourses'); ?></span>
                <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span></a></th>
                <th scope="col" class="manage-column column-id"><?php _e('ID'); ?></th>
                <th scope="col" class="manage-column column-type"><?php _e('Type'); ?></th>
                <th scope="col" class="manage-column column-lecturer"><?php _e('Lecturer','teachcourses'); ?></th>
                <th scope="col" class="manage-column column-term"><?php _e('Term','teachcourses'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $order = 'name, course_id';
            if ($search != '') {
                $order = 'semester DESC, name';	
            }
            TC_Courses_Page::get_courses($search, $term_id, $bulk, $checkbox);

            ?>
            </tbody>
            </table>
        </form>
        </div>
        <?php 
        
    }

    private static function get_count_courses () {
        $row = TC_Courses::get_courses( array('order'     => 'name, course_id'));
        return count($row);
    }
    
    /**
     * Returns the content for the course table
     * @param string $search    The search string
     * @param string $sem       The semester you want to show
     * @param array $bulk       The bulk checkbox
     * @param array $checkbox   The checkbox
     * @return type
     * @since 5.0.0
     * @access private
     */
    private static function get_courses ($search, $sem, $bulk, $checkbox) {

        $row = TC_Courses::get_courses( 
                array(
                    'search'    => $search, 
                    'semester'  => $sem, 
                    'order'     => 'name, course_id'
                ) );
        // if the query is empty
        if ( count($row) === 0 ) { 
            echo '<tr><td colspan="13"><strong>' . __('Sorry, no entries matched your criteria.','teachcourses') . '</strong></td></tr>';
            return;
        }
        

        $static['bulk'] = $bulk;
        $static['sem'] = $sem;
        $static['search'] = $search;
        $z = 0;
        foreach ($row as $row){
            $courses[$z]['course_id'] = $row->course_id;
            $courses[$z]['name'] = stripslashes($row->name);
            $courses[$z]['type'] = stripslashes($row->type);
            $courses[$z]['lecturer'] = stripslashes($row->lecturer);
            $courses[$z]['term_id'] = $row->term_id;
            $courses[$z]['term'] = stripslashes($row->term);
            $courses[$z]['visible'] = $row->visible;
            $z++;
        }
        // display courses
        $class_alternate = true;
        for ($i = 0; $i < $z; $i++) {
            // normal table design
            if ($search == '') {
                // alternate table rows
                $static['tr_class'] = ( $class_alternate === true ) ? ' class="alternate"' : '';
                $class_alternate = ( $class_alternate === true ) ? false : true;
                echo TC_Courses_Page::get_single_table_row($courses[$i], $checkbox, $static);
                
            }
            // table design for searches
            else {
                $static['tr_class'] = '';
                echo TC_Courses_Page::get_single_table_row($courses[$i], $checkbox, $static);
            }
        }	
            
    }
    
    /** 
     * Returns a single table row for show_courses.php
     * @param array $course                     course data
     * @param array $checkbox
     * @param array $static
     * @return string
     * @since 5.0.0
     * @access private
    */ 
    private static function get_single_table_row ($course, $checkbox, $static) {
        $check = '';
        
        // Check if checkbox must be activated or not
        if ( ( $static['bulk'] == "copy" || $static['bulk'] == "delete") && $checkbox != "" ) {
            for( $k = 0; $k < count( $checkbox ); $k++ ) { 
                if ( $course['course_id'] == $checkbox[$k] ) { $check = 'checked="checked"';} 
            }
        }
    
        $class = ' class="tc_course_parent title column-title has-row-actions column-primary page-title"';
        // row actions
        $delete_link = '';
        $edit_link = '';
        $edit_link = '<span class="edit"> | <a href="admin.php?page=teachcourses-add&action=edit&amp;course_id=' . $course['course_id'] . '">' . __('Edit','teachcourses') . '</a></span>';
        $delete_link = '<span class="trash"> | <a class="tc_row_delete" href="admin.php?page=teachcourses&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;checkbox%5B%5D=' . $course['course_id'] . '&amp;bulk=delete" title="' . __('Delete','teachcourses') . '">' . __('Delete','teachcourses') . '</a></span>';
        
        // complete the row
        $return = '<tr' . $static['tr_class'] . '>
            <th scope="row" class="check-column"><input name="checkbox[]" type="checkbox" value="' . $course['course_id'] . '"' . $check . '/></th>
            <td class="title column-title has-row-actions column-primary page-title">
                <a href="admin.php?page=teachcourses&amp;course_id=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=show" class="teachcourses_link" title="' . __('Click to show','teachcourses') . '"><strong>' . $course['name'] . '</strong></a>
                <div class="row-actions">
                    <a href="admin.php?page=teachcourses&amp;course_id=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=show" title="' . __('Show','teachcourses') . '">' . __('Show','teachcourses') . '</a> ' . $edit_link . $delete_link . '
                </div>
            </td>
            <td>' . $course['course_id'] . '</td>
            <td>' . $course['type'] . '</td>
            <td>' . $course['lecturer'] . '</td>
            <td>' . $course['term'] . '</td></tr>';

        // Return
        return $return;
    }
    
    
    /**
     * Gets the form for the course copy function
     * @param object $terms     an object whith all available terms
     * @param string $sem       the current term/semetser
     * @param string $search    the current search string
     * @since 5.0.0
     * @access public
     */
    public static function get_copy_course_form($terms, $sem, $search) {
        ?>
        <div class="teachcourses_message">
            <p class="teachcourses_message_headline"><?php _e('Copy courses','teachcourses'); ?></p>
            <p class="teachcourses_message_text"><?php _e('Select the term, in which you will copy the selected courses.','teachcourses'); ?></p>
            <p class="teachcourses_message_text">
            <select name="copysem" id="copysem">
                <?php
                foreach ($terms as $term) { 
                    $current = ( $term->value == $sem ) ? 'selected="selected"' : '';
                    echo '<option value="' . $term->value . '" ' . $current . '>' . stripslashes($term->value) . '</option>';
                } ?> 
            </select>
            <input name="copy_ok" type="submit" class="button-primary" value="<?php _e('copy','teachcourses'); ?>"/>
            <a href="<?php echo 'admin.php?page=teachcourses&sem=' . $sem . '&search=' . $search . ''; ?>" class="button-secondary"> <?php _e('Cancel','teachcourses'); ?></a>
            </p>
        </div>
        <?php
    }
 }