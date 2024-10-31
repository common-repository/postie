<div id="simpleTabs-content-4" class="simpleTabs-content">
    <table class='form-table'>

        <?php
        echo PostieAdmin::boolean_select_html(__("Use First Image as Featured Image", 'postie'), "postie-settings[featured_image]", $featured_image, __("If any images are attached, the first one will be the featured image for the post", 'postie'));
        echo PostieAdmin::boolean_select_html(__("Include Featured Image in Post", 'postie'), "postie-settings[include_featured_image]", $include_featured_image, __("Should the featured image be included in the post.", 'postie'));
        echo PostieAdmin::boolean_select_html(__("Automatically insert image gallery", 'postie'), "postie-settings[auto_gallery]", $auto_gallery, __("If any images are attached, they will automatically be inserted as a gallery. If the 'Preferred Text Type' is 'HTML' this will add a galery in addition to the images in the email.", 'postie'));
        echo PostieAdmin::select_html(__("Gallery Link Type", 'postie'), "postie-settings[auto_gallery_link]", $auto_gallery_link, array(__('Default', 'postie'), __('Post', 'postie'), __('File', 'postie'), __('None', 'postie')), "Select the type of link the gallery should use");
        echo PostieAdmin::boolean_select_html(__("Image/Attachment Location", 'postie'), "postie-settings[images_append]", $images_append, __("Location of attachments. Before or After content. For 'html' content this will only affect attachments that are not inline.", 'postie'), array(__('After', 'postie'), __('Before', 'postie')));
        echo PostieAdmin::boolean_select_html(__("Start Image Count At", 'postie'), "postie-settings[start_image_count_at_zero]", $start_image_count_at_zero, __('For use if using "Image Place Holder Tag" below.', 'postie'), array(__('Start at 0', 'postie'), __('Start at 1', 'postie')));
        echo PostieAdmin::boolean_select_html(__("Use custom image field for images", 'postie'), "postie-settings[custom_image_field]", $custom_image_field, __("When set to 'Yes' no images will appear in the post (other attachment types will be processed normally). Instead the url to the attachment will be put into a custom field named 'image'. Your theme will need logic to display these attachments", 'postie'));
        //echo PostieAdmin::boolean_select_html(__("Create Alternate Image Sizes", 'postie'), "postie-settings[image_resize]", $image_resize, __("Some hosts have issues with image resizing. Use this setting to disable resizing.", 'postie'));
        ?>
        <tr> 
            <th scope="row"><?php _e('Image Place Holder Tag', 'postie') ?></th> 
            <td>
                <input name='postie-settings[image_placeholder]' type="text" id='postie-settings-image_placeholder' value="<?php echo esc_attr($image_placeholder); ?>" size="50" /><br />
                <p class='description'><?php _e("For use in 'plain' messages. The code for inserting an image. I.e. put \"#img1# in your email where you want the first image to show. See also \"Start Image Count At\"", 'postie') ?></p>
            </td> 
        </tr> 
        <tr>
            <th scope="row"><?php _e('Image Template', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-selected_imagetemplate' name='postie-settings[selected_imagetemplate]'
                       value="<?php echo esc_attr($selected_imagetemplate) ?>" />
                <select name='imagetemplateselect' id='imagetemplateselect' 
                        onchange="changeStyle('imageTemplatePreview', 'postie-settings-imagetemplate',
                                        'imagetemplateselect', 'postie-settings-selected_imagetemplate', 'smiling.jpg');" >
                            <?php
                            include(POSTIE_ROOT . '/templates/image_templates.php');
                            $styleOptions = $imageTemplates;
                            $selected = $selected_imagetemplate;
                            foreach ($styleOptions as $key => $value) {
                                if ($key != 'selected') {
                                    if ($key == $selected) {
                                        $select = ' selected=selected ';
                                    } else {
                                        $select = ' ';
                                    }
                                    if ($key == 'custom')
                                        $value = $imagetemplate;
                                    echo '<option' . $select . 'value="' .
                                    esc_attr($value) . '" >' . $key . '</option>';
                                }
                            }
                            ?>
                </select>
                <p class='description'><?php _e('Choose a default template, then customize to your liking in the text box', 'postie'); ?></p>
                <p class='description'><?php _e('Note that this template are only used if the "Preferred Text Type" setting is set to "plain"', 'postie'); ?></p>
                <p class='description'><?php _e('Sizes for thumbnail, medium, and large images can be chosen in the <a href="options-media.php">Media Settings</a>. The samples here use the default sizes, and will not reflect the sizes you have chosen.', 'postie'); ?></p>
                <div style="margin-top: 10px; font-weight: bold;"><?php _e('Preview', 'postie'); ?></div>
                <div id='imageTemplatePreview'></div>
                <textarea onchange='changeStyle("imageTemplatePreview", "postie-settings-imagetemplate", "imagetemplateselect",
                                "postie-settings-selected_imagetemplate", "smiling.jpg", true);' cols='70' rows='7' id='postie-settings-imagetemplate' name='postie-settings[imagetemplate]'>
                          <?php echo esc_attr($imagetemplate) ?>
                </textarea>
                <div class='recommendation'>
                    <ul>
                        <li>{CAPTION} gets replaced with the caption you specified (if any)</li>
                        <li>{FILELINK} gets replaced with the url to the media</li>
                        <li>{FILENAME} gets replaced with the name of the attachment from the email</li>
                        <li>{FILEID} gets replaced with the ID of the media</li>
                        <li>{FILETYPE} The file extension (jpg, png, doc, pdf, etc)</li>
                        <li>{FULL} same as {FILELINK}</li>
                        <li>{HEIGHT} gets replaced with the height of the photo</li>
                        <li>{ID} gets replaced with the post id</li>
                        <li>{IMAGE} same as {FILELINK}</li>
                        <li>{LARGEHEIGHT} gets replaced with the height of a large image</li>
                        <li>{LARGEWIDTH} gets replaced with the width of a large image</li>
                        <li>{LARGE} gets replaced with the url to the large-sized image</li>
                        <li>{MEDIUMHEIGHT} gets replaced with the height of a medium image</li>
                        <li>{MEDIUMWIDTH} gets replaced with the width of a medium image</li>
                        <li>{MEDIUM} gets replaced with the url to the medium-sized image</li>
                        <li>{PAGELINK} gets replaced with the URL of the file in WordPress</li>
                        <li>{RELFILENAME} gets replaced with the relative path to the full-size image</li>
                        <li>{THUMBHEIGHT} gets replaced with the height of a thumbnail image</li>
                        <li>{THUMB} gets replaced with the url to the thumbnail image</li>
                        <li>{THUMBNAIL} same as {THUMB}</li>
                        <li>{THUMBWIDTH} gets replaced with the width of a thumbnail image</li>
                        <li>{TITLE} same as {FILENAME}</li>
                        <li>{URL} same as {FILELINK}</li>
                        <li>{WIDTH} gets replaced with width of the photo</li>
                        <li>{ICON} insert the icon for the attachment (for non-audio/image/video attachments only)</li>
                    </ul>
                </div>
            </td>
        </tr> 
    </table> 
</div> 