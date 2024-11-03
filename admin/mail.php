<?php
/**
 * This file contains all functions for displaying the mail page in admin menu
 * 
 * @package teachcorses\admin\courses
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Mail form
 * 
 * @since 3.0.0
 */
function tc_show_mail_page() {

    $current_user = wp_get_current_user();

    $course_id = isset( $_GET['course_id'] ) ? intval($_GET['course_id']) : 0;
    $redirect = isset( $_GET['redirect'] ) ?  intval($_GET['redirect']) : 0;
    $student_id = isset( $_GET['student_id'] ) ? intval($_GET['student_id']) : 0;
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $sem = isset( $_GET['sem'] ) ? htmlspecialchars($_GET['sem']) : '';
    $single = isset( $_GET['single'] ) ? htmlspecialchars($_GET['single']) : '';
    $students_group = isset( $_GET['students_group'] ) ? htmlspecialchars($_GET['students_group']) : '';
    $limit = isset( $_GET['limit'] ) ? intval($_GET['limit']) : 0;
    $group = isset( $_GET['group'] ) ? htmlspecialchars($_GET['group']) : '';
    $waitinglist = '';
    
    // check capabilities
    if ( $course_id !== 0 ) {
        $capability = tc_Courses::get_capability($course_id, $current_user->ID);
        if ( $capability !== 'owner' && $capability !== 'approved' ) {
            echo __('Access denied','teachcorses');
            return;
        }
    }

    if( !isset( $_GET['single'] ) ) {	
        // E-Mails of registered participants
        if ( $group === 'reg' ) {
            $waitinglist = 0;	
        }
        // E-Mails of participants in waitinglist
        if ( $group === 'wtl' ) {
            $waitinglist = 1;		
        }
        $mails = tc_Courses::get_signups(array('output_type' => ARRAY_A, 
                                                'course_id' => $course_id, 
                                                'waitinglist' => $waitinglist ) );
    }
    ?>
    <div class="wrap">
        <?php
        if ( isset( $_GET['course_id'] ) ) {
            $return_url = "admin.php?page=teachcorses/teachcorses.php&amp;course_id=$course_id&amp;sem=$sem&amp;search=$search&amp;redirect=$redirect&amp;action=enrollments";
        }
        if ( isset( $_GET['student_id'] ) ) {
            $return_url = "admin.php?page=teachcorses/students.php&amp;student_id=$student_id&amp;search=$search&amp;students_group=$students_group&amp;limit=$limit";
        }
        ?>
        <p><a href="<?php echo $return_url; ?>" class="button-secondary">&larr; <?php _e('Back','teachcorses'); ?></a></p>
        <h2><?php _e('Writing an E-Mail','teachcorses'); ?></h2>
        <form name="form_mail" method="post" action="<?php echo $return_url; ?>">
        <table class="form-table">
            <tr>
            <th scope="row" style="width: 65px;"><label for="mail_from"><?php _e('From','teachcorses'); ?></label</th>
            <td>
                <select name="from" id="mail_from">
                    <option value="currentuser"><?php echo $current_user->display_name . ' (' . $current_user->user_email . ')'; ?></option>
                    <option value="wordpress"><?php echo get_bloginfo('name') . ' (' . get_bloginfo('admin_email') . ')'; ?></option>
                </select>
            </td>
            </tr>
            <tr>
                <th scope="row" style="width: 65px;">
                    <select name="recipients_option" id="mail_recipients_option">
                        <option value="To"><?php _e('To','teachcorses'); ?></option>
                        <option value="Bcc"><?php _e('Bcc','teachcorses'); ?></option>
                    </select>
                </th>
                <td>
                    <?php
                    if( !isset( $_GET['single'] ) ) {
                        $link = "admin.php?page=teachcorses/teachcorses.php&amp;course_id=$course_id&amp;sem=$sem&amp;search=$search&amp;action=mail&amp;type=course";
                        if ($group == "wtl") {
                            echo '<p><strong><a href="' . $link . '">' . __('All', 'teachcorses') . '</a> | <a href="' . $link . '&amp;group=reg">' . __('Only participants', 'teachcorses') . '</a> | ' . __('Only waitinglist','teachcorses') . '</strong><p>';
                        }
                        elseif ( $group == "reg" ) {
                            echo '<p><strong><a href="' . $link . '">' . __('All', 'teachcorses') . '</a> | ' . __('Only participants', 'teachcorses') . ' | <a href="' . $link . '&amp;group=wtl">' . __('Only waitinglist','teachcorses') . '</a></strong><p>';
                        }
                        else {
                            echo '<p><strong>' . __('All', 'teachcorses') . ' | <a href="' . $link . '&amp;group=reg">' . __('Only participants', 'teachcorses') . '</a> | <a href="' . $link . '&amp;group=wtl">' . __('Only waitinglist','teachcorses') . '</a></strong><p>';
                        }
                    }
                    
                    if( !isset( $_GET['single'] ) ) {
                        $to = '';
                        foreach($mails as $mail) { 
                            $to = ( $to === '' ) ? $mail["email"] : $to . ', ' . $mail["email"]; 
                        }
                    }
                    else {
                        $to = $single;
                    }
                    ?> 
                    <textarea name="recipients" id="mail_recipients" rows="3" style="width: 590px;"><?php echo $to; ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row" style="width: 65px;"><label for="mail_subject"><?php _e('Subject','teachcorses'); ?></label></th>
                <td><input name="subject" id="mail_subject" type="text" style="width: 580px;"/></td>
            </tr>
        </table>
        <br />
        <textarea name="text" id="mail_text" style="width: 685px;" rows="15"></textarea>
        <table>
            <tr>
                <td><input type="checkbox" name="backup_mail" id="backup_mail" title="<?php _e('Send me the e-mail as separate copy','teachcorses'); ?>" value="backup" checked="checked" /></td>
                <td><label for="backup_mail"><?php _e('Send me the e-mail as separate copy','teachcorses'); ?></label></td>
            </tr>
        </table>
        <br />
        <input type="submit" class="button-primary" name="send_mail" value="<?php _e('Send','teachcorses'); ?>"/>
        <script type="text/javascript" charset="utf-8" src="<?php echo plugins_url( 'js/admin_mail.js', dirname( __FILE__ ) ); ?>"></script>
        </form>
    </div>
    <?php
}
