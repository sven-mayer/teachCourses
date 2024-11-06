<?php
/**
 * This file contains all functions for displaying the show_courses page in admin menu
 * 
 * @package teachcorses\admin\courses
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Add help tab for show courses page
 */
function tc_show_course_page_help () {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tc_show_course_help',
        'title'     => __('Display courses','teachcorses'),
        'content'   => '<p><strong>' . __('Shortcodes') . '</strong></p>
                        <p>' . __('You can use courses in a page or article with the following shortcodes:','teachcorses') . '</p>
                        <p>' . __('For course informations','teachcorses') . ': <strong>[tpcourseinfo id="x"]</strong> ' . __('x = Course-ID','teachcorses') . '</p>
                        <p>' . __('For course documents','teachcorses') . ': <strong>[tpcoursedocs id="x"]</strong> ' . __('x = Course-ID','teachcorses') . '</p>
                        <p>' . __('For the course list','teachcorses') . ': <strong>[tpcourselist]</strong></p>
                        <p><strong>' . __('More information','teachcorses') . '</strong></p>
                        <p><a href="https://github.com/winkm89/teachCorses/wiki#shortcodes" target="_blank" title="teachCorses Shortcode Reference (engl.)">teachCorses Shortcode Reference (engl.)</a></p>',
    ) );
}

/**
 * Add screen options for show courses page
 * @since 5.0.0
 */
function tc_show_course_page_screen_options() {
    global $tc_admin_show_courses_page;
    $screen = get_current_screen();
 
    if( !is_object($screen) || $screen->id != $tc_admin_show_courses_page ) {
        return;
    }

    $args = array(
        'label' => __('Items per page', 'teachcorses'),
        'default' => 50,
        'option' => 'tc_courses_per_page'
    );
    add_screen_option( 'per_page', $args );
}

/**
 * Main controller for the show courses page and all single course pages
 * @since 5.0.0
 */
function tc_show_courses_page() {
    
    tc_Admin::database_test('<div class="wrap">', '</div>');
     
    // Event Handler
    $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';

    if ( $action === 'edit' ) {
        tc_add_course_page();
    }
    elseif ( $action === 'show' || $action === 'documents' ) {
        tc_show_single_course_page();
    }
}

/**
 * This class contains all function for the show courses page
 * @since 5.0.0
 */
class tc_Courses_Page {
    
