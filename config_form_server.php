<div id="simpleTabs-content-1" class="simpleTabs-content">

    <table class='form-table' role='presentation'>
        <tbody>
            <tr>
                <th scope="row"><lable for="postie-settings-input_connection"><?php _e('Connection', 'postie') ?></lable></th>
        <td>
            <select name='postie-settings[input_connection]' id='postie-settings-input_connection'>
                <option value="sockets"  <?php echo (($input_connection == "socket") ? " selected='selected' " : "") ?>>Sockets</option>
                <?php if (function_exists('curl_version')) { ?>
                    <option value="curl" <?php echo ($input_connection == "curl") ? "selected='selected' " : "" ?>>cURL</option>
                <?php } ?>
            </select>
            <p class='description'><?php _e("Sockets is preferred, but doesn't work with some hosts.", 'postie'); ?></p>
        </td>
        </tr>

        <tr>
            <th scope="row"><lable for="postie-settings-input_protocol"><?php _e('Mail Protocol', 'postie') ?></lable></th>
        <td>
            <select name='postie-settings[input_protocol]' id='postie-settings-input_protocol'>
                <option value="pop3"  <?php echo (($input_protocol == "pop3") ? " selected='selected' " : "") ?>>POP3</option>
                <option value="imap" <?php echo ($input_protocol == "imap") ? "selected='selected' " : "" ?>>IMAP</option>
                <option value="pop3-ssl" <?php echo ($input_protocol == "pop3-ssl") ? "selected='selected' " : "" ?>>POP3-SSL</option>
                <option value="imap-ssl" <?php echo ($input_protocol == "imap-ssl") ? "selected='selected' " : "" ?>>IMAP-SSL</option>
            </select>

        </td>
        </tr>

        <tr>
            <th scope="row"><label for="postie-settings-mail_server_port"><?php _e('Port', 'postie') ?></label></th>
            <td valign="top">
                <input name='postie-settings[mail_server_port]' style="width: 70px;" type="number" min="0" id='postie-settings-mail_server_port' value="<?php echo esc_attr($mail_server_port); ?>" size="6" />
                <p class='description'><?php _e("Standard Ports:", 'postie'); ?><br />
                    <?php _e("POP3", 'postie'); ?>: 110<br />
                    <?php _e("IMAP", 'postie'); ?>: 143<br />
                    <?php _e("IMAP-SSL", 'postie'); ?>: 993 <br />
                    <?php _e("POP3-SSL", 'postie'); ?>: 995 <br />
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Mail Server', 'postie') ?></th>
            <td><input name='postie-settings[mail_server]' type="text" id='postie-settings-mail_server' value="<?php echo esc_attr($mail_server); ?>" size="40" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Mail Userid', 'postie') ?></th>
            <td>
                <input name='postie-settings[mail_userid]' type="text" id='postie-settings-mail_userid' autocomplete='new-password' value="<?php echo esc_attr($mail_userid); ?>" size="40" />
                <p class='description'><?php _e("Note that Postie will read and DELETE all the email in this account. Typically your full email address.", 'postie'); ?><br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Mail Password', 'postie') ?></th>
            <td>
                <input name='postie-settings[mail_password]' type="password" id='postie-settings-mail_password' autocomplete='new-password' value="<?php echo esc_attr($mail_password); ?>" size="40" />
            </td>
        </tr>

        <?php echo PostieAdmin::boolean_select_html(__("Ignore Email Date", 'postie'), 'postie-settings[ignore_email_date]', $ignore_email_date, __("If set to 'Yes' the email date will be ignored and the post will be published with the system time. If set to 'No' the date in the email will be used as the post date.", 'postie')); ?>
        <?php echo PostieAdmin::boolean_select_html(__("Use Postie Time Correction", 'postie'), 'postie-settings[use_time_offset]', $use_time_offset, __("If set to 'Yes' adjust the time according to Postie Time Correction otherwise use the date provided by the email.", 'postie')); ?>

        <tr>
            <th scope="row"><?php _e('Postie Time Correction', 'postie') ?></th>
            <td><input style="width: 70px;" name='postie-settings[time_offset]' type="number" step="0.5" id='postie-settings-time_offset' size="2" value="<?php echo esc_attr($time_offset); ?>" /> 
                <?php
                _e('hours', 'postie');
                ?> 
                <p class='description'><?php printf(__("Should be the same as your normal offset, but this lets you adjust it in cases where that doesn't work.<br>Blog timezone is: %s<br>Blog offset: %d", 'postie'), get_option('timezone_string') == '' ? 'GMT+0' : get_option('timezone_string'), get_option('gmt_offset')); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <?php _e('Check for mail every', 'postie') ?>:
            </th>
            <td>
                <select name='postie-settings[interval]' id='postie-settings-interval'>
                    <option value="weekly" <?php
                    if ($interval == "weekly") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('Once weekly', 'postie') ?>
                    </option>

                    <option value="daily"<?php
                    if ($interval == "daily") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('daily', 'postie') ?>
                    </option>

                    <option value="twohours"<?php
                    if ($interval == "twohours") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 2 hours', 'postie') ?>
                    </option>

                    <option value="hourly" <?php
                    if ($interval == "hourly") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('hourly', 'postie') ?>
                    </option>

                    <option value="twiceperhour" <?php
                    if ($interval == "twiceperhour") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 30 minutes', 'postie') ?>
                    </option>

                    <option value="tenminutes" <?php
                    if ($interval == "tenminutes") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 10 minutes', 'postie') ?>
                    </option>

                    <option value="fiveminutes" <?php
                    if ($interval == "fiveminutes") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 5 minutes', 'postie') ?>
                    </option>

                    <option value="oneminute" <?php
                    if ($interval == "oneminute") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 1 minute', 'postie') ?>
                    </option>

                    <option value="thirtyseconds" <?php
                    if ($interval == "thirtyseconds") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 30 seconds', 'postie') ?>
                    </option>

                    <option value="fifteenseconds" <?php
                    if ($interval == "fifteenseconds") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('every 15 seconds', 'postie') ?>
                    </option>

                    <option value="manual" <?php
                    if ($interval == "manual") {
                        echo "selected='selected'";
                    }
                    ?>><?php _e('check manually', 'postie') ?>
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <?php _e('Maximum number of emails to process', 'postie'); ?>
            </th>
            <td>
                <select name='postie-settings[maxemails]' id='postie-settings-maxemails'>
                    <option value="0" <?php if ($maxemails == '0') echo "selected='selected'"; ?>><?php _e('All', 'postie'); ?></option>
                    <option value="1" <?php if ($maxemails == '1') echo "selected='selected'" ?>>1</option>
                    <option value="2" <?php if ($maxemails == '2') echo "selected='selected'" ?>>2</option>
                    <option value="5" <?php if ($maxemails == '5') echo "selected='selected'" ?>>5</option>
                    <option value="10" <?php if ($maxemails == '10') echo "selected='selected'" ?>>10</option>
                    <option value="25" <?php if ($maxemails == '25') echo "selected='selected'" ?>>25</option>
                    <option value="50" <?php if ($maxemails == '50') echo "selected='selected'" ?>>50</option>
                </select>
            </td>
        </tr>
        <?php
        echo PostieAdmin::boolean_select_html(__("Delete email after posting", 'postie'), 'postie-settings[delete_mail_after_processing]', $delete_mail_after_processing, __("Only set to no for testing purposes", 'postie'));
//echo PostieAdmin::BuildBooleanSelect(__("Ignore mail state", 'postie'), 'postie-settings[ignore_mail_state]', $ignore_mail_state, __("Ignore whether the mails is 'read' or 'unread' If 'No' then only unread messages are processed. IMAP only", 'postie')); 

        echo PostieAdmin::boolean_select_html(__("Enable Error Logging", 'postie'), 'postie-settings[postie_log_error]', $postie_log_error, __("Log error messages to the web server error log.", 'postie'));

        $notification_options = array('(Nobody)', '(All Admins)');
        foreach (get_users(array('role' => 'administrator')) as $user) {
            $notification_options[] = $user->user_login;
        }
        echo PostieAdmin::select_html(__('Notify on Error', 'postie'), 'postie-settings[postie_log_error_notify]', $postie_log_error_notify, $notification_options);

        echo PostieAdmin::boolean_select_html(__("Enable Debug Logging", 'postie'), 'postie-settings[postie_log_debug]', $postie_log_debug, __("Log debug messages to the web server error log.", 'postie'));
        ?>
        </tbody>
    </table>
</div>
