<?php
/**
 * teachcourses template file
 * @package teachcourses\core\templates
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 6.0.0
 */

class tc_Template_Orig implements tc_Publication_Template {
    
    public function get_settings() {
        return array ('name'                => 'teachcourses original',
                      'description'         => 'The original 4-line style template for publication lists.',
                      'author'              => 'Michael Winkler',
                      'version'             => '1.2',
                      'button_separator'    => ' | ',
                      'citation_style'      => 'teachcourses'
        );
    }
    
    /**
     * Returns the body element for a publication list
     * @param string $content   The content of the publication list itself
     * @param array $args       An array with some basic settings for the publication list 
     * @return string
     */
    public function get_body ($content, $args = array() ) {
        return '<table class="teachcourses_publication_list">' . $content . '</table>';
    }
    
    /**
     * Returns the headline for a publication list or a part of that
     * @param type $content     The content of the headline
     * @param type $args        An array with some basic settings for the publication list
     * @return string
     */
    public function get_headline ($content, $args = array()) {
        return '<tr>
                    <td' . $args['colspan'] . '>
                        <h3 class="tc_h3" id="tc_h3_' . esc_attr($content) .'">' . $content . '</h3>
                    </td>
                </tr>';
    }
    
    /**
     * Returns the headline (second level) for a publication list or a part of that
     * @param type $content     The content of the headline
     * @param type $args        An array with some basic settings for the publication list
     * @return string
     */
    public function get_headline_sl ($content, $args = array()) {
        return '<tr>
                    <td' . $args['colspan'] . '>
                        <h4 class="tc_h4" id="tc_h4_' . esc_attr($content) .'">' . $content . '</h4>
                    </td>
                </tr>';
    }
    
    /**
     * Returns the container for publication images
     * @param string $content               The image element
     * @param string $position              The image position: left, right or buttom
     * @param string $optional_attributes   Optional attributes for the framing container element
     * @return string
     * @since 8.0.0
     */
    public function get_image($content, $position, $optional_attributes = '') {
        return '<td class="tc_pub_image_' . $position . '" ' . $optional_attributes . '>' . $content . '</td>';
    }
    
    /**
     * Returns the single entry of a publication list
     * @param object $interface     The interface object
     * @return string
     */
    public function get_entry ($interface) {
        $class = ' tc_publication_' . $interface->get_type('');
        $s = '<tr class="tc_publication' . $class . '">';
        $s .= $interface->get_number('<td class="tc_pub_number">', '.</td>');
        $s .= $interface->get_images('left');
        $s .= '<td class="tc_pub_info">';
        $s .= $interface->get_author('<p class="tc_pub_author">', '</p>');
        $s .= '<p class="tc_pub_title">' . $interface->get_title() . ' (' . $interface->get_type() . ')</p>';
        $s .= '<p class="tc_pub_additional">' . $interface->get_meta() . '</p>';
        $s .= '<p class="tc_pub_menu">' . $interface->get_menu_line('(', ')') . '</p>';
        $s .= $interface->get_infocontainer();
        $s .= $interface->get_images('bottom');
        $s .= '</td>';
        $s .= $interface->get_images('right');
        $s .= '</tr>';
        return $s;
    }
    
}

