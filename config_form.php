<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "postie-admin.class.php");
?>
<div class="wrap"> 
    <h2>
        <a style='text-decoration:none' href='admin.php?page=postie-settings'>
            <?php
            echo '<img src="' . esc_url(plugins_url('images/mail.png', __FILE__)) . '" alt="postie" />';
            _e('Postie Settings', 'postie');
            ?>
        </a>
        <span class="description">(v<?php _e(POSTIE_VERSION, 'postie'); ?>)</span>
    </h2>

    <?php
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'reset':
                $pconfig = new PostieConfig();
                $pconfig->reset_to_default();
                $message = 1;
                break;
            case 'cronless':
                postie_check();
                $message = 1;
                break;
            case 'test':
                $g_postie->test_config();
                exit;
                break;
            case 'runpostie':
                DebugEcho(__("Checking for mail manually", 'postie'));
                postie_get_mail();
                exit;
                break;
            case 'runpostie-debug':
                DebugEcho(__("Checking for mail manually with debug output", 'postie'));
                if (!defined('POSTIE_DEBUG')) {
                    define('POSTIE_DEBUG', true);
                }
                postie_get_mail();
                exit;
                break;
            default:
                $message = 2;
                break;
        }
    }
    global $wpdb, $wp_roles; //don't remove - used in included files

    $pconfig = new PostieConfig();
    $config = $pconfig->config_read();
    if (empty($config)) {
        $config = $pconfig->reset_to_default();
    }

    $arrays = $pconfig->arrayed_settings();
    // some fields are stored as arrays, because that makes back-end processing much easier
    // and we need to convert those fields to strings here, for the options form
    foreach ($arrays as $sep => $fields) {
        foreach ($fields as $field) {
            $config[$field] = implode($sep, $config[$field]);
        }
    }
    extract($config);
    if (!isset($maxemails)) {
        DebugEcho(__("New setting: maxemails", 'postie'));
        $maxemails = 0;
    }
    if (!isset($category_match)) {
        $category_match = true;
    }

    if ($interval == 'manual') {
        wp_clear_scheduled_hook('check_postie_hook');
    }

    $messages[1] = __("Configuration successfully updated!", 'postie');
    $messages[2] = __("Error - unable to save configuration", 'postie');
    ?>
    <?php if (isset($_GET['message'])) : ?>
        <div class="updated"><p><?php _e($messages[$_GET['message']], 'postie'); ?></p></div>
    <?php endif; ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <!-- main content -->
            <div id="post-body-content" style="position: relative;">
                <div class="meta-box-sortables ui-sortable">
                    <div class="inside">
                        <form name="postie-options" method="post" action='options.php' autocomplete="off">
                            <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                            <input style="display:none" type="text" name="fakeusernameremembered"/>
                            <input style="display:none" type="password" name="fakepasswordremembered"/>

                            <?php settings_fields('postie-settings'); ?>
                            <input type="hidden" name="action" value="config" />
                            <div id="simpleTabs">
                                <h2 class="nav-tab-wrapper">
                                    <a href="#" id="simpleTabs-nav-1" data-tab="1" class="nav-tab nav-tab-active"><?php _e('Mailserver', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-2" data-tab="2" class="nav-tab"><?php _e('User', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-3" data-tab="3" class="nav-tab"><?php _e('Message', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-4" data-tab="4" class="nav-tab"><?php _e('Image', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-5" data-tab="5" class="nav-tab"><?php _e('Video and Audio', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-6" data-tab="6" class="nav-tab"><?php _e('Attachments', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-7" data-tab="7" class="nav-tab"><?php _e('Support', 'postie') ?></a>
                                </h2>

                                <?php include 'config_form_server.php'; ?>

                                <?php include 'config_form_user.php'; ?>

                                <?php include 'config_form_message.php'; ?>

                                <?php include 'config_form_image.php'; ?>

                                <?php include 'config_form_video.php'; ?>

                                <?php include 'config_form_attachments.php'; ?>

                                <?php include 'config_form_support.php'; ?>
                            </div>

                            <p class="submit" style="clear: both;">
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="page_options" value="postie-settings" />
                                <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" class="button button-primary" />
                            </p>
                        </form> 
                    </div>
                </div>
            </div>

            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox">
                        <h3 class="hndle ui-sortable-handle"><span>Actions</span></h3>
                        <div class="inside">
                            <div class="submitbox">
                                <p><?php _e("To run the check mail script manually", 'postie'); ?></p>
                                <form name="postie-options" method='post'> 
                                    <input type="hidden" name="action" value="runpostie" />
                                    <input name="Submit" value="<?php _e("Process Email", 'postie'); ?>" type="submit" class='button'>
                                </form>

                                <p><?php _e("To run the check mail script manually with full debug output", 'postie'); ?></p>
                                <form name="postie-options" method='post'> 
                                    <input type="hidden" name="action" value="runpostie-debug" />
                                    <input name="Submit" value="<?php _e("Debug", 'postie'); ?>" type="submit" class='button'>
                                </form>

                                <p><?php _e("Test your configuration (save first)", 'postie'); ?></p>
                                <form name="postie-options" method="post">
                                    <input type="hidden" name="action" value="test" />
                                    <input name="Submit" value="<?php _e("Test Config", 'postie'); ?>" type="submit" class='button'>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle ui-sortable-handle"><span>Getting Started</span></h3>
                        <div class="inside">
                            <p>Be sure and check out the <a href="http://postieplugin.com/getting-started/" target="_blank">getting started</a> guide.</p>
                            <p>Please use the Postie <a href="https://wordpress.org/support/plugin/postie" target="_blank">support forums</a> if you need help.</p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle ui-sortable-handle"><span>AddOns</span></h3>
                        <div class="inside">
                            <p>There are a number of different AddOns available to extend Postie's functionality.
                                See <a href='http://postieplugin.com/add-ons/' target='_blank'>the list</a> for more information.</p>                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle ui-sortable-handle"><span>Donations</span></h3>

                        <div class="inside">
                            <p style="font-weight: bolder; margin-top: 0px; margin-bottom: 2px;"><?php _e("Please Donate, Every $ Helps!", 'postie'); ?></p>
                            <p style="margin-top: 0;margin-bottom: 2px;"><?php _e("Your generous donation allows me to continue developing Postie for the WordPress community.", 'postie'); ?></p>
                            <form style="" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                <input type="hidden" name="cmd" value="_s-xclick">
                                <input type="hidden" name="hosted_button_id" value="HPK99BJ88V4C2">
                                <div style="text-align:center;">
                                    <input style="border: none; margin: 0;" type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            <br class="clear">
        </div>
    </div>
</div>

<?php
$iconDir = esc_url(plugins_url() . '/postie/icons');
//print_r($config);
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.simpleTabs-content').hide();
        jQuery("#simpleTabs-content-1").show();

        jQuery(".nav-tab").click(function (event) {
            jQuery(".nav-tab").removeClass('nav-tab-active');
            jQuery(event.currentTarget).addClass('nav-tab-active');
            jQuery('.simpleTabs-content').hide();
            tab = jQuery(event.currentTarget).data('tab');
            jQuery("#simpleTabs-content-" + tab).show();
        });

        jQuery("#simpleTabs-nav-7").click(function () {
            jQuery.get('http://postieplugin.com/feed/?post_type=download', function (data) {
                //console.log(data);
                var h = '';
                jQuery(data).find("item").each(function () {
                    var t = jQuery(this).find("title").text();
                    if (t != 'Donation') {
                        h += "<div style='float: left; width: 300px;'>";
                        h += "<h4 class='title'><a href='" + jQuery(this).find("link").text() + "' target='_blank'>" + t + "</a></h4>";
                        var d = jQuery(this).find("description").text();
                        if ((i = d.indexOf('<p class="more')) != -1) {
                            d = d.substring(0, i);
                        } else if ((i = d.indexOf('<p>The post <a')) != -1) {
                            d = d.substring(0, i);
                        }
                        h += "<div>" + d + "</div>";
                        h += "</div>";
                    }
                });
                jQuery("#postie-addons").html(h);
            });
        });
    });

    function changeIconSet(selectBox, size) {
        var iconSet = document.getElementById('postie-settings-icon_set');
        var iconSize = document.getElementById('postie-settings-icon_size');
        var preview = document.getElementById('postie-settings-attachment_preview');
        var iconDir = '<?php echo $iconDir ?>/';
        if (size == true) {
            var hiddenInput = iconSize
        } else {
            var hiddenInput = iconSet;
        }
        for (i = 0; i < selectBox.options.length; i++) {
            if (selectBox.options[i].selected == true) {
                hiddenInput.value = selectBox.options[i].value;
            }
        }
        var fileTypes = new Array('doc', 'pdf', 'xls', 'default');
        preview.innerHTML = '';
        for (j = 0; j < fileTypes.length; j++) {
            preview.innerHTML += "<img src='" + iconDir + iconSet.value + '/' +
                    fileTypes[j] + '-' + iconSize.value + ".png' />";
        }
    }

    function changeStyle(preview, template, select, selected, sample, custom) {
        var preview = document.getElementById(preview);
        var pageStyles = document.getElementById(select);
        var selectedStyle;
        var hiddenStyle = document.getElementById(selected);
        var pageStyle = document.getElementById(template);
        if (custom == true) {
            selectedStyle = pageStyles.options[pageStyles.options.length - 1];
            selectedStyle.value = pageStyle.value;
            selectedStyle.selected = true;
        } else {
            for (i = 0; i < pageStyles.options.length; i++) {
                if (pageStyles.options[i].selected == true) {
                    selectedStyle = pageStyles.options[i];
                }
            }
        }
        hiddenStyle.value = selectedStyle.innerHTML
        var previewHTML = selectedStyle.value;
        var fileLink = '<?php echo $templateDir ?>/' + sample;
        var thumb = '<?php echo $templateDir ?>/' + sample.replace(/\.jpg/, '-150x150.jpg');
        var medium = '<?php echo $templateDir ?>/' + sample.replace(/\.jpg/, '-300x200.jpg');
        var large = '<?php echo $templateDir ?>/' + sample.replace(/\.jpg/, '-1024x682.jpg');
        var pagelink = '<?php echo get_option("siteurl") ?>' + '/?attachment_id=9999';
        var fileType = 'mp4';
        previewHTML = previewHTML.replace(/{FILELINK}/g, fileLink);
        previewHTML = previewHTML.replace(/{FULL}/g, fileLink);
        previewHTML = previewHTML.replace(/{IMAGE}/g, fileLink);
        previewHTML = previewHTML.replace(/{FILENAME}/, sample);
        previewHTML = previewHTML.replace(/{FILETYPE}/, fileType);
        previewHTML = previewHTML.replace(/{PAGELINK}/, pagelink);
        previewHTML = previewHTML.replace(/{RELFILENAME}/, sample);
        previewHTML = previewHTML.replace(/{THUMB(NAIL|)}/, thumb);
        previewHTML = previewHTML.replace(/{MEDIUM}/, medium);
        previewHTML = previewHTML.replace(/{LARGE}/, large);
        previewHTML = previewHTML.replace(/{HEIGHT}/, 800);
        previewHTML = previewHTML.replace(/{WIDTH}/, 1200);
        previewHTML = previewHTML.replace(/{THUMBWIDTH}/, 150);
        previewHTML = previewHTML.replace(/{THUMBHEIGHT}/, 150);
        previewHTML = previewHTML.replace(/{MEDIUMWIDTH}/, 300);
        previewHTML = previewHTML.replace(/{MEDIUMHEIGHT}/, 200);
        previewHTML = previewHTML.replace(/{LARGEWIDTH}/, 1024);
        previewHTML = previewHTML.replace(/{LARGEHEIGHT}/, 682);
        previewHTML = previewHTML.replace(/{ID}/, 9999);
        previewHTML = previewHTML.replace(/{FILEID}/, 9999);
        previewHTML = previewHTML.replace(/{POSTTITLE}/g, 'Post title');
        previewHTML = previewHTML.replace(/{CAPTION}/g, 'Spencer smiling');
        preview.innerHTML = previewHTML;
        pageStyle.value = selectedStyle.value;
    }

    function showAdvanced(advancedId, arrowId) {
        var advanced = document.getElementById(advancedId);
        var arrow = document.getElementById(arrowId);
        if (advanced.style.display == 'none') {
            advanced.style.display = 'block';
            arrow.innerHTML = '&#9660;';
        } else {
            advanced.style.display = 'none';
            arrow.innerHTML = '&#9654;';
        }
    }

    changeStyle('imageTemplatePreview', 'postie-settings-imagetemplate', 'imagetemplateselect', 'postie-settings-selected_imagetemplate', 'smiling.jpg', false);
    changeStyle('audioTemplatePreview', 'postie-settings-audiotemplate', 'audiotemplateselect', 'postie-settings-selected_audiotemplate', 'funky.mp3', false);
    changeStyle('video1TemplatePreview', 'postie-settings-video1template', 'video1templateselect', 'postie-settings-selected_video1template', 'hi.mp4', false);
    changeStyle('video2TemplatePreview', 'postie-settings-video2template', 'video2templateselect', 'postie-settings-selected_video2template', 'hi.flv', false);
    changeIconSet(document.getElementById('icon_set_select'));
</script>
