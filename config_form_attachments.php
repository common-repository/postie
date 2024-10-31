<div id="simpleTabs-content-6" class="simpleTabs-content">
    <table class='form-table'>
        <?php echo PostieAdmin::textarea_html(__("Supported MIME Types", 'postie'), "postie-settings[supported_file_types]", $supported_file_types, __("Add just the type (not the subtype). Text, Video, Audio, Image and Multipart are always supported. Put each type on a single line", 'postie')); ?>
        <?php echo PostieAdmin::textarea_html(__("Banned File Names", 'postie'), "postie-settings[banned_files_list]", $banned_files_list, __("Put each file name on a single line. Files matching this list will never be posted to your blog. You can use wildcards such as *.xls, or *.* for all files", 'postie')); ?>

        <tr>
            <th scope='row'><?php _e('Attachment icon set', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-icon_set' name='postie-settings[icon_set]'
                       value="<?php echo esc_attr($icon_set) ?>" />

                <?php
                $icon_sets = array('silver', 'black', 'white', 'metro', 'custom', 'none');
                $icon_sizes = array(32, 48, 64);
                ?>
                <select name='icon_set_select' id='icon_set_select'  onchange="changeIconSet(this);" >
                    <?php
                    $styleOptions = $icon_sets;
                    $selected = $icon_set;
                    foreach ($styleOptions as $key) {
                        if ($key != 'selected') {
                            if ($key == $selected) {
                                $select = ' selected=selected ';
                            } else {
                                $select = ' ';
                            }
                            echo '<option' . $select . 'value="' . esc_attr($key) . '" >' . $key . '</option>';
                        }
                    }
                    ?>
                </select>
                <div id='postie-settings-attachment_preview'></div>
            </td>
        </tr>
        <tr>
            <th scope='row'><?php _e('Attachment icon size (in pixels)', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-icon_size' name='postie-settings[icon_size]'
                       value="<?php echo esc_attr($icon_size) ?>" />
                <select name='icon_size_select' id='icon_size_select' onchange="changeIconSet(this, true);" >
                    <?php
                    $styleOptions = $icon_sizes;
                    $selected = $icon_size;
                    foreach ($styleOptions as $key) {
                        if ($key != 'selected') {
                            if ($key == $selected) {
                                $select = ' selected=selected ';
                            } else {
                                $select = ' ';
                            }
                            echo '<option' . $select . 'value="' . esc_attr($key) . '" >' . $key . '</option>';
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope='row'><?php _e('Attachment template', 'postie') ?>:<br />
            </th>
            <td>
                <input type='hidden' id='postie-settings-selected_generaltemplate' name='postie-settings[selected_generaltemplate]'
                       value="<?php echo esc_attr($selected_generaltemplate) ?>" />
                <select name='generaltemplateselect' id='generaltemplateselect' 
                        onchange="changeStyle('generalTemplatePreview', 'postie-settings-generaltemplate',
                                        'generaltemplateselect', 'postie-settings-selected_generaltemplate', 'interesting_document.doc', false);" >
                            <?php
                            include(POSTIE_ROOT . '/templates/general_template.php');
                            $styleOptions = $generalTemplates;
                            $selected = $selected_generaltemplate;
                            foreach ($styleOptions as $key => $value) {
                                if ($key != 'selected') {
                                    if ($key == $selected) {
                                        $select = ' selected="selected" ';
                                    } else {
                                        $select = ' ';
                                    }
                                    if ($key == 'custom') {
                                        $value = $generaltemplate;
                                    }
                                    echo '<option' . $select . 'value="' . esc_attr($value) . '" >' . $key . '</option>';
                                }
                            }
                            ?>
                </select>
                <p class='description'><?php _e('Choose a default template, then customize to your liking in the text box', 'postie') ?></p>
                <p class='description'><?php _e('Note that this template are only used if the attachment is not "inline" or if the email type is "plain"', 'postie'); ?></p>

                <div style="margin-top: 10px; font-weight: bold;">
                    <?php _e('Preview', 'postie'); ?>
                </div>
                <div id='generalTemplatePreview'></div>
                <textarea onchange="changeStyle('generalTemplatePreview', 'postie-settings-generaltemplate', 'generaltemplateselect', 'postie-settings-selected_generaltemplate', 'interesting_document.doc', true);" 
                          cols='70' rows='7' 
                          id='postie-settings-generaltemplate'
                          name='postie-settings[generaltemplate]'><?php echo esc_attr($generaltemplate) ?></textarea>
            </td>
        </tr>
    </table> 
</div>