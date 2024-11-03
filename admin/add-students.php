<?php 
/**
 * This file contains all functions for displaying the add_students page in admin menu
 * 
 * @package teachcorses\admin\students
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Form for manual edits in the enrollment system
 * @param array $fields       An associative array with the settings of the meta data fields. The array keys are variable an value.
 * @since 2.0.0
*/ 
function tc_add_student_page($fields) { 

    $wp_id = isset($_POST['wp_id']) ? intval($_POST['wp_id']) : '';
    $data['userlogin'] = isset( $_POST['userlogin'] ) ? htmlspecialchars($_POST['userlogin']) : '';
    $data['email'] = isset( $_POST['email'] ) ? htmlspecialchars($_POST['email']) : '';

    // actions
    if (isset( $_POST['insert'] ) && $wp_id != __('WordPress User-ID','teachcorses') && $wp_id != '') {
        tc_Enrollments::add_student($wp_id, $data['userlogin'], $data['email'], $fields, filter_input_array(INPUT_POST, $_POST));
        get_tc_message( __('Student added','teachcorses') );
    }
    ?>
    <div class="wrap" >
        <p><a href="admin.php?page=teachcorses/students.php" class="button-secondary"><?php _e('Back','teachcorses'); ?></a></p>
    <h2><?php _e('Add student','teachcorses'); ?></h2>

    <p style="padding:0px; margin:0px;">&nbsp;</p>
    <form id="new_student" name="new_student" method="post" action="admin.php?page=teachcorses/students.php&action=add">
    <table class="form-table">
        <thead>
          <tr>
            <td><label for="wp_id"><b><?php _e('WordPress User-ID','teachcorses'); ?></b></label></td>
            <td style="text-align:left;">
                <?php 
                echo '<select name="wp_id" id="wp_id">';
                echo '<option value="n">' . __('Select user','teachcorses') . '</option>';
                $row = tc_Students::get_unregistered_students();
                foreach ($row as $row) {
                    echo '<option value="' . $row['ID'] . '">' . $row['user_login'] . '</option>';
                }
                echo '</select> ' . __('The Menu shows all your blog users who has no teachCorses account','teachcorses'); ?>
            </td>
      	  </tr>
          <tr>
            <td><label for="firstname"><b><?php _e('First name','teachcorses'); ?></b></label></td>
            <td><input name="firstname" type="text" id="firstname" size="40" required="required" /></td>
          </tr>
          <tr>
            <td><label for="lastname"><b><?php _e('Last name','teachcorses'); ?></b></label></td>
            <td><input name="lastname" type="text" id="lastname" size="40" required="required" /></td>
          </tr>
          <tr>
            <td><label for="userlogin"><b><?php _e('User account','teachcorses'); ?></b></label></td>
            <td style="text-align:left;"><input type="text" name="userlogin" id="userlogin" required="required" /></td>
          </tr>
          <tr>
            <td><label for="email"><b><?php _e('E-Mail'); ?></b></label></td>
            <td><input name="email" type="text" id="email" size="50" required="required" /></td>
          </tr>
          <?php
          // Show custom fields
            foreach ($fields as $row) {
                $data = tc_DB_Helpers::extract_column_data($row['value']);
                $required = ( $data['required'] === 'true' ) ? true : false;
                $value = '';
                if ( $data['type'] === 'SELECT' ) {
                    echo tc_Enrollments::get_form_select_field($row['variable'], $data['title'], $value, false, $required);
                }
                elseif ( $data['type'] === 'TEXTAREA' ) {
                    echo tc_Enrollments::get_form_textarea_field($row['variable'], $data['title'], $value, $required);
                }
                elseif ( $data['type'] === 'DATE' ) {
                    echo tc_Enrollments::get_form_date_field($row['variable'], $data['title'], $value);
                }
                elseif ( $data['type'] === 'INT' ) {
                    $data['min'] = ( $data['min'] !== 'false' ) ? intval($data['min']) : 0;
                    $data['max'] = ( $data['max'] !== 'false' ) ? intval($data['max']) : 999;
                    $data['step'] = ( $data['step'] !== 'false' ) ? intval($data['step']) : 1;
                    echo tc_Enrollments::get_form_int_field($row['variable'], $data['title'], $value, $data['min'], $data['max'], $data['step'], false, $required);
                }
                elseif ( $data['type'] === 'CHECKBOX' ) {
                    echo tc_Enrollments::get_form_checkbox_field($row['variable'], $data['title'], $value, false, $required);
                }
                elseif ( $data['type'] === 'RADIO' ) {
                    echo tc_Enrollments::get_form_radio_field($row['variable'], $data['title'], $value, false, $required);
                }
                else {
                    echo tc_Enrollments::get_form_text_field($row['variable'], $data['title'], $value, false, $required);
                }
            }
            ?>
         </thead>
        </table>
    <p>
      <input name="insert" type="submit" id="std_einschreiben" value="<?php _e('Create','teachcorses'); ?>" class="button-primary"/>
      <input name="reset" type="reset" id="reset" value="Reset" class="button-secondary"/>
    </p>
</form>
</div>
<?php }