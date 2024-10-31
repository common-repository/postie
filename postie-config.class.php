<?php

class PostieConfigOptions {

    const PostType = 'post_type';
    const CategoryRemove = 'category_remove';
    const CategoryMatch = 'category_match';
    const CategoryBracket = 'category_bracket';
    const CategoryDash = 'category_dash';
    const CategoryColon = 'category_colon';
    const PostFormat = 'post_format';
    const ImageResize = 'image_resize';
    const DefaultTitle = 'default_title';
    const TurnAuthorizationOff = 'turn_authorization_off';
    const PreferTextType = 'prefer_text_type';
    const AddWrapperDiv = 'add_wrapper_div';
}

class PostieConfig {

    /**
     *
     * @config array 
     */
    private $config;

    function __construct() {
        $this->config = $this->config_fetch();
    }

    function config_fetch() {
        $config = get_option('postie-settings');
        return $this->validate_settings($config);
    }

    /**
     * 
     * @return array
     */
    function config_read() {
        return $this->config;
    }

    /**
     * validates the config form output, fills in any gaps by using the defaults,
     * and ensures that arrayed items are stored as such
     */
    function validate_settings($in) {
        //DebugEcho("config_ValidateSettings");

        $out = array();

        //DebugDump($in);
        // use the default as a template: 
        // if a field is present in the defaults, we want to store it; otherwise we discard it
        $allowed_keys = $this->defaults();
        foreach ($allowed_keys as $key => $default) {
            if (is_array($in)) {
                $out[$key] = array_key_exists($key, $in) ? $in[$key] : $default;
            } else {
                $out[$key] = $default;
            }
        }

        // some fields are always forced to lower case:
        $lowercase = array('authorized_addresses', 'smtp', 'supported_file_types', 'video1types', 'video2types', 'audiotypes');
        foreach ($lowercase as $field) {
            $out[$field] = ( is_array($out[$field]) ) ? array_map("strtolower", $out[$field]) : strtolower($out[$field]);
        }
        $arrays = $this->arrayed_settings();

        foreach ($arrays as $sep => $fields) {
            foreach ($fields as $field) {
                if (!is_array($out[$field])) {
                    $out[$field] = explode($sep, trim($out[$field]));
                }
                foreach ($out[$field] as $key => $val) {
                    $tst = trim($val);
                    if
                    (empty($tst)) {
                        unset($out[$field][$key]);
                    } else {
                        $out[$field][$key] = $tst;
                    }
                }
            }
        }

        $out['message_encoding'] = 'UTF-8'; //force to UTF-8;

        $this->fix_permission_cron($out);
        return $out;
    }

    /**
     * This function used to handle updating the configuration.
     * @return boolean
     */
    function fix_permission_cron($data) {
        $this->update_permissions($data['role_access']);
        // We also update the cron settings
        PostieInit::postie_cron_hook($data['interval']);
    }

