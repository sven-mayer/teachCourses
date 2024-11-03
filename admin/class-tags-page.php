<?php
/**
 * This class contains all functions for the tags page in the admin menu
 * @since 5.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */
class tc_Tags_Page {
    
    public static function init () {

        echo '<div class="wrap" style="max-width:900px;">';
        echo '<h2>' . __('Tags') . '</h2>';
        echo '<form id="form1" name="form1" method="get" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        echo '<input name="page" type="hidden" value="teachcorses/tags.php" />';

        tc_Tags_Page::get_page();

        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Handle page actions
     * @param string $action
     * @param array $checkbox
     * @param string $page
     * @param sting $search
     * @param int $curr_page
     * @since 8.1
     */
    private static function actions ($action, $checkbox, $page, $search, $curr_page) {
        
        // Delete tags - part 1
        if ( $action === 'delete' ) {
            echo '<div class="teachcorses_message teachcorses_message_orange">
                <p class="teachcorses_message_headline">' . __('Do you want to delete the selected items?','teachcorses') . '</p>
                <p><input name="delete_ok" type="submit" class="button-secondary" value="' . __('Delete','teachcorses') . '"/>
                <a href="admin.php?page=' . $page . '&search=' . $search . '&amp;limit=' . $curr_page . '"> ' . __('Cancel','teachcorses') . '</a></p>
                </div>';
        }
        
        // delete tags - part 2
        if ( isset($_GET['delete_ok']) ) {
            tc_Tags::delete_tags($checkbox);
            get_tc_message( __('Removing successful','teachcorses') );
        }
        if ( isset( $_GET['tc_edit_tag_submit'] )) {
            $name = htmlspecialchars($_GET['tc_edit_tag_name']);
            $tag_id = intval($_GET['tc_edit_tag_id']);
            tc_Tags::edit_tag($tag_id, $name);
            get_tc_message( __('Tag saved','teachcorses') );
        }
    }


    /**
     * Prints the page
     * @param string $search
     * @param int $entry_limit
     * @param int $number_messages
     * @param array $checkbox
     * @param string $action
     * @param int $page
     * @param int $curr_page
     * @since 6.0.0
     */
    public static function get_page () {
        /**
         * Form data
         */
        // Get screen options
        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $screen_option, true);
        
        $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
        $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : array();
        $filter = isset( $_GET['filter'] ) ? htmlspecialchars($_GET['filter']) : '';
        $only_zero = ( $filter === 'only_zero' ) ? true : false;
        $page = 'teachcorses/tags.php';
        if ( empty ( $per_page) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }
        $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';
        
        // Handle limits
        $number_messages = $per_page;
        if (isset($_GET['limit'])) {
            $curr_page = (int)$_GET['limit'] ;
            if ( $curr_page <= 0 ) {
                $curr_page = 1;
            }
            $entry_limit = ( $curr_page - 1 ) * $number_messages;
        }
        else {
            $entry_limit = 0;
            $curr_page = 1;
        }
        
        // Actions
        self::actions($action, $checkbox, $page, $search, $curr_page);
        
        // Page Menu
        $test = tc_Tags::get_tags_occurence( array( 
                    'count'         => true, 
                    'search'        => $search, 
                    'only_zero'     => $only_zero
        ));
        
        // Search box
        tc_HTML::line('<div id="tc_searchbox">');
        if ( $search != "" ) { 
            tc_HTML::line('<a href="admin.php?page=teachcorses/tags.php" class="tc_search_cancel" title="' . __('Cancel the search','teachcorses') . '">X</a>');
        }
        tc_HTML::line('<input type="search" name="search" id="pub_search_field" value="' . stripslashes($search) . '"/>');
        tc_HTML::line('<input type="submit" name="button" id="button" value="' . __('Search','teachcorses') . '" class="button-secondary"/>');
        tc_HTML::line('</div>');
        
        // Table actions
        tc_HTML::line('<div class="tablenav" style="padding-bottom:5px;">');
        tc_HTML::line('<div class="alignleft actions">');
        
        tc_HTML::line('<select name="action">');
        tc_HTML::line('<option value="">- ' . __('Bulk actions','teachcorses') . '-</option>');
        tc_HTML::line('<option value="delete">'  . __('Delete','teachcorses') . '</option>');
        tc_HTML::line('</select>');
        
        tc_HTML::line('<select name="filter">');
        tc_HTML::line('<option>- ' . __('Select filter','teachcorses') . ' -</option>');
        $selected = ( $only_zero === true ) ? 'selected="selected"' : '';
        tc_HTML::line('<option value="only_zero"' . $selected . '>' . __('Occurence = 0','teachcorses') . '</option>');
        tc_HTML::line('</select>');
        
        tc_HTML::line('<input name="OK" value="OK" type="submit" class="button-secondary"/>');
        tc_HTML::div_close('alignleft actions');
        
        // Page nav
        $args = array('number_entries'  => $test,
                  'entries_per_page'    => $number_messages,
                  'current_page'        => $curr_page,
                  'entry_limit'         => $entry_limit,
                  'page_link'           => "admin.php?page=$page&amp;",
                  'link_attributes'     => "search=$search");
        echo tc_page_menu($args);
        
        tc_HTML::div_close('tablenav');
        
        // Table
        tc_HTML::line('<table border="0" cellspacing="0" cellpadding="0" class="widefat">');
        tc_HTML::line('<thead>');
        tc_HTML::line('<tr>');
        $onclick = "teachcorses_checkboxes('checkbox[]','tc_check_all');";
        tc_HTML::line('<td class="check-column"><input name="tc_check_all" id="tc_check_all" type="checkbox" value="" onclick="' . $onclick . '" /></td>');
        tc_HTML::line('<th>' . __('Name','teachcorses') . '</th>');
        tc_HTML::line('<th>' . __('ID') . '</th>');
        tc_HTML::line('<th>' . __('Number','teachcorses') . '</th>');
        tc_HTML::line('</tr>');
        tc_HTML::line('</thead>');
        
        if ( $test === 0 ) {
            tc_HTML::line('<tr><td colspan="4"><strong>' . __('Sorry, no entries matched your criteria.','teachcorses') . '</strong></td></tr>');
        }
        else {
            $link = 'admin.php?page=' . $page . '&amp;search=' . $search . '&amp;limit=' . $curr_page . '&amp;action=delete&amp;filter=' . $filter;
            $results = tc_Tags::get_tags_occurence( array(
                    'search'        => $search,
                    'limit'         => $entry_limit . ',' . $number_messages,
                    'order'         => 't.name ASC',
                    'only_zero'     => $only_zero,
            ) );
            tc_Tags_Page::get_table($results, $action, $checkbox, $link);
        } 

        tc_HTML::line('</table>');
        // END Table
  
        tc_HTML::div_open('tablenav bottom');
        tc_HTML::line('<div class="tablenav-pages" style="float:right;">');
        
        if ( $test > $number_messages ) {
            $args = array('number_entries'  => $test,
                      'entries_per_page'    => $number_messages,
                      'current_page'        => $curr_page,
                      'entry_limit'         => $entry_limit,
                      'page_link'           => "admin.php?page=$page&amp;",
                      'link_attributes'     => "search=$search",
                      'mode'                => 'bottom');
            echo tc_page_menu($args);
        } 
        else {
            if ($test === 1) {
               echo $test . ' ' . __('entry','teachcorses');
            }
            else {
               echo $test . ' ' . __('entries','teachcorses');
            }
        }
        tc_HTML::div_close('tablenav-pages');
        tc_HTML::div_close('tablenav bottom');
    }
    
