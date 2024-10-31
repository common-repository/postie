<div id = "simpleTabs-content-3" class = "simpleTabs-content">
    <table class = 'form-table'>
        <tr> 
            <th scope="row"><?php _e('Preferred Text Type', 'postie') ?> </th> 
            <td>
                <select name='postie-settings[prefer_text_type]' id='postie-settings-prefer_text_type'>
                    <?php printf('<option value="plain" %s>' . __('Plain Text', 'postie') . '</option>', ($prefer_text_type == "plain") ? "selected" : "") ?>
                    <?php printf('<option value="html" %s>Html</option>', ($prefer_text_type == "html") ? "selected" : "") ?>
                </select>
            </td> 
        </tr> 
        <?php
        echo PostieAdmin::boolean_select_html(__("Text fallback", 'postie'), "postie-settings[prefer_text_convert]", $prefer_text_convert, __("Use plain if html is missing and vice versa.", 'postie'));
        ?>
        <tr valign = "top">
            <th scope = "row"><?php _e('Default category', 'postie') ?></th>
            <td>
                <?php
                $defaultCat = $default_post_category;
                $args = array('name' => 'postie-settings[default_post_category]', 'hierarchical' => 1, 'selected' => $defaultCat, 'hide_empty' => 0);
                wp_dropdown_categories($args);
                ?>
        </tr>
        <?php
        echo PostieAdmin::boolean_select_html(__("Match short category", 'postie'), "postie-settings[category_match]", $category_match, __("Try to match categories using 'starts with logic' otherwise only do exact matches.<br />Note that custom taxonomies will not be found if this setting is 'No'", 'postie'));

        echo PostieAdmin::boolean_select_html(__("Use colon to match category", 'postie'), "postie-settings[category_colon]", $category_colon);
        echo PostieAdmin::boolean_select_html(__("Use dash to match category", 'postie'), "postie-settings[category_dash]", $category_dash);
        echo PostieAdmin::boolean_select_html(__("Use square bracket to match category", 'postie'), "postie-settings[category_bracket]", $category_bracket, __('See the following article for more information <a href="http://postieplugin.com/faq/override-post-categories/" target="_blank">http://postieplugin.com/faq/override-post-categories/</a>', 'postie'));
        echo PostieAdmin::boolean_select_html(__("Remove matched categories", 'postie'), "postie-settings[category_remove]", $category_remove, __('Typically you want any categories specified on the subject line removed from the post title.'));
        ?>

        <tr valign="top">
            <th scope="row">
                <?php _e('Default tag(s)', 'postie') ?><br />
            </th>
            <td>
                <input type='text' name='postie-settings[default_post_tags]' id='postie-settings-default_post_tags' value='<?php echo esc_attr($default_post_tags) ?>' />
                <p class='description'><?php _e('(optional) separated by commas', 'postie') ?></p>
            </td>
        </tr>

        <tr> 
            <th scope="row"><?php _e('Default Post Status', 'postie') ?> </th> 
            <td>
                <select name='postie-settings[post_status]' id='postie-settings-post_status'>                               
                    <?php
                    $stati = get_post_stati();
                    //DebugEcho($config['post_status']);
                    //DebugDump($stati);
                    foreach ($stati as $status) {
                        $selected = "";
                        if ($config['post_status'] == $status) {
                            $selected = " selected='selected'";
                        }
                        echo "<option value='$status'$selected>$status</option>";
                    }
                    ?>
                </select>               
            </td> 
        </tr> 

        <tr> 
            <th scope="row"><?php _e('Default Post Format', 'postie') ?> </th> 
            <td>
                <select name='postie-settings[post_format]' id='postie-settings-post_format'>
                    <?php
                    $formats = (array) get_theme_support('post-formats');
                    if (is_array($formats[0])) {
                        $formats = $formats[0];
                    } else {
                        $formats = array();
                    }
                    array_unshift($formats, 'standard');
                    foreach ($formats as $format) {
                        $selected = "";
                        if ($config['post_format'] == $format) {
                            $selected = " selected='selected'";
                        }
                        echo "<option value='$format'$selected>$format</option>";
                    }
                    ?>
                </select>               
            </td> 
        </tr> 

        <tr> 
            <th scope="row"><?php _e('Default Post Type', 'postie') ?> </th> 
            <td>
                <select name='postie-settings[post_type]' id='postie-settings-post_type'>
                    <?php
                    $types = get_post_types();
                    //array_unshift($types, "standard");
                    foreach ($types as $type) {
                        $selected = "";
                        if ($config['post_type'] == $type) {
                            $selected = " selected='selected'";
                        }
                        echo "<option value='$type'$selected>$type</option>";
                    }
                    ?>
                </select>               
            </td> 
        </tr> 

        <tr> 
            <th scope="row"><?php _e('Default Title', 'postie') ?> </th> 
            <td>
                <input name='postie-settings[default_title]' type="text" id='postie-settings-default_title' value="<?php echo esc_attr($default_title); ?>" size="50" /><br />
                <p class='description'><?php _e('(optional) only used if no subject is supplied by the email (rare)', 'postie') ?></p>
            </td> 
        </tr> 
        <?php echo PostieAdmin::boolean_select_html(__("Allow Legacy Commands", 'postie'), "postie-settings[legacy_commands]", $legacy_commands, "Process commands such as comments, status, delay, excerptstart, excerptend, date, tags."); ?>

        <?php echo PostieAdmin::boolean_select_html(__("Treat Replies As", 'postie'), "postie-settings[reply_as_comment]", $reply_as_comment, "", array(__('comments', 'postie'), __('new posts', 'postie'))); ?>
        <?php echo PostieAdmin::boolean_select_html(__("Strip Original Content from Replies", 'postie'), "postie-settings[strip_reply]", $strip_reply, "Only applicable if replies are treated as comments"); ?>
        <?php echo PostieAdmin::boolean_select_html(__("Allow Duplicate Comments", 'postie'), "postie-settings[duplicate_comments]", $duplicate_comments, 'WordPress will stop Postie from running if it decides a comment is a duplicate.'); ?>

        <?php echo PostieAdmin::boolean_select_html(__("Forward Rejected Mail", 'postie'), "postie-settings[forward_rejected_mail]", $forward_rejected_mail); ?>
        <?php echo PostieAdmin::boolean_select_html(__("Allow Subject In Mail", 'postie'), "postie-settings[allow_subject_in_mail]", $allow_subject_in_mail, "Enclose the subject between '#' on the very first line. E.g. #this is my subject#"); ?>
        <?php echo PostieAdmin::boolean_select_html(__("Allow HTML In Mail Subject", 'postie'), "postie-settings[allow_html_in_subject]", $allow_html_in_subject); ?>
        <tr> 
            <th scope="row"><?php _e('Text for Message Start', 'postie') ?> </th>
            <td>
                <input name='postie-settings[message_start]' type="text" id='postie-settings-message_start' value="<?php echo esc_attr($message_start); ?>" size="50" /><br />
                <p class='description'><?php _e('(optional) Remove all text from the beginning of the message up to the point where this is found. Note this works best with "Plain" messages.', 'postie') ?></p>
            </td> 
        </tr>
        <tr>
            <th scope="row"><?php _e('Text for Message End', 'postie') ?> </th>
            <td>
                <input name='postie-settings[message_end]' type="text" id='postie-settings-message_end' value="<?php echo esc_attr($message_end); ?>" size="50" /><br />
                <p class='description'><?php _e('(optional) Remove all text from the point this is found to the end of the message. Note this works best with "Plain" messages.', 'postie') ?></p>
            </td>
        </tr>

        <?php
        echo PostieAdmin::boolean_select_html(__("Filter newlines", 'postie'), "postie-settings[filternewlines]", $filternewlines, __("Whether to strip newlines from plain text. Set to no if using markdown or textitle syntax", 'postie'));
        echo PostieAdmin::boolean_select_html(__("Replace newline characters with html line breaks (&lt;br /&gt;)", 'postie'), "postie-settings[convertnewline]", $convertnewline, __("Filter newlines must be turned on for this option to take effect", 'postie'));
        echo PostieAdmin::boolean_select_html(__("Return rejected mail to sender", 'postie'), "postie-settings[return_to_sender]", $return_to_sender);

        $confirmation_options = array('sender' => 'sender', 'admin' => 'administrator(s)', 'both' => 'both', '' => 'nobody');
        foreach (get_users(array('role' => 'administrator')) as $user) {
            if (!array_key_exists($user->user_login, $confirmation_options)) {
                $confirmation_options[$user->user_login] = $user->user_email;
            }
        }
        echo PostieAdmin::select_html2(__('Send post confirmation email to', 'postie'), 'postie-settings[confirmation_email]', $confirmation_email, $confirmation_options);
        echo PostieAdmin::boolean_select_html(__("Automatically convert urls to links", 'postie'), "postie-settings[converturls]", $converturls);
        echo PostieAdmin::boolean_select_html(__("Drop The Signature From Mail", 'postie'), "postie-settings[drop_signature]", $drop_signature);
        echo PostieAdmin::textarea_html(__("Signature Patterns", 'postie'), "postie-settings[sig_pattern_list]", $sig_pattern_list, __("Put each pattern on a separate line. Patterns are <a href='http://regex101.com/' target='_blank'>regular expressions</a> and are put inside '/^{pattern}\s?$/miu'. For HTML content the inner text of each element is evaluated against the pattern. E.g for &lt;p&gt;some test&lt;/p&gt;&lt;p&gt;-- &lt;br&gt;signature&lt;/p&gt; Postie will evaluate 'some text', '-- ' and 'signature' against the pattern(s) and the first time it find a match it will assume that is the signature and remove it and anything below it from the post.", 'postie'));
        echo PostieAdmin::boolean_select_html(__("Add a Wrapper Div", 'postie'), "postie-settings[add_wrapper_div]", $add_wrapper_div, __("Should Post add a &lt;div class='postie'&gt; around each received email? This can allow you to add CSS that applies only to posts created by Postie. If this interfers with your theme you can turn it off."));
        ?>
    </table> 
</div>