    /**
     * This function handles setting up the basic permissions
     */
    function update_permissions($role_access) {
        global $wp_roles;
        if (is_object($wp_roles)) {
            $admin = $wp_roles->get_role('administrator');
            if (!empty($admin)) {
                $admin->add_cap('config_postie');
                $admin->add_cap('post_via_postie');

                if (!is_array($role_access)) {
                    $role_access = array();
                }
                foreach ($wp_roles->role_names as $roleId => $name) {
                    $role = $wp_roles->get_role($roleId);
                    if ($roleId != 'administrator') {
                        if (array_key_exists($roleId, $role_access)) {
                            $role->add_cap('post_via_postie');
                            //DebugEcho("added $roleId");
                        } else {
                            $role->remove_cap('post_via_postie');
                            //DebugEcho("removed $roleId");
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns a list of config keys that should be arrays
     * @return array
     */
    function arrayed_settings() {
        return array(
            ', ' => array('audiotypes', 'video1types', 'video2types', 'default_post_tags'),
            "\n" => array('smtp', 'authorized_addresses', 'supported_file_types', 'banned_files_list', 'sig_pattern_list'));
    }

    /**
     * return an array of the config defaults
     */
    function defaults() {
        include('templates/audio_templates.php');
        include('templates/image_templates.php');
        include('templates/video1_templates.php');
        include('templates/video2_templates.php');
        include 'templates/general_template.php';
        return array(
            'add_meta' => 'no',
            'admin_username' => 'admin',
            'allow_html_in_body' => true,
            'allow_html_in_subject' => true,
            'allow_subject_in_mail' => true,
            'audiotemplate' => $simple_link,
            'audiotypes' => array('m4a', 'mp3', 'ogg', 'wav', 'mpeg'),
            'authorized_addresses' => array(),
            'banned_files_list' => array(),
            'confirmation_email' => '',
            'convertnewline' => false,
            'converturls' => true,
            'custom_image_field' => false,
            'default_post_category' => NULL,
            'category_match' => true,
            'default_post_tags' => array(),
            'default_title' => "Live From The Field",
            'delete_mail_after_processing' => true,
            'drop_signature' => true,
            'filternewlines' => true,
            'forward_rejected_mail' => true,
            'icon_set' => 'silver',
            'icon_size' => 32,
            'auto_gallery' => false,
            'image_new_window' => false,
            'image_placeholder' => '#img%#',
            'images_append' => true,
            'imagetemplate' => $wordpress_default,
            'imagetemplates' => $imageTemplates,
            'input_protocol' => 'pop3',
            'input_connection' => 'sockets',
            'interval' => 'twiceperhour',
            'mail_server' => NULL,
            'mail_server_port' => 110,
            'mail_userid' => NULL,
            'mail_password' => NULL,
            'maxemails' => 0,
            'message_start' => '',
            'message_end' => '',
            'message_encoding' => 'UTF-8',
            'message_dequote' => true,
            'post_status' => 'publish',
            'prefer_text_type' => 'plain',
            'return_to_sender' => false,
            'role_access' => array(),
            'selected_audiotemplate' => 'simple_link',
            'selected_imagetemplate' => 'wordpress_default',
            'selected_video1template' => 'vshortcode',
            'selected_video2template' => 'simple_link',
            'shortcode' => false,
            'sig_pattern_list' => array('--\s?[\r\n]?', '--\s', '--', '---'),
            'smtp' => array(),
            'start_image_count_at_zero' => false,
            'supported_file_types' => array('application'),
            'turn_authorization_off' => false,
            'time_offset' => get_option('gmt_offset'),
            'video1template' => $simple_link,
            'video1types' => array('mp4', 'mpeg4', '3gp', '3gpp', '3gpp2', '3gp2', 'mov', 'mpeg', 'quicktime'),
            'video2template' => $simple_link,
            'video2types' => array('x-flv'),
            'video1templates' => $video1Templates,
            'video2templates' => $video2Templates,
            'wrap_pre' => 'no',
            'featured_image' => false,
            'include_featured_image' => true,
            'email_tls' => false,
            'post_format' => 'standard',
            'post_type' => 'post',
            'generaltemplates' => $generalTemplates,
            'generaltemplate' => $postie_default,
            'selected_generaltemplate' => 'postie_default',
            'generate_thumbnails' => true,
            'reply_as_comment' => true,
            'force_user_login' => false,
            'auto_gallery_link' => 'default',
            'ignore_mail_state' => false,
            'strip_reply' => true,
            'postie_log_error' => true,
            'postie_log_debug' => false,
            'category_colon' => true,
            'category_dash' => true,
            'category_bracket' => true,
            'prefer_text_convert' => true,
            'category_remove' => true,
            'ignore_email_date' => false,
            'use_time_offset' => false,
            'postie_log_error_notify' => '(All Admins)',
            'image_resize' => true,
            'duplicate_comments' => true,
            'legacy_commands' => true,
            'add_wrapper_div' => true
        );
    }

    /**
     * This function resets all the configuration options to the default
     */
    function reset_to_default() {
        $newconfig = $this->defaults();
        $config = get_option('postie-settings');
        $save_keys = array('mail_password', 'mail_server', 'mail_server_port', 'mail_userid', 'input_protocol', 'input_connection');
        foreach ($save_keys as $key) {
            $newconfig[$key] = $config[$key];
        }
        update_option('postie-settings', $newconfig);
        $this->fix_permission_cron($newconfig);
        return $newconfig;
    }
}
