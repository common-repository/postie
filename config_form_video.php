<div id="simpleTabs-content-5" class="simpleTabs-content">
    <table class='form-table'>
        <tr>
            <th scope='row'><?php _e('Video template 1', 'postie') ?></th>
            <?php $templateDir = esc_url(plugins_url() . '/postie/templates'); ?>
            <td>
                <input type='hidden' id='postie-settings-selected_video1template' name='postie-settings[selected_video1template]'
                       value="<?php echo esc_attr($selected_video1template) ?>" />
                <select name='video1templateselect' id='video1templateselect' 
                        onchange="changeStyle('video1TemplatePreview', 'postie-settings-video1template', 'video1templateselect', 'postie-settings-selected_video1template', 'hi.mp4');" />
                        <?php
                        include(POSTIE_ROOT . '/templates/video1_templates.php');
                        $styleOptions = $video1Templates;
                        $selected = $selected_video1template;
                        foreach ($styleOptions as $key => $value) {
                            if ($key != 'selected') {
                                if ($key == $selected) {
                                    $select = ' selected=selected ';
                                } else {
                                    $select = ' ';
                                }
                                if ($key == 'custom') {
                                    $value = $video1template;
                                }
                                echo '<option' . $select . 'value="' .
                                esc_attr($value) . '" >' . $key . '</option>';
                            }
                        }
                        ?>
                </select>
                <p class='description'><?php _e('Choose a default template, then customize to your liking in the text box', 'postie') ?></p>
                <p class='description'><?php _e('Note that this template are only used if the video is not "inline" or the "Preferred Text Type" setting is set to "plain"', 'postie'); ?></p>

                <div style="margin-top: 10px; font-weight: bold;"><?php _e('Preview', 'postie'); ?></div>
                <div id='video1TemplatePreview'></div>
                <textarea onchange="changeStyle('video1TemplatePreview', 'postie-settings-video1template',
                                'video1templateselect', 'postie-settings-selected_video1template', 'hi.mp4', true);" cols='70' rows='7' id='postie-settings-video1template'
                          name='postie-settings[video1template]'><?php echo esc_attr($video1template) ?></textarea>
            </td>
        </tr>
        <tr> 
            <th scope="row"><?php _e('Video 1 file extensions', 'postie') ?></th> 
            <td>
                <br/><input name='postie-settings[video1types]' type="text" id='postie-settings-video1types'
                            value="<?php if ($video1types != '') echo esc_attr($video1types); ?>" size="40" />                
                <p class='description'>
                    <?php _e('Use video template 1 for files with these extensions (separated by commas)', 'postie') ?></p>
            </td> 
        </tr> 
        <tr><td colspan="2"><hr /></td></tr>
        <tr>
            <th scope='row'><?php _e('Video template 2', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-selected_video2template' name='postie-settings[selected_video2template]'
                       value="<?php echo esc_attr($selected_video2template) ?>" />
                <select name='video2templateselect' id='video2templateselect' 
                        onchange="changeStyle('video2TemplatePreview', 'postie-settings-video2template','video2templateselect', 'postie-settings-selected_video2template', 'hi.flv');" >
                            <?php
                            include(POSTIE_ROOT . '/templates/video2_templates.php');
                            $styleOptions = $video2Templates;
                            $selected = $selected_video2template;
                            foreach ($styleOptions as $key => $value) {
                                if ($key != 'selected') {
                                    if ($key == $selected) {
                                        $select = ' selected=selected ';
                                    } else {
                                        $select = ' ';
                                    }
                                    if ($key == 'custom') {
                                        $value = $video2template;
                                    }
                                    echo '<option' . $select . 'value="' . esc_attr($value) . '" >' . $key . '</option>';
                                }
                            }
                            ?>
                </select>
                <p class='description'><?php _e('Choose a default template, then customize to your liking in the text box', 'postie') ?></p>
                <p class='description'><?php _e('Note that this template are only used if the video is not "inline" or the "Preferred Text Type" setting is set to "plain"', 'postie'); ?></p>

                <div style="margin-top: 10px; font-weight: bold;"><?php _e('Preview', 'postie'); ?></div>
                <div id='video2TemplatePreview'></div>
                <textarea onchange="changeStyle('video2TemplatePreview', 'postie-settings-video2template',
                                'video2templateselect', 'postie-settings-selected_video2template', 'hi.flv', true);" cols='70' rows='7' id='postie-settings-video2template'
                          name='postie-settings[video2template]'>
                              <?php echo esc_attr($video2template) ?>
                </textarea>
            </td>
        </tr>
        <tr> 
            <th scope="row"><?php _e('Video 2 file extensions', 'postie') ?></th> 
            <td>
                <br/><input name='postie-settings[video2types]' type="text" id='postie-settings-video2types'
                            value="<?php if ($video2types != '') echo esc_attr($video2types); ?>" size="40" />                
                <p class='description'>
                    <?php _e('Use video template 2 for files with these extensions (separated by commas)', 'postie') ?></p>
            </td> 
        </tr> 
        <tr><td colspan="2"><hr /></td></tr>
        <tr>
            <th scope='row'><?php _e('Audio template', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-selected_audiotemplate' name='postie-settings[selected_audiotemplate]'
                       value="<?php echo esc_attr($selected_audiotemplate) ?>" />
                <select name='audiotemplateselect' id='audiotemplateselect' 
                        onchange="changeStyle('audioTemplatePreview', 'postie-settings-audiotemplate', 'audiotemplateselect', 'postie-settings-selected_audiotemplate', 'funky.mp3', false);" >
                            <?php
                            include(POSTIE_ROOT . '/templates/audio_templates.php');
                            $styleOptions = $audioTemplates;
                            $selected = $selected_audiotemplate;
                            foreach ($styleOptions as $key => $value) {
                                if ($key != 'selected') {
                                    if ($key == $selected) {
                                        $select = ' selected=selected ';
                                    } else {
                                        $select = ' ';
                                    }
                                    if ($key == 'custom') {
                                        $value = $audiotemplate;
                                    }
                                    echo '<option' . $select . 'value="' .
                                    esc_attr($value) . '" >' . $key . '</option>';
                                }
                            }
                            ?>
                </select>
                <p class='description'><?php _e('Choose a default template, then customize to your liking in the text box', 'postie') ?></p>
                <p class='description'><?php _e('Note that this template are only used if the "Preferred Text Type" setting is set to "plain"', 'postie'); ?></p>

                <div style="margin-top: 10px; font-weight: bold;"><?php _e('Preview', 'postie'); ?></div>
                <div id='audioTemplatePreview'></div>
                <textarea onchange="changeStyle('audioTemplatePreview', 'postie-settings-audiotemplate', 'audiotemplateselect', 'postie-settings-selected_audiotemplate', 'funky.mp3', true);" 
                          cols='70' rows='7' id='postie-settings-audiotemplate' name='postie-settings[audiotemplate]'><?php echo esc_attr($audiotemplate) ?></textarea>
            </td>
        </tr>
        <tr> 
            <th scope="row"><?php _e('Audio file extensions', 'postie') ?></th> 
            <td>
                <input name='postie-settings[audiotypes]' type="text" id='postie-settings-audiotypes' value="<?php echo esc_attr($audiotypes); ?>" size="40" />
                <p class='description'><?php _e('Use the audio template for files with these extensions (separated by commas)', 'postie') ?></p>
            </td> 
        </tr> 
    </table> 
</div>