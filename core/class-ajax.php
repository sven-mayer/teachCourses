<?php
/**
 * This file contains all functions which are used in ajax calls
 * @package teachcorses\core\ajax
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * This class contains all functions which are used in ajax calls
 * @package teachcorses\core\ajax
 * @since 5.0.0
 */
class tc_Ajax {
    /**
     * Adds a document headline
     * @param string $doc_name      The name of the document
     * @param int $course_id        The course ID
     * @since 5.0.0
     * @access public
     */
    public static function add_document_headline( $doc_name, $course_id ) {
        $file_id = tc_Documents::add_document($doc_name, '', 0, $course_id);
        echo $file_id;
    }
    
    /**
     * Changes the name of a document
     * @param int $doc_id          The document ID
     * @param string $doc_name     The name of the document
     * @since 5.0.0
     * @access public
     */
    public static function change_document_name( $doc_id, $doc_name ) {
        tc_Documents::change_document_name($doc_id, $doc_name);
        echo $doc_name;
    }
    
    /**
     * Deletes a document
     * @param int $doc_id           The document ID
     * @return boolean
     * @since 5.0.0
     * @access public
     */
    public static function delete_document( $doc_id ) {
        $doc_id = intval($doc_id);
        $data = tc_Documents::get_document($doc_id);
        if ( $data['path'] !== '' ) {
            $uploads = wp_upload_dir();
            $test = @ unlink( $uploads['basedir'] . $data['path'] );
            //echo $uploads['basedir'] . $data['path'];
            if ( $test === false ) {
                echo 'false';
                return false;
            }
        }
        tc_Documents::delete_document($doc_id);
        echo 'true';
        return true;
    }
    