    /**
     * Gets the show courses main page
     * @since 5.0.0
     * @access public
     */
    public static function get_tab() {
        global $current_user;
        $terms = get_tc_options('semester');
        $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
        $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
        $bulk = isset( $_GET['bulk'] ) ? $_GET['bulk'] : '';
        $copysem = isset( $_GET['copysem'] ) ? $_GET['copysem'] : '';
        $sem = ( isset($_GET['sem']) ) ? htmlspecialchars($_GET['sem']) : get_tc_option('sem');
    
        echo '<div class="wrap">';?>
            <h1><?php _e('Courses','teachcorses'); ?> <a href="admin.php?page=teachcorses/add_course.php" class="add-new-h2"><?php _e('Add new','teachcorses'); ?></a></h1>
        <hr class="wp-header-end">
        <ul class="subsubsub">
            <li class="all"><a href="edit.php?post_type=post" class="current" aria-current="page">All <span class="count">(<?php echo tc_Courses_Page::get_count_courses(); ?>)</span></a></li>
            <!-- <li class="publish"><a href="edit.php?post_status=publish&amp;post_type=post">Published <span class="count">(1)</span></a></li> -->
        </ul>
        <form id="showcourse" name="showcourse" method="get" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        
        <div id="tc_searchbox"> 
            <p class="search-box">
            <?php if ($search != '') { ?>
            <a href="admin.php?page=teachcorses.php" class="tc_search_cancel" title="<?php _e('Cancel the search','teachcorses'); ?>">X</a>
            <?php } ?>
            <input type="search" name="search" id="pub_search_field" value="<?php echo stripslashes($search); ?>"/></td>
            <input type="submit" name="pub_search_button" id="pub_search_button" value="<?php _e('Search','teachcorses'); ?>" class="button-secondary"/>
            </p>
        </div>
        
        <input name="page" type="hidden" value="teachcorses.php" />
           <?php 	
           // delete a course, part 1
           if ( $bulk === 'delete' ) {
                echo '<div class="teachcorses_message">
                <p class="teachcorses_message_headline">' . __('Do you want to delete the selected items?','teachcorses') . '</p>
                <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachcorses') . '"/>
                <a href="admin.php?page=teachcorses.php&sem=' . $sem . '&search=' . $search . '" class="button-secondary"> ' . __('Cancel','teachcorses') . '</a></p>
                </div>';
           }
           // delete a course, part 2
           if ( isset($_GET['delete_ok']) ) {
                tc_Courses::delete_courses($current_user->ID, $checkbox);
                $message = __('Removing successful','teachcorses');
                get_tc_message($message);
           }
           // copy a course, part 1
           if ( $bulk === "copy" ) { 
                tc_Courses_Page::get_copy_course_form($terms, $sem, $search);
           }
           // copy a course, part 2
           if ( isset($_GET['copy_ok']) ) {
                tc_copy_course::init($checkbox, $copysem);
                $message = __('Copying successful','teachcorses');
                get_tc_message($message);
           }
           ?>
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk" id="bulk">
                         <option>- <?php _e('Bulk actions','teachcorses'); ?> -</option>
                         <option value="copy"><?php _e('copy','teachcorses'); ?></option>
                         <option value="delete"><?php _e('Delete','teachcorses'); ?></option>
                    </select>
                    <input type="submit" name="teachcorses_submit" id="doaction" value="<?php _e('apply','teachcorses'); ?>" class="button-secondary"/>
                </div>
                <div class="alignleft actions">
                    <select name="sem" id="sem">
                         <option value=""><?php _e('All terms','teachcorses'); ?></option>
                         <?php
                         foreach ($terms as $row) { 
                              $current = ( $row->value == $sem ) ? 'selected="selected"' : '';
                              echo '<option value="' . $row->value . '" ' . $current . '>' . stripslashes($row->value) . '</option>';
                         } ?> 
                    </select>
                   <input type="submit" name="start" value="<?php _e('filter','teachcorses'); ?>" id="teachcorses_submit" class="button-secondary"/>
                </div>
                <div class="tablenav-pages one-page">
                    <span class="displaying-num"><?php echo tc_Courses_Page::get_count_courses(); ?> item</span>
                </div>
             </div>
            <table class="wp-list-table widefat fixed striped table-view-list">
               <thead>
               <tr>
                   <td id="cb" class="manage-column column-cb check-column"><input name="tc_check_all" id="tc_check_all" type="checkbox" value="" onclick="teachcorses_checkboxes('checkbox[]','tc_check_all');" /></td>
                   <th scope="col" id="name" class="manage-column column-name column-primary sortable desc"><a><span><?php _e('Name','teachcorses'); ?></span>
                   <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span></a></th>
                   <th scope="col" class="manage-column column-id"><?php _e('ID'); ?></th>
                   <th scope="col" class="manage-column column-type"><?php _e('Type'); ?></th>
                   <th scope="col" class="manage-column column-lecturer"><?php _e('Lecturer','teachcorses'); ?></th>
                   <th scope="col" class="manage-column column-term"><?php _e('Term','teachcorses'); ?></th>
               </tr>
               </thead>
               <tbody>
            <?php
               $order = 'name, course_id';
               if ($search != '') {
                   $order = 'semester DESC, name';	
               }
               tc_Courses_Page::get_courses($current_user->ID, $search, $sem, $bulk, $checkbox);
  
            ?>
            </tbody>
            </table>
        </form>
        </div>
        <?php 
        
    }

    private static function get_count_courses () {
        $row = tc_Courses::get_courses( array('order'     => 'name, course_id'));
        return count($row);
    }
    
