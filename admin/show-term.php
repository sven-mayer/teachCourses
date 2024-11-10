<?php

class TC_Term_Page {

    /**
     * Main controller for the show courses page and all single course pages
      * @since 0.0.1
     */
    public static function init() {
        
        tc_Admin::database_test('<div class="wrap">', '</div>');
        
        // Event Handler
        $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';
        
        if ( ($action === 'edit') || ($action === 'save')  || ($action === 'create') ) {
            TC_Add_Term_Page::init();
        } else {
            TC_Term_Page::get_tab();
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
      * @since 0.0.1
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
      * @since 0.0.1
     * @access public
     */
    public static function get_tab() {
        global $current_user;
        $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
        $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
        $bulk = isset( $_GET['bulk'] ) ? $_GET['bulk'] : '';
        $copysem = isset( $_GET['copysem'] ) ? $_GET['copysem'] : '';
        $term_id = ( isset($_GET['term_id']) ) ? htmlspecialchars($_GET['term_id']) : get_tc_option('active_term');
    
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">'.esc_html__('Term','teachcourses').'</h1><a href="admin.php?page=teachcourses-term&action=edit" class="page-title-action">'.esc_html__('Add New Term','teachcourses').'</a>';
        echo '<hr class="wp-header-end">
        <ul class="subsubsub">
            <li class="all"><a href="edit.php?post_type=post" class="current" aria-current="page">All <span class="count">('.TC_Term_Page::get_count_terms().')</span></a></li>
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
        
        echo '<input name="page" type="hidden" value="teachcourses.php" />';
        // delete a course, part 1
        if ( $bulk === 'delete' ) {
                echo '<div class="teachcourses_message">
                <p class="teachcourses_message_headline">' . __('Do you want to delete the selected items?','teachcourses') . '</p>
                <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachcourses') . '"/>
                <a href="admin.php?page=teachcourses&term_id=' . $term_id . '&search=' . $search . '" class="button-secondary"> ' . __('Cancel','teachcourses') . '</a></p>
                </div>';
        }
        // delete a course, part 2
        if ( isset($_GET['delete_ok']) ) {
                TC_Term::delete_term($current_user->ID, $checkbox);
                $message = __('Removing successful','teachcourses');
                get_tc_message($message);
        }
        // copy a course, part 1
        if ( $bulk === "copy" ) { 
                TC_Term_Page::get_copy_term_form($terms, $term_id, $search);
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
                <div class="tablenav-pages one-page">
                    <span class="displaying-num"><?php echo TC_Term_Page::get_count_terms(); ?> item</span>
                </div>
            </div>
            <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column"><input name="tc_check_all" id="tc_check_all" type="checkbox" value="" onclick="teachcourses_checkboxes('checkbox[]','tc_check_all');" /></td>
                <th scope="col" id="name" class="manage-column column-name column-primary sortable desc"><a><span><?php _e('Name','teachcourses'); ?></span>
                <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span></a></th>
                <th scope="col" class="manage-column column-slug"><?php _e('Slug','teachcourses'); ?></th>
                <th scope="col" class="manage-column column-order"><?php _e('Order','teachcourses'); ?></th>
                <th scope="col" class="manage-column column-courses"><?php _e('Courses in Term','teachcourses'); ?></th>
                <th scope="col" class="manage-column column-id"><?php _e('ID'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $order = 'name, sequence';
            if ($search != '') {
                $order = 'semester DESC, name';	
            }
            TC_Term_Page::get_terms($search, $term_id, $bulk, $checkbox);

            ?>
            </tbody>
            </table>
        </form>
        </div>
        <?php 
        
    }

    private static function get_count_terms () {
        $row = TC_Terms::get_terms( array('order'     => 'name, term_id'));
        return count($row);
    }
    
    /**
     * Returns the content for the course table
     * @param string $search    The search string
     * @param string term_id       The semester you want to show
     * @param array $bulk       The bulk checkbox
     * @param array $checkbox   The checkbox
     * @return type
      * @since 0.0.1
     * @access private
     */
    private static function get_terms ($search, $term_id, $bulk, $checkbox) {

        $row = TC_Terms::get_terms( 
                array(
                    'search'    => $search, 
                    'order'     => 'sequence, name'
                ) );
        // if the query is empty
        if ( count($row) === 0 ) { 
            echo '<tr><td colspan="13"><strong>' . __('No terms found. ','teachcourses') . '</strong></td></tr>';
            return;
        }
        

        $static['bulk'] = $bulk;
        $static['term_id'] = $term_id;
        $static['search'] = $search;
        $z = 0;
        foreach ($row as $row){
            $courses[$z]['term_id'] = $row->term_id;
            $courses[$z]['name'] = stripslashes($row->name);
            $courses[$z]['slug'] = stripslashes($row->slug);
            $courses[$z]['sequence'] = stripslashes($row->sequence);
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
                echo TC_Term_Page::get_single_table_row($courses[$i], $checkbox, $static);
                
            }
            // table design for searches
            else {
                $static['tr_class'] = '';
                $parent_name = ( $courses[$i]['parent'] != 0 ) ? TC_Terms::get_course_data($courses[$i]['parent'], 'name') : '';
                echo TC_Term_Page::get_single_table_row($courses[$i], $checkbox, $static);
            }
        }	
            
    }
    
    /** 
     * Returns a single table row for show_courses.php
     * @param array $course                     course data
     * @param array $checkbox
     * @param array $static
     * @return string
     * @since 0.0.1
     * @access private
    */ 
    private static function get_single_table_row ($course, $checkbox, $static) {
        $check = '';
        
        // Check if checkbox must be activated or not
        if ( ( $static['bulk'] == "copy" || $static['bulk'] == "delete") && $checkbox != "" ) {
            for( $k = 0; $k < count( $checkbox ); $k++ ) { 
                if ( $course['term_id'] == $checkbox[$k] ) { $check = 'checked="checked"';} 
            }
        }
    
        $class = ' class="title column-title has-row-actions column-primary page-title"';
        // row actions
        $delete_link = '';
        $edit_link = '';

        $courses_in_term = TC_Courses::get_courses(array('term_id' => $course['term_id']));

        if (count($courses_in_term) == 0) {
            $delete_link = '<span class="trash"> | <a class="tc_row_delete" href="admin.php?page=teachcourses&amp;term_id=' . $static['term_id'] . '&amp;search=' . $static['search'] . '&amp;checkbox%5B%5D=' . $course['term_id'] . '&amp;bulk=delete" title="' . __('Delete','teachcourses') . '">' . __('Delete','teachcourses') . '</a></span>';
        } else {
            $delete_link = '';
        }
        // complete the row
        $return = '<tr' . $static['tr_class'] . '>
            <th scope="row" class="check-column"><input name="checkbox[]" type="checkbox" value="' . $course['term_id'] . '"' . $check . '/></th>
            <td class="title column-title has-row-actions column-primary page-title">
                <a href="admin.php?page=teachcourses-term&action=edit&amp;term_id=' . $course['term_id'] . '" class="teachcourses_link" title="' . __('Click to show','teachcourses') . '"><strong>' . $course['name'] . '</strong></a>
                <div class="row-actions">
                    <span class="edit"><a href="admin.php?page=teachcourses-term&action=edit&amp;term_id=' . $course['term_id'] . '">' . __('Edit','teachcourses') . '</a></span>' . $delete_link . '
                </div>
            </td>
            <td>' . $course['slug'] . '</td>
            <td>' . $course['sequence'] . '</td>
            <td>' . count($courses_in_term) . '</td>
            <td>' . $course['term_id'] . '</td></tr>';

        // Return
        return $return;
    }

}

?>