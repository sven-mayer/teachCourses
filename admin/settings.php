<?php
/**
 * This file contains all functions for displaying the settings page in admin menu
 * 
 * @package teachcorses\admin\settings
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * teachCorses settings menu: controller
 * @since 5.0.0
 */
function tc_show_admin_settings() {
    tc_Settings_Page::load_page();   
}

/**
 * This class contains all functions for the teachcorses settings page
 * @since 5.0.0
 */
class tc_Settings_Page {
    
    /**
     * Generates the settings page
     * @since 5.0.0
     */
    public static function load_page (){
        echo '<div class="wrap">';

        $site = 'options-general.php?page=teachcorses/settings.php';
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

        // update dababase
        if ( isset($_GET['up']) ) {
            tc_Settings_Page::update_database($site);
        }

        // sync database
        if ( isset($_GET['sync']) ) {
            $sync = intval($_GET['sync']);
            if ( $sync === 1 ) {
                tc_db_sync('authors');
                tc_Settings_Page::update_database($site, false);
            }
            if ( $sync === 2 ) {
                tc_db_sync('stud_meta');
            }
        }

        // install database
        if ( isset($_GET['ins']) ) {
            tc_install();
        }
        
        // delete database
        if ( isset( $_GET['drop_tp'] ) || isset( $_GET['drop_tc_ok'] ) ) {
            tc_Settings_Page::delete_database();
        }

        // change general options
        if (isset( $_POST['einstellungen'] )) {
            tc_Settings_Page::change_general_options();
        }

        // change publication options
        if ( isset($_POST['save_pub']) ) {
            tc_Settings_Page::change_publication_options();
        }

        // delete settings
        if ( isset( $_GET['delete'] ) ) {
            tc_Options::delete_option($_GET['delete']);
            get_tc_message(__('Deleted', 'teachcorses'));
        }
        
        // Delete data field
        if ( isset( $_GET['delete_field'] ) || isset( $_GET['delete_field_ok'] ) ) {
            tc_Settings_Page::delete_meta_fields($tab);
        }

        // add course options
        if ( isset( $_POST['add_term'] ) || isset( $_POST['add_type'] ) ) {
            tc_Settings_Page::add_course_options();
        }

        // add meta field options
        if ( isset($_POST['add_field']) ) {
            if ( $tab === 'course_data' ) {
                $table = 'teachcorses_courses';
            }
            elseif ( $tab === 'publication_data' ) {
                $table = 'teachcorses_pub';
            }
            else {
                $table = 'teachcorses_stud';
            }
            tc_Settings_Page::add_meta_fields($table);
        }

        // test if database is installed
        tc_Admin::database_test();

        echo '<h2 style="padding-bottom:0px;">' . __('teachCorses settings','teachcorses') . '</h2>';

        // Site menu
        $set_menu_1 = ( $tab === 'general' || $tab === '' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_3 = ( $tab === 'term' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_4 = ( $tab === 'type' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_5 = ( $tab === 'course_data' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_99 = ( $tab === 'db_status' ) ? 'nav-tab nav-tab-active' : 'nav-tab';

        echo '<h3 class="nav-tab-wrapper">'; 
        echo '<a href="' . $site . '&amp;tab=general" class="' . $set_menu_1 . '">' . __('General','teachcorses') . '</a>';
        echo '<a href="' . $site . '&amp;tab=term" class="' . $set_menu_3 . '">' . __('Term','teachcorses') . '</a>';
        echo '<a href="' . $site . '&amp;tab=type" class="' . $set_menu_4 . '">' . __('Type','teachcorses') . '</a>';
        echo '<a href="' . $site . '&amp;tab=course_data" class="' . $set_menu_5 . '">' . __('Meta','teachcorses') .'</a>';
        echo '<a href="' . $site . '&amp;tab=db_status" class="' . $set_menu_99 . '">' . __('Database','teachcorses') .' '. __('Index status','teachcorses') . '</a>';
       
        echo '</h3>';

        echo '<form id="form1" name="form1" method="post" action="' . $site . '&amp;tab=' . $tab . '">';
        echo '<input name="page" type="hidden" value="teachcorses/settings.php" />';
        echo '<input name="tab" type="hidden" value="<?php echo $tab; ?>" />';

        /* General */
        if ($tab === '' || $tab === 'general') {
            self::get_general_tab($site);
        }
        /* Term */
        if ( $tab === 'term' ) { 
            self::get_term_tab();
        }
        /* Type */
        if ( $tab === 'type' ) { 
            self::get_type_tab();
        }
        /* Meta data */
        if ( $tab === 'course_data' || $tab === 'publication_data' ) {
            self::get_meta_tab($tab);
        }
        /* DB Status Tab */
        if ( $tab === 'db_status' ) {
            self::get_db_status_tab();
        }

        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Gets the about dialog for the general tab
     * @since 5.0.0
     * @access private
     */
    private static function get_about_dialog () {
        // img source: https://unsplash.com/photos/uG1jwfpCRhg
        echo '<div id="dialog" title="About">
                <div style="text-align: center;">
                <p><img src="' . plugins_url( 'images/misc/about.jpg', dirname( __FILE__ ) ) . '" style="border-radius: 130px; width: 250px; height: 250px;" title="Photo by Ella Olsson on Unsplash" /></p>
                <p><img src="' . plugins_url( 'images/full.png', dirname( __FILE__ ) ) . '" width="400" /></p>
                <p style="font-size: 20px; font-weight: bold; color: #e6005c;">' . get_tc_option('db-version') . ' "Raspberry Brownie"</p>
                <p><a href="http://mtrv.wordpress.com/teachcorses/">Website</a> | <a href="https://github.com/winkm89/teachCorses/">teachCorses on GitHub</a> | <a href="https://github.com/winkm89/teachCorses/wiki">Dokumentation</a> | <a href="https://github.com/winkm89/teachCorses/wiki/Changelog">Changelog</a></p>
                <p>&copy;2008-2022 by Michael Winkler | License: GPLv2 or later<br/></p>
                </div>
              </div>';
    }

    /**
     * Returns the select form for rel_page option
     * @param string $type  rel_page_publications or rel_page_courses
     * @access private
     * @since 5.0.0
     */
    private static function get_rel_page_form ($type) {
        $title = ( $type === 'rel_page_publications' ) ? __('For publications','teachcorses') : __('For courses','teachcorses');
        $value = get_tc_option($type);
        echo '<p><select name="' . $type . '" id="' . $type . '" title="' . $title . '">';
        
        echo '<option value="page" ';
        if ($value == 'page') { echo 'selected="selected"'; }
        echo '>' . __('Pages') . '</option>';
        
        echo '<option value="post" ';
        if ($value == 'post') { echo 'selected="selected"'; }
        echo '>' . __('Posts') . '</option>';

        $post_types = get_post_types( array('public' => true, '_builtin' => false ), 'objects' ); 
        foreach ($post_types as $post_type ) {
            $current = ($post_type->name == $value) ? 'selected="selected"' : '';
            echo '<option value="'. $post_type->name . '" ' . $current . '>'. $post_type->label. '</option>';
        }
        echo '</select> ';
        echo '<label for="' . $type . '">' . $title. '</label></p>';
    }
    
    /**
     * Returns the select for for user role field
     * @param string $type
     * @access private
     * @since 5.0.0
     */
    private static function get_user_role_form ($type){
        $title = ( $type === 'userrole_publications' ) ? __('Backend access for publication module','teachcorses') : __('Backend access for course module','teachcorses');
        $cap = ( $type === 'userrole_publications' ) ? 'use_teachcorses' : 'use_teachcorses_courses';
        
        echo '<tr>';
        echo '<th><label for="' . $type . '">' . $title . '</label></th>';
        echo '<td style="vertical-align: top;">';
        echo '<select name="' . $type . '[]" id="' . $type . '" multiple="multiple" style="height:120px; width: 220px;" title="' . $title . '">';
        
        global $wp_roles;
        foreach ($wp_roles->role_names as $roledex => $rolename){
           $role = $wp_roles->get_role($roledex);
           $select = $role->has_cap($cap) ? 'selected="selected"' : '';
           echo '<option value="'.$roledex.'" '.$select.'>'.$rolename.'</option>';
        }
        
        echo '</select>';
        echo '</td>';
        echo '<td style="vertical-align: top;">' . __('Select which userrole your users must have to use the teachCorses backend.','teachcorses') . '<br />' . __('use &lt;Ctrl&gt; key to select multiple roles','teachcorses') . '</td>';        
        echo '</tr>';
    }
    
    /**
     * Shows the type settings tab
     * @access private
     * @since 1.0.0
     */
    private static function get_type_tab() {
        echo '<div style="width:100%;">';

        $args3 = array ( 
            'element_title' => __('Type'),
            'count_title' => __('Number of courses','teachcorses'),
            'delete_title' => __('Delete type','teachcorses'),
            'add_title' => __('Add type','teachcorses'),
            'tab' => 'type'
            );
        tc_Admin::get_course_option_box(__('Types of courses','teachcorses'), 'type', $args3);

        echo '</div>';
    }

    /**
     * Shows the term settings tab
     * @access private
     * @since 1.0.0
     */
    private static function get_term_tab() {
        echo '<div style="width:100%;">';

        $args2 = array ( 
            'element_title' => __('Term','teachcorses'),
            'count_title' => __('Number of courses','teachcorses'),
            'delete_title' => __('Delete term','teachcorses'),
            'add_title' => __('Add term','teachcorses'),
            'tab' => 'term'
            );
        tc_Admin::get_course_option_box(__('Term','teachcorses'), 'term', $args2);

        echo '</div>';
    }

    /**
     * Shows the tab for general options
     * @param sting $site
     * @access private
     * @since 5.0.0
     */
    private static function get_general_tab($site) {

        echo '<table class="form-table">';
        echo '<thead>';

        // Version
        echo '<tr>';
        echo '<th width="160">' . __('teachCorses version','teachcorses') . '</th>';
        echo '<td width="250"><a id="tc_open_readme" class="tc_open_readme">' . get_tc_option('db-version') . '</a></td>';
        echo '<td></td>';
        echo '</td>';
        echo '</tr>';

        // Related content
        echo '<tr>';
        echo '<th>' . __('Related content','teachcorses') . '</th>';
        echo '<td>';
        tc_Settings_Page::get_rel_page_form('rel_page_courses');
        echo '</td>';
        echo '<td style="vertical-align: top;">' . __('If you create a course you can define a link to related content. It is kind of a "more information link", which helps you to connect a course with a page. If you want to use custom post types instead of pages, so you can set it here.','teachcorses') . '</td>';
        echo '</tr>';

        // Frontend styles
        echo '<tr>';
        echo '<th><label for="stylesheet">' . __('Frontend styles','teachcorses') . '</label></th>';
        echo '<td style="vertical-align: top;">';
        echo '<select name="stylesheet" id="stylesheet" title="' . __('Frontend styles','teachcorses') . '">';

        $value = get_tc_option('stylesheet');
        if ($value == '1') {
            echo '<option value="1" selected="selected">' . __('teachcorses_front.css','teachcorses') . '</option>';
            echo '<option value="0">' . __('your theme.css','teachcorses') . '</option>';
        }
        else {
            echo '<option value="1">' . __('teachcorses_front.css','teachcorses') . '</option>';
            echo '<option value="0" selected="selected">' . __('your theme.css','teachcorses') . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td>' . __('Select which style sheet you will use. teachcorses_front.css is the teachCorses default style. If you have created your own style in the default style sheet of your theme, you can activate this here.','teachcorses') . '</td>';
        echo '</tr>';

        // User roles
        tc_Settings_Page::get_user_role_form('userrole_courses');
        
        echo '<tr>';
        echo '<th colspan="3"><h3>' . __('Misc','teachcorses') . '</h3></th>';
        echo '</tr>';
    
        echo '<tr>';
        echo '<th>' . __('Uninstalling','teachcorses') . '</th>';
        echo '<td>';
        echo '<a class="tc_row_delete" href="options-general.php?page=teachcorses/settings.php&amp;tab=general&amp;drop_tp=1">' . __('Remove teachCorses from database','teachcorses') . '</a>';
        echo '</td>';
        echo '</tr>';
        
        echo '</thead>';
        echo '</table>';
        
        echo '<p><input name="einstellungen" type="submit" id="teachcorses_settings" value="' . __('Save') . '" class="button-primary" /></p>';
        
        echo '<script type="text/javascript" src="' . plugins_url( 'js/admin_settings.js', dirname( __FILE__ ) ) . '"></script>';
        self::get_about_dialog();
    }
    
    /**
     * Shows the student settings tab
     * @param string $tab course_data
     * @access private
     * @since 5.0.0
     */
    private static function get_meta_tab($tab) {
        // Select right table name
        $table = 'teachcorses_courses';

        $select_fields = array();

        echo '<div style="width:100%;">';
        echo '<h3>' . __('Meta data fields','teachcorses') . '</h3>';

        echo '<table class="widefat">';
        
        // Table Head
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Field name','teachcorses') . '</th>';
        echo '<th>' . __('Properties','teachcorses') . '</th>';
        echo '</tr>';
        echo '</thead>';
        
        // Table Body
        echo '<tbody>';

        // Default fields
        $class_alternate = true;
        $fields = get_tc_options($table,'`variable` ASC');
        foreach ($fields as $field) {
            $data = tc_DB_Helpers::extract_column_data($field->value);
            if ( $data['type'] === 'SELECT' || $data['type'] === 'CHECKBOX' || $data['type'] === 'RADIO' ) {
                array_push($select_fields, $field->variable);
                // search for select options and add it
                if ( isset( $_POST['add_' . $field->variable] ) && $_POST['new_' . $field->variable] != __('Add element','teachcorses') ) {
                    tc_Options::add_option($_POST['new_' . $field->variable], $_POST['new_' . $field->variable], $field->variable);
                }
            }
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            echo '<tr ' . $tr_class . '>
                <td>' . $field->variable . '
                    <div class="tc_row_actions">
                    <a class="tc_edit_meta_field" title="' . __('Click to edit','teachcorses') . '" href="' . admin_url( 'admin-ajax.php' ) . '?action=teachcorses&meta_field_id=' . $field->setting_id . '">' . __('Edit','teachcorses') . '</a> | <a class="tc_row_delete" title="' . __('Delete','teachcorses') . '" href="options-general.php?page=teachcorses/settings.php&amp;delete_field=' . $field->setting_id . '&amp;tab=' . $tab . '">' . __('Delete','teachcorses') . '</a>
                    </div>
                </td>
                <td>';
            if ( isset( $data['title'] ) ) {
                echo 'Label: <b>' . stripslashes($data['title']) . '</b><br/>'; }
            if ( isset( $data['type'] ) ) {
                echo 'Type: <b>' . stripslashes($data['type']) . '</b><br/>';}
            if ( isset( $data['visibility'] ) ) {
                echo 'Visibility: <b>' . stripslashes($data['visibility']) . '</b><br/>'; }
            if ( isset( $data['min'] ) ) {
                echo 'Min: <b>' . stripslashes($data['min']) . '</b><br/>'; }
            if ( isset( $data['max'] ) ) {
                echo 'Max: <b>' . stripslashes($data['max']) . '</b><br/>'; }
            if ( isset( $data['step'] ) ) {
                echo 'Step: <b>' . stripslashes($data['step']) . '</b><br/>'; }
            if ( isset( $data['required'] ) ) {
                echo 'Required: <b>' . stripslashes($data['required']) . '</b>'; }
            echo '</td>';
            echo '</tr>';
        }
        // Table Footer
        echo '</tbody>';
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="2">';
        echo '<a class="tc_edit_meta_field button-primary" title="' . __('Add new','teachcorses') . '" href="' . admin_url( 'admin-ajax.php' ) . '?action=teachcorses&meta_field_id=0">' . __('Add new','teachcorses') . '</a>';
        echo '</td>';
        echo '</tr>';
        echo '</tfoot>';
        echo '</table>';

        echo '</div>';
        echo '<div style="width:48%; float:left; padding-left:2%;">';

        foreach ( $select_fields as $elem ) {
            $args1 = array ( 
                 'element_title' => __('Name','teachcorses'),
                 'count_title' => __('Number of students','teachcorses'),
                 'delete_title' => __('Delete elemtent','teachcorses'),
                 'add_title' => __('Add element','teachcorses'),
                 'tab' => $tab
                 );
             tc_Admin::get_course_option_box($elem, $elem, $args1);
        }

        ?>
        <script type="text/javascript" charset="utf-8">
            jQuery(document).ready(function($){
                $(".tc_edit_meta_field").each(function() {
                    var $link = $(this);
                    var $dialog = $('<div></div>')
                        .load($link.attr('href') + ' #content')
                        .dialog({
                                autoOpen: false,
                                title: '<?php _e('Meta Field Settings','teachcorses'); ?>',
                                width: 600
                        });

                    $link.click(function() {
                        $dialog.dialog('open');
                        return false;
                    });
                });
            });
        </script>
        <?php
    }
    
    /**
     * Creates the list of publication templates
     * @return string
     * @access private
     * @since 6.0.0
     */
    private static function list_templates () {
        $templates = tc_detect_templates();
        $s = '';
        $class_alternate = true;
        foreach ($templates as $key => $value) {
            // alternate row style
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            
            // load template
            include_once $templates[$key];
            $template = new $key();
            $settings = tc_HTML_Publication_Template::load_settings($template);
            
            $s .= '<tr ' . $tr_class . '>';
            $s .= '<td>' . esc_html($settings['name']) . '</td>';
            $s .= '<td>' . esc_html($key) . '</td>';
            $s .= '<td>' . esc_html($settings['description']) . '
                       <p>' . __('Version', 'teachcorses') . ' ' . esc_html($settings['version']) . ' | ' . __('by', 'teachcorses') . ' ' . esc_html($settings['author']) . '</p>
                  </td>';
            $s .= '</tr>';
        }
        
        $s .= '</table>';
        return $s;
    }
    
    /**
     * Shows the db status tab
     * @access private
     * @since 7.0.0 
     */
    private static function get_db_status_tab () {
        self::list_db_table_index(TEACHCOURSES_ARTEFACTS);
        self::list_db_table_index(TEACHCOURSES_ASSESSMENTS);
        self::list_db_table_index(TEACHCOURSES_AUTHORS);
        self::list_db_table_index(TEACHCOURSES_COURSES);
        self::list_db_table_index(TEACHCOURSES_COURSE_CAPABILITIES);
        self::list_db_table_index(TEACHCOURSES_COURSE_DOCUMENTS);
        self::list_db_table_index(TEACHCOURSES_COURSE_META);
        self::list_db_table_index(TEACHCOURSES_PUB);
        self::list_db_table_index(TEACHCOURSES_PUB_CAPABILITIES);
        self::list_db_table_index(TEACHCOURSES_PUB_DOCUMENTS);
        self::list_db_table_index(TEACHCOURSES_PUB_IMPORTS);
        self::list_db_table_index(TEACHCOURSES_PUB_META);
        self::list_db_table_index(TEACHCOURSES_RELATION);
        self::list_db_table_index(TEACHCOURSES_REL_PUB_AUTH);
        self::list_db_table_index(TEACHCOURSES_SETTINGS);
        self::list_db_table_index(TEACHCOURSES_SIGNUP);
        self::list_db_table_index(TEACHCOURSES_STUD);
        self::list_db_table_index(TEACHCOURSES_STUD_META);
        self::list_db_table_index(TEACHCOURSES_TAGS);
        self::list_db_table_index(TEACHCOURSES_USER);
    }
    
    /**
     * Returns the list of table indexes for the given database table
     * @param $db_name
     * @return string
     * @access private
     * @since 7.0.0
     */
    private static function list_db_table_index ($db_name) {
        echo '<h3>' . $db_name . '</h3>';
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Key_name','teachcorses') . '</th>';
        echo '<th>' . __('Type','teachcorses') . '</th>';
        echo '<th>' . __('Unique','teachcorses') . '</th>';
        echo '<th>' . __('Packed','teachcorses') . '</th>';
        echo '<th>' . __('Column','teachcorses') . '</th>';
        echo '<th>' . __('Cardinality','teachcorses') . '</th>';
        echo '<th>' . __('Collation','teachcorses') . '</th>';
        echo '<th>NULL</th>';
        echo '<th>' . __('Seq index','teachcorses') . '</th>';
        echo '</tr>';
        echo '</thead>';
        
        $result = tc_DB_Helpers::get_db_index($db_name);
        foreach ($result as $row) {
            // For unique field
            $unique = ( $row['Non_unique'] === '0' ) ? __('No') : __('Yes');
            
            // For NULL field
            if ( $row['Null'] === 'YES' ) {
                $n = __('Yes');
            }
            else if ( $row['Null'] === 'NO' ) {
                $n = __('No');
            }
            else {
                $n = $row['Null'];
            }
            
            echo '<tr>';
            echo '<td>' . $row['Key_name'] . '</td>';
            echo '<td>' . $row['Index_type'] . '</td>';
            echo '<td>' . $unique . '</td>';
            echo '<td>' . $row['Packed'] . '</td>';
            echo '<td>' . $row['Column_name'] . '</td>';
            echo '<td>' . $row['Cardinality'] . '</td>';
            echo '<td>' . $row['Collation'] . '</td>';
            echo '<td>' . $n . '</th>';
            echo '<td>' . $row['Seq_in_index'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    /**
     * Adds new term and new types for courses
     * @access private
     * @since 5.0.0
     */
    private static function add_course_options () {
        $new_term = isset( $_POST['new_term'] ) ? htmlspecialchars($_POST['new_term']) : ''; 
        $new_type = isset( $_POST['new_type'] ) ? htmlspecialchars($_POST['new_type']) : '';

        if (isset( $_POST['add_type'] ) && $new_type != __('Add type','teachcorses')) {
            tc_Options::add_option($new_type, $new_type, 'course_type');
            get_tc_message(__('Saved'));
        }
        if (isset( $_POST['add_term'] ) && $new_term != __('Add term','teachcorses')) {
           tc_Options::add_option($new_term, $new_term, 'semester');
           get_tc_message(__('Saved'));
        }
    }
    
    /**
     * Handles adding of new meta data fields
     * @param string $table         The table name (teachcorses_stud, teachcorses_courses or teachcorses_pub)
     * @access private
     * @since 5.0.0
     */
    private static function add_meta_fields ($table) {
        if ( !isset( $_POST['field_name'] ) ) {
            return;
        }
        
        // Generate field name
        $field_name = self::generate_meta_field_name($_POST['field_name'], $table);
        
        // Field values
        $data['title'] = isset( $_POST['field_label'] ) ? htmlspecialchars($_POST['field_label']) : '';
        $data['type'] = isset( $_POST['field_type'] ) ? htmlspecialchars($_POST['field_type']) : '';
        $data['visibility'] = isset( $_POST['visibility'] ) ? htmlspecialchars($_POST['visibility']) : '';
        $data['min'] = isset( $_POST['number_min'] ) ? intval($_POST['number_min']) : 'false';
        $data['max'] = isset( $_POST['number_max'] ) ? intval($_POST['number_max']) : 'false';
        $data['step'] = isset( $_POST['number_step'] ) ? intval($_POST['number_step']) : 'false';
        $data['required'] = isset( $_POST['is_required'] ) ? 'true' : 'false';
        $data['field_edit'] = isset( $_POST['field_edit'] ) ? intval($_POST['field_edit']) : 0 ;
        
        // Generate an array of forbidden field names
        $forbidden_names = array('system', 'course_type', 'semester', __('Field name','teachcorses'));
        $options = get_tc_options($table);
        foreach ( $options as $row) {
            if ( $data['field_edit'] !== intval($row->setting_id) ) {
                array_push( $forbidden_names, $row->variable );
            }
        }
        
        if ( !in_array($field_name, $forbidden_names) && $data['title'] != __('Label', 'teachcorses') && preg_match("#^[_A-Za-z0-9]+$#", $field_name) ) {
            
            // Delete old settings if needed
            if ( $data['field_edit'] > 0 ) {
                tc_Options::delete_option($data['field_edit']);
            }
            
            tc_DB_Helpers::register_column($table, $field_name, $data);
            get_tc_message(  __('Field added','teachcorses') );
        }
        else {
            get_tc_message(  __('Warning: This field name is not possible.','teachcorses'), 'red' );
        }
    }
    
    /**
     * Generates and returns a name for meta data fields
     * @param string $fieldname     The field name
     * @param string $table         The table name (used to define a prefix)
     * @access private
     * @since 5.0.0
     */
    private static function generate_meta_field_name($fieldname, $table) {
        $name = str_replace( array("'", '"', ' '), array("", "", '_'), $fieldname);
        
        if ( $table === 'teachcorses_courses' ) {
            $prefix = 'tc_meta_courses_';
        }
        elseif ( $table === 'teachcorses_pub' ) {
            $prefix = 'tc_meta_pub_';
        }
        elseif ( $table === 'teachcorses_stud' ) {
            $prefix = 'tc_meta_stud_';
        }
        else {
            $prefix = 'tc_meta_';
        }
        
        // Check if the prefix is already part of the field name
        if ( stristr($fieldname, $prefix) === false ) {
            return $prefix . esc_attr($name);
        }
        
        return esc_attr($name);
    }
    
    /**
     * Deletes student data fields
     * @param string $tab   The name of the tab (used for return link)
     * @access private
     * @since 5.0.0
     */
    private static function delete_meta_fields ($tab) {
        if ( isset($_GET['delete_field']) ) {
            $message = '<p>' . __('Do you really want to delete the selected meta field?','teachcorses') . '</p>' . '<a class="button-primary" href="options-general.php?page=teachcorses/settings.php&amp;delete_field_ok=' . intval($_GET['delete_field']) . '&amp;tab=' . $tab . '">'. __('OK') . '</a> <a class="button-secondary" href="options-general.php?page=teachcorses/settings.php&amp;tab=student_data">'. __('Cancel') . '</a>';
            get_tc_message($message,'orange');
        }
        if ( isset($_GET['delete_field_ok']) ) {
            $option = tc_Options::get_option_by_id($_GET['delete_field_ok']);
            $options = get_tc_options($option['variable'], "`setting_id` DESC", ARRAY_A);
            foreach ( $options as $row ) {
                tc_Options::delete_option($row['setting_id']);
            }
            tc_Options::delete_option($_GET['delete_field_ok']);
            get_tc_message( __('Field deleted','teachcorses') );
        }
    }

    /**
     * Handles changing of general options
     * @access private
     * @since 5.0.0
     */
    private static function change_general_options () {
        $option_semester = isset( $_POST['semester'] ) ? htmlspecialchars($_POST['semester']) : '';
        $option_rel_page_courses = isset( $_POST['rel_page_courses'] ) ? htmlspecialchars($_POST['rel_page_courses']) : '';
        $option_rel_page_publications = isset( $_POST['rel_page_publications'] ) ? htmlspecialchars($_POST['rel_page_publications']) : '';
        $option_stylesheet = isset( $_POST['stylesheet'] ) ? intval($_POST['stylesheet']) : '';
        $option_sign_out = isset( $_POST['sign_out'] ) ? intval($_POST['sign_out']) : '';
        $option_login = isset( $_POST['login'] ) ? htmlspecialchars($_POST['login']) : '';
        $option_userrole_publications = isset( $_POST['userrole_publications'] ) ? $_POST['userrole_publications'] : '';
        $option_userrole_courses = isset( $_POST['userrole_courses'] ) ? $_POST['userrole_courses'] : '';
    
        tc_Options::change_option('sem', $option_semester);
        tc_Options::change_option('rel_page_courses', $option_rel_page_courses);
        tc_Options::change_option('rel_page_publications', $option_rel_page_publications);
        tc_Options::change_option('stylesheet', $option_stylesheet);
        tc_Options::change_option('sign_out', $option_sign_out);
        tc_Options::change_option('login', $option_login);
        tc_update_userrole($option_userrole_courses, 'use_teachcorses_courses');
        tc_update_userrole($option_userrole_publications, 'use_teachcorses');

        get_tc_message( __('Settings are changed. Please note that access changes are visible, until you have reloaded this page a second time.','teachcorses') );
    }

    /**
     * Handles changing of options for publications
     * @access private
     * @since 5.0.0
     */
    private static function change_publication_options () {
        $checkbox_convert_bibtex = isset( $_POST['convert_bibtex'] ) ? 1 : '';
        $checkbox_import_overwrite = isset( $_POST['import_overwrite'] ) ? 1 : '';
        $checkbox_rel_content_auto = isset( $_POST['rel_content_auto'] ) ? 1 : '';
        tc_Options::change_option('convert_bibtex', $checkbox_convert_bibtex, 'checkbox');
        tc_Options::change_option('import_overwrite', $checkbox_import_overwrite, 'checkbox');
        tc_Options::change_option('rel_content_auto', $checkbox_rel_content_auto, 'checkbox');
        tc_Options::change_option('rel_content_template', $_POST['rel_content_template']);
        tc_Options::change_option('rel_content_category', $_POST['rel_content_category']);
        get_tc_message(__('Saved'));
        
    }
    
    /**
     * Handles start of database updates
     * @param string $site                      The current URL
     * @param boolean $with_structure_change    Update database structure (true) or not (false), Default is true
     * @access private
     * @since 5.0.0
     */
    private static function update_database ($site, $with_structure_change = true) {
        if ( $with_structure_change === true ) {
            tc_db_update();
        }
        $check_stud_meta = tc_Update::check_table_stud_meta();
        $check_authors = tc_Update::check_table_authors();
        if ( $check_authors === false && $check_stud_meta === false ) {
            return;
        }
        $sync = ( $check_authors === true ) ? 1 : 2;
        $table = ( $check_authors === true ) ? 'teachcorses_authors' : 'teachcorses_stud_meta';
        $message = 'TABLE ' . $table . ': ' .  __('teachCorses wants to fill up the new database. This can take some time.','teachcorses') . ' <a href="' . $site . '&amp;sync=' . $sync . '" class="button-primary">' . __('Continue','teachcorses') . '</a>';
        get_tc_message($message, 'orange');
    }
    
    /**
     * Hanldes start of database deletion
     * @access private
     * @since 5.0.0
     */
    private static function delete_database () {
        if ( isset($_GET['drop_tp']) ) {
            $message = '<p>' . __('Do you really want to delete all teachcorses database tables?','teachcorses') . '</p>' . '<a class="button-primary" href="options-general.php?page=teachcorses/settings.php&amp;tab=general&amp;drop_tc_ok=1">'. __('OK') . '</a> <a class="button-secondary" href="options-general.php?page=teachcorses/settings.php&amp;tab=general">'. __('Cancel') . '</a>';
            get_tc_message($message,'orange');
        }
        if ( isset($_GET['drop_tc_ok']) ) {
            tc_uninstall();
            get_tc_message( __('Database uninstalled','teachcorses') );
        }
    }
}