    /**
     * Gets the artefact info screen. The info screen is used in the assessment menu of teachCorses.
     * @param int $artefact_id      The artefact ID
     * @since 5.0.0
     * @access public
     */
    public static function get_artefact_screen($artefact_id) {
        $artefact = tc_Artefacts::get_artefact($artefact_id);
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachCorses - Assessment details</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="tc_artefact_id" type="hidden" value="' . $artefact_id . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . __('Title','teachcorses') . '</td>';
        echo '<td><input name="tc_artefact_title" cols="50" value="' . stripslashes($artefact['title']) . '"/></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tc_save_artefact" type="submit" class="button-primary" value="' . __('Save') . '"/> <input name="tc_delete_artefact" type="submit" class="button-secondary" value="' . __('Delete','teachcorses') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets the info screen for a single assessment.
     * @param int $assessment_id       The assessment ID
     * @since 5.0.0
     * @access public
     */
    public static function get_assessment_screen($assessment_id) {
        global $current_user;
        $assessment = tc_Assessments::get_assessment($assessment_id);
        $artefact = tc_Artefacts::get_artefact($assessment['artefact_id']);
        $course_id = ( $assessment['course_id'] !== '' ) ? $assessment['course_id'] : $artefact['course_id'];
        $capability = tc_Courses::get_capability($course_id, $current_user->ID);
        $student = tc_Students::get_student($assessment['wp_id']);
        $examiner = get_userdata($assessment['examiner_id']);

        // Check capability
        if ( $capability !== 'owner' && $capability !== 'approved' ) {
            return;
        }

        $artefact['title'] = ( $artefact['title'] == '' ) ? __('Complete Course','teachcorses') : $artefact['title'];
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachCorses - Assessment details</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="tc_assessment_id" type="hidden" value="' . $assessment_id . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . __('Name','teachcorses') . '</td>';
        echo '<td>' . stripslashes($student['firstname']) . ' ' . stripslashes($student['lastname']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Artefact','teachcorses') . '</td>';
        echo '<td>' . stripslashes($artefact['title'])  . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Type','teachcorses') . '</td>';
        echo '<td>' . tc_Admin::get_assessment_type_field('tc_type', $assessment['type']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Value/Grade','teachcorses') . '</td>';
        echo '<td><input name="tc_value" type="text" size="50" value="' . $assessment['value'] . '" /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Comment','teachcorses') . '</td>';
        echo '<td><textarea name="tc_comment" rows="4" cols="50">' . stripslashes($assessment['comment']) . '</textarea></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Has passed','teachcorses') . '</td>';
        echo '<td>' . tc_Admin::get_assessment_passed_field('tc_passed', $assessment['passed']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Date','teachcorses') . '</td>';
        echo '<td>' . $assessment['exam_date'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Examiner','teachcorses') . '</td>';
        echo '<td>' . stripslashes($examiner->display_name) . '</td>';
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tc_save_assessment" type="submit" class="button-primary" value="' . __('Save') . '"/> <input name="tc_delete_assessment" type="submit" class="button-secondary" value="' . __('Delete','teachcorses') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets a list of publications of a single author. This function is used for teachcorses/admin/show_authors.php
     * @param int $author_id        The authur ID
     * @since 5.0.0
     * @access public
     */
    public static function get_author_publications( $author_id ) {
        $author_id = intval($author_id);
        $pubs = tc_Authors::get_related_publications($author_id, ARRAY_A);
        echo '<ol>';
        foreach ( $pubs as $pub) {
            echo '<li style="padding-left:10px;">';
            echo '<a target="_blank" title="' . __('Edit publication','teachcorses') .'" href="admin.php?page=teachcorses/addpublications.php&pub_id=' . $pub['pub_id'] . '">' . tc_HTML::prepare_title($pub['title'], 'decode') . '</a>, ' . stripslashes($pub['type']) . ', ' . $pub['year'];
            if ( $pub['is_author'] == 1 ) {
                echo ' (' . __('as author','teachcorses') . ')';
            }
            if ( $pub['is_editor'] == 1 ) {
                echo ' (' . __('as editor','teachcorses') . ')';
            }
            echo '</li>';
        }
        echo '</ol>';
    }
    
    /**
     * Gets a unique bibtex key from a given string
     * @param string $string
     * @since 6.1.1
     * @access public
     */
    public static function get_generated_bibtex_key ($string) {
        echo tc_Publications::generate_unique_bibtex_key($string);
    }

    /**
     * Gets the cite screen for a single publication.
     * @param int $cite_id       The publication ID
     * @since 6.0.0
     * @access public
     */
    public static function get_cite_screen ($cite_id) {
        $publication = tc_Publications::get_publication($cite_id, ARRAY_A);
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachCorses - cite publication</title>';
        echo '</head>';
        echo '<body>';
        echo '<div class="content">';
        echo '<div class="wrap">';
        echo '<h3 class="nav-tab-wrapper"><a class="nav-tab nav-tab-active tc_cite_text" id="tc_cite_text_' . $cite_id . '" pub_id="' . $cite_id . '">' . __('Text','teachcorses') . '</a> <a class="nav-tab tc_cite_bibtex" id="tc_cite_bibtex_' . $cite_id . '" pub_id="' . $cite_id . '">' . __('BibTeX','teachcorses') . '</a></h3>';
        echo '<form name="form_cite" method="post">';
        echo '<input name="tc_cite_id" type="hidden" value="' . '"/>';
        echo '<textarea name="tc_cite_full" id="tc_cite_full_' . $cite_id . '" class="tc_cite_full" rows="7" style="width:100%; border-top:none;" title="' . __('Publication entry','teachcorses') . '">' . tc_Export::text_row($publication) . '</textarea>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets the cite text for a publication
     * @param int $cite_id      the publication ID
     * @param string $mode      text or bibtex
     * @access public
     * @since 6.0.0
     */
    public static function get_cite_text ($cite_id, $mode) {
        if ( $mode === 'bibtex' ) {
            $publication = tc_Publications::get_publication($cite_id, ARRAY_A);
            $tags = tc_Tags::get_tags(array('pub_id' => $cite_id, 'output_type' => ARRAY_A));
            echo tc_Bibtex::get_single_publication_bibtex($publication, $tags);
        }
        if ( $mode === 'text' ) {
            $publication = tc_Publications::get_publication($cite_id, ARRAY_A);
            echo tc_Export::text_row($publication);
        }
    }


    /**
     * Gets the name of a document
     * @param int $doc_id       The ID of the document
     * @since 5.0.0
     * @access public
     */
    public static function get_document_name( $doc_id ) {
        $doc_id = intval($doc_id);
        $data = tc_Documents::get_document($doc_id);
        echo stripslashes($data['name']);
    }
    
    /**
     * Gets the meta field screen for the settings panel
     * @param int $meta_field_id        The meta field ID
     * @since 6.0.0
     * @access public
     */
    public static function get_meta_field_screen ( $meta_field_id ) {
        if ( $meta_field_id === 0 ) {
            $data = array(
                'name' => '',
                'title' => '',
                'type' => '',
                'min' => '',
                'max' => '',
                'step' => '',
                'visibility' => '',
                'required'
            );
        }
        else {
            $field = tc_Options::get_option_by_id($meta_field_id);
            $data = tc_DB_Helpers::extract_column_data($field['value']);
        }
        
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachCorses - Meta Field Screen</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="field_edit" type="hidden" value="' . $meta_field_id . '">';
        echo '<table class="form-table">';
        
        // field name
        if ( $meta_field_id === 0 ) {
            echo '<tr>';
            echo '<td><label for="field_name">' . __('Field name','teachcorses') . '</label></td>';
            echo '<td><input name="field_name" type="text" id="field_name" size="30" title="' . __('Allowed chars','teachcorses') . ': A-Z,a-z,0-9,_" value="' . $data['name'] . '"/></td>';
            echo '</tr>';
        }
        else {
            echo '<input name="field_name" id="field_name" type="hidden" value="' . $data['name'] . '">';
        }
        
        // label
        echo '<tr>';
        echo '<td><label for="field_label">' . __('Label','teachcorses') . '</label></td>';
        echo '<td><input name="field_label" type="text" id="field_label" size="30" title="' . __('The visible name of the field','teachcorses') . '" value="' . $data['title'] . '" /></td>';
        echo '</tr>';
        
        // field type
        $field_types = array('TEXT', 'TEXTAREA', 'INT', 'DATE', 'SELECT', 'CHECKBOX', 'RADIO');
        echo '<tr>';
        echo '<td><label for="field_type">' . __('Field type','teachcorses') . '</label></td>';
        echo '<td>';
        echo '<select name="field_type" id="field_type">';
        foreach ( $field_types as $type ) {
            $selected = ( $data['type'] === $type ) ? 'selected="selected"' : '';
            echo '<option value="' . $type . '" ' . $selected . '>' . $type . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        // min
        $min = ( $data['min'] === 'false' ) ? '' : intval($min);
        echo '<tr>';
        echo '<td><label for="number_min">' . __('Min','teachcorses') . ' (' . __('Only for INT fields','teachcorses') . ')</label></td>';
        echo '<td><input name="number_min" id="number_min" type="number" size="10" value="' . $min . '"/></td>';
        echo '</tr>';
        
        // max
        $max = ( $data['max'] === 'false' ) ? '' : intval($max);
        echo '<tr>';
        echo '<td><label for="number_max">' . __('Max','teachcorses') . ' (' . __('Only for INT fields','teachcorses') . ')</label></td>';
        echo '<td><input name="number_max" id="number_max" type="number" size="10" value="' . $max . '"/></td>';
        echo '</tr>';
        
        // step
        $step = ( $data['step'] === 'false' ) ? '' : intval($step);
        echo '<tr>';
        echo '<td><label for="number_step">' . __('Step','teachcorses') . ' (' . __('Only for INT fields','teachcorses') . ')</label></td>';
        echo '<td><input name="number_step" id="number_step" type="text" size="10" value="' . $step . '"/></td>';
        echo '</tr>';
        
        // visibility
        echo '<tr>';
        echo '<td><label for="visibility">' . __('Visibility','teachcorses') . '</label></td>';
        echo '<td>';
        echo '<select name="visibility" id="visibility">';
        
        // normal
        $vis_normal = ( $data['visibility'] === 'normal' ) ? 'selected="selected"' : '';
        echo '<option value="normal" ' . $vis_normal . '>' . __('Normal','teachcorses') . '</option>';

        // admin
        $vis_admin = ( $data['visibility'] === 'admin' ) ? 'selected="selected"' : '';
        echo '<option value="admin" ' . $vis_admin . '>' . __('Admin','teachcorses') . '</option>';

        // hidden
        $vis_hidden = ( $data['visibility'] === 'hidden' ) ? 'selected="selected"' : '';
        echo '<option value="hidden" ' . $vis_hidden . '>' . __('Hidden','teachcorses') . '</option>';
        
        echo '</select>';
        echo '</td>';
        echo '</tr>'; 
        
        // required
        $req = ( $data['required'] === 'true' ) ? 'checked="checked"' : '';
        echo '<tr>';
        echo '<td colspan="2"><input type="checkbox" name="is_required" id="is_required" ' . $req . '/> <label for="is_required">' . __('Required field','teachcorses') . '</label></td>';
        echo '</tr>';
           
        echo '</table>';
        echo '<p><input type="submit" name="add_field" class="button-primary" value="' . __('Save','teachcorses') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets the url of a mimetype image
     * @param string $filename      The filename or the url
     * @since 5.0.0
     * @access public
     */
    public static function get_mimetype_image( $filename ) {
        echo tc_Icons::get_class($filename);
    }

    /**
     * Saves the order of a document list
     * @param array $array      A numeric array which represents the sort order of course documents
     * @since 5.0.0
     * @access public
     */
    public static function set_sort_order( $array ) {
        $i = 0;
        foreach ($array as $value) {
            tc_Documents::set_sort($value, $i);
            $i++;
        }
    }
}