    /**
     * Prints a single table row for the table
     * @param array $results
     * @param string action
     * @param array $checkbox
     * @param string link
     * @since 6.0.0
     */
    private static function get_table ($results, $action, $checkbox, $link) {
        $class_alternate = true;
        
        foreach ($results as $row) {
            // Alternate line style
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            
            tc_HTML::line('<tr ' . $tr_class . '>');
            $checked = '';
            if ( $action === "delete") { 
                $checked = in_array($row['tag_id'], $checkbox ) ? 'checked="checked"' : '';
            }
            tc_HTML::line('<th class="check-column"><input name="checkbox[]" class="tc_checkbox" ' . $checked . ' type="checkbox" value="' . $row['tag_id'] . '"></th>');
            tc_HTML::line('<td id="tc_tag_row_' . $row['tag_id'] . '">');
            tc_HTML::line( '<a onclick="teachcorses_editTags(' . "'" . $row['tag_id'] . "'" . ')" class="teachcorses_link" title="' . __('Click to edit','teachcorses') . '" style="cursor:pointer;"><strong>' . stripslashes($row['name']) . '</strong></a><input type="hidden" id="tc_tag_row_name_' . $row['tag_id'] . '" value="' . stripslashes($row['name']) . '"/>');
            
            // Row actions
            tc_HTML::line( '<div class="tc_row_actions">');
            tc_HTML::line( '<a onclick="teachcorses_editTags(' . "'" . $row['tag_id'] . "'" . ')" class="teachcorses_link" title="' . __('Click to edit','teachcorses') . '" style="cursor:pointer;">' . __('Edit', 'teachcorses') . '</a> | <a href="admin.php?page=publications.php&amp;tag=' . $row['tag_id'] . '" title="' . __('Show all publications which have a relationship to this tag','teachcorses') . '">' . __('Publications','teachcorses') . '</a> | <a class="tc_row_delete" href="' . $link . '&amp;checkbox%5B%5D=' . $row['tag_id'] . '" title="' . __('Delete','teachcorses') . '">' . __('Delete', 'teachcorses') . '</a>');
            tc_HTML::line('</div>');
            // END Row actions
            
            tc_HTML::line('</td>');
            tc_HTML::line('<td>' . $row['tag_id'] . '</td>');
            tc_HTML::line('<td>' . $row['count'] . '</td>');
            tc_HTML::line('</tr>');
            
        }
    }
    
    /**
     * Adds the screen options
     * @global string $tc_admin_edit_tags_page
     * @since 8.1
     */
    public static function add_screen_options () {
        global $tc_admin_edit_tags_page;
        $screen = get_current_screen();

        if( !is_object($screen) || $screen->id != $tc_admin_edit_tags_page ) {
            return;
        }

        $args = array(
            'label' => __('Items per page', 'teachcorses'),
            'default' => 50,
            'option' => 'tc_tags_per_page'
        );
        add_screen_option( 'per_page', $args );
    }
    
}