    /**
     * Returns the content for the course table
     * @param int $user_ID      The ID of the current user
     * @param string $search    The search string
     * @param string $sem       The semester you want to show
     * @param array $bulk       The bulk checkbox
     * @param array $checkbox   The checkbox
     * @return type
     * @since 5.0.0
     * @access private
     */
    private static function get_courses ($user_ID, $search, $sem, $bulk, $checkbox) {
        $row = tc_Courses::get_courses( 
                array(
                    'search'    => $search, 
                    'semester'  => $sem, 
                    'order'     => 'name, course_id'
                ) );
        // if the query is empty
        if ( count($row) === 0 ) { 
            echo '<tr><td colspan="13"><strong>' . __('Sorry, no entries matched your criteria.','teachcorses') . '</strong></td></tr>';
            return;
        }
           

        $static['bulk'] = $bulk;
        $static['sem'] = $sem;
        $static['search'] = $search;
        $z = 0;
        foreach ($row as $row){
            $date1 = tc_datesplit($row->start);
            $date2 = tc_datesplit($row->end);
            $courses[$z]['course_id'] = $row->course_id;
            $courses[$z]['name'] = stripslashes($row->name);
            $courses[$z]['type'] = stripslashes($row->type);
            // $courses[$z]['room'] = stripslashes($row->room);
            $courses[$z]['lecturer'] = stripslashes($row->lecturer);
            // $courses[$z]['date'] = stripslashes($row->date);
            // $courses[$z]['start'] = '' . $date1[0][0] . '-' . $date1[0][1] . '-' . $date1[0][2] . '';
            // $courses[$z]['end'] = '' . $date2[0][0] . '-' . $date2[0][1] . '-' . $date2[0][2] . '';
            $courses[$z]['semester'] = stripslashes($row->semester);
            $courses[$z]['visible'] = $row->visible;
            // $courses[$z]['use_capabilities'] = $row->use_capabilities;
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
                echo tc_Courses_Page::get_single_table_row($courses[$i], $user_ID, $checkbox, $static);
               	
            }
            // table design for searches
            else {
                $static['tr_class'] = '';
                $parent_name = ( $courses[$i]['parent'] != 0 ) ? tc_Courses::get_course_data($courses[$i]['parent'], 'name') : '';
                echo tc_Courses_Page::get_single_table_row($courses[$i], $user_ID, $checkbox, $static);
            }
        }	
             
    }
    
    /** 
     * Returns a single table row for show_courses.php
     * @param array $course                     course data
     * @param array $user_ID                    The ID of the user
     * @param array $checkbox
     * @param array $static
           $static['bulk']                      copy or delete
           $static['sem']                       semester
           $static['search']                    input from search field
     * @return string
     * @since 5.0.0
     * @access private
    */ 
    private static function get_single_table_row ($course, $user_ID, $checkbox, $static) {
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
        $edit_link = '<span class="edit"> | <a href="admin.php?page=teachcorses.php&amp;course_id=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=edit&amp;ref=overview" title="' . __('Edit','teachcorses') . '">' . __('Edit','teachcorses') . '</a></span>';
        $delete_link = '<span class="trash"> | <a class="tc_row_delete" href="admin.php?page=teachcorses.php&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;checkbox%5B%5D=' . $course['course_id'] . '&amp;bulk=delete" title="' . __('Delete','teachcorses') . '">' . __('Delete','teachcorses') . '</a></span>';
        
        // complete the row
        $return = '<tr' . $static['tr_class'] . '>
            <th scope="row" class="check-column"><input name="checkbox[]" type="checkbox" value="' . $course['course_id'] . '"' . $check . '/></th>
            <td class="title column-title has-row-actions column-primary page-title">
                <a href="admin.php?page=teachcorses.php&amp;course_id=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=show" class="teachcorses_link" title="' . __('Click to show','teachcorses') . '"><strong>' . $course['name'] . '</strong></a>
                <div class="row-actions">
                    <a href="admin.php?page=teachcorses.php&amp;course_id=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=show" title="' . __('Show','teachcorses') . '">' . __('Show','teachcorses') . '</a> ' . $edit_link . $delete_link . '
                </div>
            </td>
            <td>' . $course['course_id'] . '</td>
            <td>' . $course['type'] . '</td>
            <td>' . $course['lecturer'] . '</td>
            <td>' . $course['semester'] . '</td></tr>';

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
        <div class="teachcorses_message">
            <p class="teachcorses_message_headline"><?php _e('Copy courses','teachcorses'); ?></p>
            <p class="teachcorses_message_text"><?php _e('Select the term, in which you will copy the selected courses.','teachcorses'); ?></p>
            <p class="teachcorses_message_text">
            <select name="copysem" id="copysem">
                <?php
                foreach ($terms as $term) { 
                    $current = ( $term->value == $sem ) ? 'selected="selected"' : '';
                    echo '<option value="' . $term->value . '" ' . $current . '>' . stripslashes($term->value) . '</option>';
                } ?> 
            </select>
            <input name="copy_ok" type="submit" class="button-primary" value="<?php _e('copy','teachcorses'); ?>"/>
            <a href="<?php echo 'admin.php?page=teachcorses.php&sem=' . $sem . '&search=' . $search . ''; ?>" class="button-secondary"> <?php _e('Cancel','teachcorses'); ?></a>
            </p>
        </div>
        <?php
    }
}