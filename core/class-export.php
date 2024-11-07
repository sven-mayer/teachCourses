<?php
/**
 * This file contains all general functions for the export system
 * 
 * @package teachcourses\core\export
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * teachcourses export class
 *
 * @package teachcourses\core\export
 * @since 3.0.0
 */
class tc_Export {

    /**
     * Export course data in xls format
     * @param int $course_id 
     * @since 3.0.0
     */
    public static function get_course_xls($course_id) {
        global $current_user;
        $parent = '';
        
        // load course data
        $data = TC_Courses::get_course($course_id, ARRAY_A);
        $course_name = $data['name'];
        if ($data['parent'] != '0') {
            $parent = TC_Courses::get_course($data['parent'], ARRAY_A);
            $course_name = $parent['name'] . ' ' . $data['name'];
        }

        // load settings
        $option['regnum'] = get_tc_option('regnum');
        $option['studies'] = get_tc_option('studies');

        echo '<h2>' . stripslashes(utf8_decode($course_name)) . ' ' . stripslashes(utf8_decode($data['semester'])) . '</h2>';
        echo '<table border="1" cellspacing="0" cellpadding="5">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Lecturer','teachcourses') . '</th>';
        echo '<td>' . stripslashes(utf8_decode($data['lecturer'])) . '</td>';
        echo '<th>' . __('Date','teachcourses') . '</th>';
        echo '<td>' . $data['date'] . '</td>';
        echo '<th>' . __('Room','teachcourses') . '</th>';
        echo '<td>' . stripslashes(utf8_decode($data['room'])) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th>' . __('Places','teachcourses') . '</th>';
        echo '<td>' . $data['places'] . '</td>';
        echo '<th>' . __('free places','teachcourses') . '</th>';
        echo '<td></td>';
        echo '<td>&nbsp;</td>';
        echo '<td>&nbsp;</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th>' . __('Comment','teachcourses') . '</th>';
        echo '<td colspan="5">' . stripslashes(utf8_decode($data['comment'])) . '</td>';
        echo '</tr>';
        echo '</thead>';
        echo '</table>';

        global $tc_version;
        echo '<p style="font-size:11px; font-style:italic;">' . __('Created on','teachcourses') . ': ' . date("d.m.Y") . ' | teachcourses ' . $tc_version . '</p>';
    }

    /**
     * Export course data in csv format
     * @param int $course_id
     * @param array $options 
     * @since 3.0.0
     */
    public static function get_course_csv($course_id) {
        global $current_user;
        
        // load settings
        $option['regnum'] = get_tc_option('regnum');
        $option['studies'] = get_tc_option('studies');
        $fields = get_tc_options('teachcourses_stud','`setting_id` ASC');
        
        $extra_headlines = '';
        foreach ( $fields as $field ) {
            $data = tc_DB_Helpers::extract_column_data($field->value);
            if ( $data['visibility'] === 'admin') {
                $extra_headlines .= '"' . stripslashes( utf8_decode( $data['title'] ) ) . '";';
            }
        }

        $headline = '"' . __('Last name','teachcourses') . '";"' . __('First name','teachcourses') . '";"' . __('User account','teachcourses') . '";"' . __('E-Mail') . '";' . $extra_headlines . '"' . __('Registered at','teachcourses') . '";"' . __('Record-ID','teachcourses') . '";"' . __('Waiting list','teachcourses') . '"' . "\r\n";
        $headline = tc_Export::decode($headline);
        echo $headline;
       
    }

    /**
     * Generate rtf document format
     * @param array $row
     * @return string
     * @since 3.0.0
     * @access private
     */
    private static function rtf ($row) {
        $head = '{\rtf1';
        $line = '';
        foreach ($row as $row) {
            $line .= self::rtf_row($row) . '\par'. '\par';
        }
        $foot = '}';
        return $head . $line . $foot;
    }

    /**
     * Returns a single line for rtf file
     * @param array $row        The publication array
     * @return string
     * @since 3.0.0
     * @access public
    */
    public static function rtf_row ($row) {
        $settings = array(
            'author_name'       => 'initials',
            'editor_name'       => 'initials',
            'editor_separator'  => ';',
            'style'             => 'simple',
            'meta_label_in'     => __('In','teachcourses') . ': ',
            'use_span'          => false
        );
        if ( $row['type'] === 'collection' || $row['type'] === 'periodical' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = tc_Bibtex::parse_author($row['editor'], ';', $settings['editor_name'] ) . ' (' . __('Ed.','teachcourses') . ')';
        }
        else {
            $all_authors = tc_Bibtex::parse_author($row['author'], ';', $settings['author_name'] );
        }
        $meta = tc_HTML_Publication_Template::get_publication_meta_row($row, $settings);
        $line = $all_authors . ': ' . tc_HTML::prepare_title($row['title'], 'replace') . '. ' . $meta;
        $line = str_replace('  ', ' ', $line);
        $line = utf8_decode(self::decode($line));
        return $line;
    }
    
    /**
     * Returns a single line for a utf8 encoded text
     * @param array $row        The publication array
     * @return string
     * @since 6.0.0
     * @access public
    */
    public static function text_row ($row) {
        $settings = array(
            'author_name'       => 'initials',
            'editor_name'       => 'initials',
            'editor_separator'  => ';',
            'style'             => 'simple',
            'meta_label_in'     => __('In','teachcourses') . ': ',
            'use_span'          => false
        );
        if ( $row['type'] === 'collection' || $row['type'] === 'periodical' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = tc_Bibtex::parse_author($row['editor'], ';', $settings['editor_name'] ) . ' (' . __('Ed.','teachcourses') . ')';
        }
        else {
            $all_authors = tc_Bibtex::parse_author($row['author'], ';', $settings['author_name'] );
        }
        $meta = tc_HTML_Publication_Template::get_publication_meta_row($row, $settings);
        $line = $all_authors . ': ' . tc_HTML::prepare_title($row['title'], 'replace') . '. ' . $meta;
        $line = str_replace('  ', ' ', $line);
        return trim($line);
    }

    /**
     * Decode chars with wrong charset to UTF-8
     * @param string $char
     * @return string
     * @since 3.0.0
     * @access private 
    */
    private static function decode ($char) {
        $array_1 = array('–', 'Ã¼', 'Ã¶', 'Ã¤', 'Ã¤', 'Ã?', 'Â§', 'Ãœ', 'Ã', 'Ã–','&Uuml;','&uuml;', '&Ouml;', '&ouml;', '&Auml;','&auml;', '&nbsp;', '&szlig;', '&sect;', '&ndash;', '&rdquo;', '&ldquo;', '&eacute;', '&egrave;', '&aacute;', '&agrave;', '&ograve;','&oacute;', '&copy;', '&reg;', '&micro;', '&pound;', '&raquo;', '&laquo;', '&yen;', '&Agrave;', '&Aacute;', '&Egrave;', '&Eacute;', '&Ograve;', '&Oacute;', '&shy;', '&amp;', '&quot;',);
        $array_2 = array('-', 'ü', 'ö', 'ä', 'ä', 'ß', '§', 'Ü', 'Ä', 'Ö', 'Ü', 'ü', 'Ö', 'ö', 'Ä', 'ä', ' ', 'ß', '§', '-', '”', '“', 'é', 'è', 'á', 'à', 'ò', 'ó', '©', '®', 'µ', '£', '»', '«', '¥', 'À', 'Á', 'È', 'É', 'Ò', 'Ó', '­', '&', '"');
        $char = str_replace($array_1, $array_2, $char);
        return $char;
    }
}
