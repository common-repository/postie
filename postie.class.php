<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fUnexpectedException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fExpectedException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fConnectivityException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fValidationException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fProgrammerException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fEnvironmentException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fCore.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fMailbox.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fEmail.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pConnection.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pCurlConnection.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pSocketConnection.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pMailServer.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pImapMailServer.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pPop3MailServer.php");

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php'); //wp_tempnam()

if (!function_exists('file_get_html')) {
    //DebugEcho('Including Postie simple_html_dom');
    require_once (plugin_dir_path(__FILE__) . 'lib/simple_html_dom.php');
} else {
    //DebugEcho('non-Postie simple_html_dom already loaded');
}

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "postie-filters.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "postie-tags.php");

class Postie {

    function load_html($text) {
        return str_get_html($text, true, true, DEFAULT_TARGET_CHARSET, false);
    }

    function return_false() {
        return false;
    }

    function get_mail() {
        $config = postie_config_read();
        if (true == $config['postie_log_error'] || (defined('POSTIE_DEBUG') && true == POSTIE_DEBUG)) {
            add_action('postie_log_error', array($this, 'log_error'));
        }
        if (true == $config['postie_log_debug'] && !defined('POSTIE_DEBUG')) {
            define('POSTIE_DEBUG', true);
        }
        if (true == $config['postie_log_debug'] || (defined('POSTIE_DEBUG') && true == POSTIE_DEBUG)) {
            add_action('postie_log_debug', array($this, 'log_debug'));
        }
        if (true == $config['duplicate_comments']) {
            DebugEcho("enabling duplicate comments");
            add_filter('duplicate_comment_id', array($this, 'return_false'));
        }
        add_filter('intermediate_image_sizes_advanced', array($this, 'intermediate_image_sizes_advanced'));

        DebugEcho("doing postie_session_start");
        do_action('postie_session_start');

        DebugEcho('Starting mail fetch');
        DebugEcho('WordPress datetime: ' . current_time('mysql'));

        $wp_content_path = dirname(dirname(dirname(__FILE__)));
        DebugEcho("wp_content_path: $wp_content_path");
        if (file_exists($wp_content_path . DIRECTORY_SEPARATOR . 'filterPostie.php')) {
            DebugEcho("found filterPostie.php in $wp_content_path");
            include_once ($wp_content_path . DIRECTORY_SEPARATOR . 'filterPostie.php');
        }

        $this->postie_environment();
        $this->postie_environment_encoding();

        if (function_exists('memory_get_usage')) {
            DebugEcho(__("memory at start of email processing: ", 'postie') . memory_get_usage());
        }

        if (has_filter('postie_post')) {
            echo "Postie: filter 'postie_post' is depricated in favor of 'postie_post_before'";
        }

        //don't output the password
        DebugEcho("postie configuration");
        $tmp_config = $config;
        unset($tmp_config['mail_password']);
        DebugDump($tmp_config);

        $conninfo = $this->connection_info($config);

        $this->fetch_mail($conninfo['mail_server'], $conninfo['mail_port'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_protocol'], $conninfo['email_delete_after_processing'], $conninfo['email_max']);

        DebugEcho("doing postie_session_end");
        do_action('postie_session_end');

        if (function_exists('memory_get_usage')) {
            DebugEcho('memory at end of email processing: ' . memory_get_usage());
        }
    }

    function intermediate_image_sizes_advanced($sizes) {
        $config = postie_config_read();
        if ($config[PostieConfigOptions::ImageResize]) {
            DebugEcho('intermediate_image_sizes_advanced');
            DebugDump($sizes);
            return $sizes;
        }

        DebugEcho('intermediate_image_sizes_advanced: None');
        return false;
    }

    function show_filters_for($hook = '') {
        global $wp_filter;
        if (empty($hook) || !isset($wp_filter[$hook])) {
            DebugEcho("No registered filters for $hook");
            return;
        }
        DebugEcho("Registered filters for $hook");
        //DebugDump($wp_filter[$hook]->callbacks);
    }

    function save_email_debug($raw, $email) {
        if ($this->is_debugmode()) {
            //DebugDump($email);
            //DebugDump($mimeDecodedEmail);

            $dname = POSTIE_ROOT . DIRECTORY_SEPARATOR . 'test_emails' . DIRECTORY_SEPARATOR;
            if (is_dir($dname)) {
                $mid = $email['headers']['message-id'];
                if (empty($mid)) {
                    $mid = uniqid();
                }
                $fname = $dname . sanitize_file_name($mid);

                file_put_contents($fname . '-raw.txt', $raw);
                file_put_contents($fname . '.txt', $email['text']);
                file_put_contents($fname . '.html', $email['html']);
            }
        }
    }

    function get_mailbox($server, $port, $email, $password, $protocol, $config) {
        $protocol = strtolower($protocol);
        if ($protocol == 'imap' || $protocol == 'imap-ssl') {
            $type = 'imap';
        } else {
            $type = 'pop3';
        }
        $connectiontype = $config['input_connection'];
        if ($connectiontype == 'curl') {
            $conn = new pCurlConnection($type, trim($server), $email, $password, $port, ($protocol == 'imap-ssl' || $protocol == 'pop3-ssl'));
        } else {
            $conn = new pSocketConnection($type, trim($server), $email, $password, $port, ($protocol == 'imap-ssl' || $protocol == 'pop3-ssl'));
        }

        if ($type == 'imap') {
            $srv = new pImapMailServer($conn);
        } else {
            $srv = new pPop3MailServer($conn);
        }

        $mailbox = new fMailbox($type, $conn, $srv);
        return $mailbox;
    }

    /**
     * This function handles determining the protocol and fetching the mail
     */
    function fetch_mail($server, $port, $email, $password, $protocol, $deleteMessages, $maxemails) {

        $emails = array();
        if (!$server || !$port || !$email) {
            EchoError("Missing Configuration For Mail Server");
            return $emails;
        }

        DebugEcho("fetch_mail: Connecting to $server:$port ($protocol)");

        $config = postie_config_read();

        try {
            $mailbox = $this->get_mailbox($server, $port, $email, $password, $protocol, $config);
            $messages = $mailbox->listMessages($maxemails);

            DebugEcho(sprintf(__("fetch_mail: There are %d messages to process", 'postie'), count($messages)));

            DebugDump($messages);

            $message_number = 0;
            foreach ($messages as $message) {
                $config = postie_config_read(); //refresh config in case PostieMessage altered it

                $message_number++;
                DebugEcho("fetch_mail: $message_number: ------------------------------------");
                DebugEcho("fetch_mail: fetch {$message['uid']}");

                $email = new PostieMessage($mailbox->fetchMessage($message['uid']), $config);

                if ($email->is_email_empty()) {
                    $message = __('Dang, message is empty!', 'postie');
                    EchoError("fetch_mail: $message_number: ");
                    DebugDump($message);
                    continue;
                } else if ($email->is_email_read()) {
                    $message = __("Message is already marked 'read'.", 'postie');
                    DebugEcho("fetch_mail: $message_number");
                    DebugDump($message);
                    continue;
                }

                $email->preprocess();
                $email->process();
                $email->postprocess();

                DebugEcho("fetch_mail: $message_number: processed");

                if ($deleteMessages) {
                    DebugEcho("fetch_mail: deleting {$message['uid']}");
                    $mailbox->deleteMessages($message['uid']);
                }
            }

            DebugEcho("fetch_mail: closing connection");
            $mailbox->close();

            DebugEcho("Mail fetch complete, $message_number emails");
        } catch (Exception $e) {
            EchoError("fetch_mail: " . $e->getMessage());
        }
    }

    function postie_environment_encoding($force_display = false) {
        $default_charset = ini_get('default_charset');
        if (version_compare(phpversion(), '5.6.0', '<')) {
            if (empty($default_charset)) {
                DebugEcho("default_charset: WARNING no default_charset set see http://php.net/manual/en/ini.core.php#ini.default-charset", true);
            } else {
                DebugEcho("default_charset: $default_charset", $force_display);
            }
        } else {
            if (empty($default_charset)) {
                DebugEcho("default_charset: UTF-8 (default)", $force_display);
            } else {
                DebugEcho("default_charset: $default_charset", $force_display);
            }
        }

        if (defined('DB_CHARSET')) {
            DebugEcho("DB_CHARSET: " . DB_CHARSET, $force_display);
        } else {
            DebugEcho("DB_CHARSET: undefined (utf8)", $force_display);
        }

        if (defined('DB_COLLATE')) {
            $db_collate = DB_COLLATE;
            if (empty($db_collate)) {
                DebugEcho("DB_COLLATE: database default", $force_display);
            } else {
                DebugEcho("DB_COLLATE: " . DB_COLLATE, $force_display);
            }
        }

        DebugEcho("WordPress encoding: " . esc_attr(get_option('blog_charset')), $force_display);
    }

    function postie_environment($force_display = false) {
        global $g_postie_init;
        global $wpdb;

        DebugEcho("OS: " . PHP_OS, $force_display);
        DebugEcho("PHP version: " . phpversion(), $force_display);
        DebugEcho("PHP error_log: " . ini_get('error_log'), $force_display);
        DebugEcho("PHP log_errors: " . (ini_get('log_errors') ? 'On' : 'Off'), $force_display);
        DebugEcho("PHP get_temp_dir: " . get_temp_dir(), $force_display);
        DebugEcho("PHP disable_functions: " . ini_get('disable_functions'), $force_display);
        if (function_exists('curl_version')) {
            $cv = curl_version();
            DebugEcho("PHP cURL version: " . $cv['version'], $force_display);
        }

        DebugEcho("PHP Multibyte String support: " . (function_exists('mb_detect_encoding') ? 'yes' : 'No'), $force_display);

        DebugEcho("MySQL Version: " . $wpdb->db_version(), $force_display);
        DebugEcho("MySQL client: " . ($wpdb->use_mysqli ? 'mysqli' : 'mysql'), $force_display);
        $charset = $wpdb->get_col_charset($wpdb->posts, 'post_content');
        DebugEcho("$wpdb->posts charset: $charset", $force_display);

        DebugEcho("WordPress Version: " . get_bloginfo('version'), $force_display);
        if (defined('MULTISITE') && MULTISITE) {
            DebugEcho("WordPress Multisite", $force_display);
            DebugEcho("network_home_url(): " . network_home_url(), $force_display);
        } else {
            DebugEcho("WordPress Singlesite", $force_display);
        }
        DebugEcho("WP_TEMP_DIR: " . (defined('WP_TEMP_DIR') ? WP_TEMP_DIR : '(none)'), $force_display);
        DebugEcho("WP_HOME: " . (defined('WP_HOME') ? WP_HOME : '(none)'), $force_display);
        DebugEcho("home_url(): " . home_url(), $force_display);
        DebugEcho("WP_SITEURL: " . (defined('WP_SITEURL') ? WP_SITEURL : '(none)'), $force_display);
        DebugEcho("site_url(): " . site_url(), $force_display);

        if (defined('WP_DEBUG')) {
            DebugEcho("WP_DEBUG: " . (WP_DEBUG === true ? 'On' : 'Off'), $force_display);
        } else {
            DebugEcho("WP_DEBUG: Off", $force_display);
        }

        if (defined('WP_DEBUG_DISPLAY')) {
            if (null == WP_DEBUG_DISPLAY) {
                DebugEcho("WP_DEBUG_DISPLAY: null", $force_display);
            } else {
                DebugEcho("WP_DEBUG_DISPLAY: " . (WP_DEBUG_DISPLAY === true ? 'On' : 'Off'), $force_display);
            }
        } else {
            DebugEcho("WP_DEBUG_DISPLAY: Off", $force_display);
        }

        if (defined('WP_DEBUG_LOG')) {
            DebugEcho("WP_DEBUG_LOG: " . (WP_DEBUG_LOG === true ? 'On' : 'Off'), $force_display);
        } else {
            DebugEcho("WP_DEBUG_LOG: Off", $force_display);
        }

        if (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON) {
            DebugEcho("Alternate cron is enabled", $force_display);
        }

        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            DebugEcho("WordPress cron is disabled. Postie will not run unless you have an external cron set up.", $force_display);
        }

        if (defined('WP_MAX_MEMORY_LIMIT ')) {
            DebugEcho("WP_MAX_MEMORY_LIMIT: " . WP_MAX_MEMORY_LIMIT, $force_display);
        } else {
            DebugEcho("WP_MAX_MEMORY_LIMIT: 256M (default)", $force_display);
        }

        if (defined('WP_MEMORY_LIMIT')) {
            DebugEcho("WP_MEMORY_LIMIT: " . WP_MEMORY_LIMIT, $force_display);
        } else {
            DebugEcho("WP_MEMORY_LIMIT: 32M (default)", $force_display);
        }

        DebugEcho("imagick version: " . phpversion('imagick'), $force_display);
        DebugEcho("gd version: " . phpversion('gd'), $force_display);

        require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
        require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';
        require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';
        $implementations = apply_filters('wp_image_editors', array('WP_Image_Editor_Imagick', 'WP_Image_Editor_GD'));
        foreach ($implementations as $implementation) {
            if (!call_user_func(array($implementation, 'test'))) {
                DebugEcho("image editor not supported: $implementation", $force_display);
                continue;
            } else {
                DebugEcho("image editor supported: $implementation", $force_display);

                foreach (array('image/jpeg', 'image/png', 'image/gif') as $mtype) {
                    if (!call_user_func(array($implementation, 'supports_mime_type'), $mtype)) {
                        DebugEcho("$implementation does not support: $mtype", $force_display);
                    } else {
                        DebugEcho("$implementation supports: $mtype", $force_display);
                    }
                }
            }
        }

        DebugEcho("Registered image sizes", $force_display);
        DebugDump(get_intermediate_image_sizes());
        $this->show_filters_for('image_downsize');
        $this->show_filters_for('wp_handle_upload');
        $this->show_filters_for('wp_get_attachment_thumb_file');
        $this->show_filters_for('wp_handle_upload_prefilter');
        $this->show_filters_for('wp_handle_sideload_prefilter');
        $this->show_filters_for('pre_move_uploaded_file');

        DebugEcho("image memory limit: " . apply_filters('image_memory_limit', WP_MAX_MEMORY_LIMIT), $force_display);

        DebugEcho("DISABLE_WP_CRON: " . (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON === true ? 'On' : 'Off'), $force_display);
        DebugEcho("ALTERNATE_WP_CRON: " . (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON === true ? 'On' : 'Off'), $force_display);

        if (defined('WP_CRON_LOCK_TIMEOUT')) {
            DebugEcho("WP_CRON_LOCK_TIMEOUT: " . WP_CRON_LOCK_TIMEOUT, $force_display);
        }

        if ($g_postie_init->postie_IsIconvInstalled()) {
            DebugEcho("iconv: present", $force_display);
        } else {
            DebugEcho("iconv: missing", $force_display);
        }

        DebugEcho('Active plugins', $force_display);
        $plugins = get_option('active_plugins');
        foreach ($plugins as $plugin) {
            $plugin_data = get_file_data(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin, array('Version' => 'Version'), false);
            $plugin_version = $plugin_data['Version'];
            DebugEcho("     $plugin => $plugin_version", $force_display);
        }

        DebugEcho("Postie is in " . plugin_dir_path(__FILE__), $force_display);
        DebugEcho("Postie Version: " . POSTIE_VERSION, $force_display);
        DebugEcho("POSTIE_DEBUG: " . ($this->is_debugmode() ? 'On' : 'Off'), $force_display);

        $this->show_filters_for('postie_filter_email');
        $this->show_filters_for('postie_filter_email2');
        $this->show_filters_for('postie_filter_email3');
        $this->show_filters_for('postie_author');
        $this->show_filters_for('postie_post_before');
        $this->show_filters_for('postie_post_after');
        $this->show_filters_for('postie_file_added');
        $this->show_filters_for('postie_gallery');
        $this->show_filters_for('postie_comment_before');
        $this->show_filters_for('postie_comment_after');
        $this->show_filters_for('postie_category_default');
        $this->show_filters_for('postie_log_debug');
        $this->show_filters_for('postie_log_error');
        $this->show_filters_for('postie_session_start');
        $this->show_filters_for('postie_session_end');
        $this->show_filters_for('postie_preconnect');
        $this->show_filters_for('postie_post_pre');
        $this->show_filters_for('postie_email_reject_recipients');
        $this->show_filters_for('postie_email_notify_recipients');
        $this->show_filters_for('postie_email_reject_subject');
        $this->show_filters_for('postie_email_notify_subject');
        $this->show_filters_for('postie_email_reject_body');
        $this->show_filters_for('postie_place_media');
        $this->show_filters_for('postie_place_media_before');
        $this->show_filters_for('postie_place_media_after');
        $this->show_filters_for('postie_raw');
        $this->show_filters_for('postie_bare_link');
        $this->show_filters_for('postie_category');
        $this->show_filters_for('postie_file_added_pre');
        $this->show_filters_for('postie_include_attachment');
    }

    function is_debugmode() {
        return (defined('POSTIE_DEBUG') && POSTIE_DEBUG == true);
    }

    function log_onscreen($data) {
        if (php_sapi_name() == 'cli') {
            print("$data\n");
        } else {
            print("<pre>" . htmlspecialchars($data) . "</pre>\n");
        }
    }

    function log_error($v) {
        error_log("Postie [error]: $v");
        $this->log_onscreen($v);
    }

    function log_debug($data) {
        error_log("Postie [debug]: $data");
    }

    function connection_info($config) {
        $conninfo = array();
        $conninfo['mail_server'] = $config['mail_server'];
        $conninfo['mail_port'] = $config['mail_server_port'];
        $conninfo['mail_user'] = $config['mail_userid'];
        $conninfo['mail_password'] = $config['mail_password'];
        $conninfo['mail_protocol'] = $config['input_protocol'];
        $conninfo['mail_tls'] = $config['email_tls'];
        $conninfo['email_delete_after_processing'] = $config['delete_mail_after_processing'];
        $conninfo['email_max'] = $config['maxemails'];
        $conninfo['email_ignore_state'] = $config['ignore_mail_state'];

        return apply_filters('postie_preconnect', $conninfo);
    }

    function test_config() {

        wp_get_current_user();

        if (!current_user_can('manage_options')) {
            DebugEcho('non-admin tried to set options');
            echo '<h2> Sorry only admin can run this file</h2>';
            exit();
        }

        $config = postie_config_read();
        if (true == $config['postie_log_error'] || (defined('POSTIE_DEBUG') && true == POSTIE_DEBUG)) {
            add_action('postie_log_error', array($this, 'log_error'));
        }
        if (true == $config['postie_log_debug'] && !defined('POSTIE_DEBUG')) {
            define('POSTIE_DEBUG', true);
        }
        if (true == $config['postie_log_debug'] || (defined('POSTIE_DEBUG') && true == POSTIE_DEBUG)) {
            add_action('postie_log_debug', array($this, 'log_debug'));
        }
        ?>
        <div class="wrap"> 
            <h1>Postie Configuration Test</h1>
            <?php
            $this->postie_environment(true);
            ?>

            <h2>Clock</h2>
            <p>This shows what time it would be if you posted right now</p>
            <?php
            $wptz = get_option('gmt_offset');
            $wptzs = get_option('timezone_string');
            DebugEcho("Wordpress timezone: $wptzs ($wptz)", true);
            DebugEcho("Current time: " . current_time('mysql'), true);
            DebugEcho("Current time (gmt): " . current_time('mysql', 1), true);
            DebugEcho("Postie time correction: {$config['time_offset']}", true);
            $offsetdate = strtotime(current_time('mysql')) + ($config['use_time_offset'] ? $config['time_offset'] * 3600 : 0);

            DebugEcho("Post time: " . date('Y-m-d H:i:s', $offsetdate), true);
            ?>
            <h2>Encoding</h2>
            <?php
            $this->postie_environment_encoding(true);
            ?>

            <h2>Connect to Mail Host</h2>
            <?php
            DebugEcho("Postie connection: " . $config['input_connection'], true);
            DebugEcho("Postie protocol: " . $config['input_protocol'], true);
            DebugEcho("Postie server: " . $config['mail_server'], true);
            DebugEcho("Postie port: " . $config['mail_server_port'], true);

            if (!$config['mail_server'] || !$config['mail_server_port'] || !$config['mail_userid']) {
                EchoError("FAIL - server settings not complete");
            }

            $conninfo = $this->connection_info($config);
            if ($this->is_debugmode()) {
                fCore::enableDebugging(true);
                fCore::registerDebugCallback('DebugEcho');
            }

            switch (strtolower($config['input_protocol'])) {
                case 'imap':
                case 'imap-ssl':
                    try {
                        if ($config['input_connection'] == 'curl') {
                            $conn = new pCurlConnection('imap', $conninfo['mail_server'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_port'], ($conninfo['mail_protocol'] == 'imap-ssl' || $conninfo['mail_protocol'] == 'pop3-ssl'));
                        } else {
                            $conn = new pSocketConnection('imap', $conninfo['mail_server'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_port'], ($conninfo['mail_protocol'] == 'imap-ssl' || $conninfo['mail_protocol'] == 'pop3-ssl'));
                        }
                        $srv = new pImapMailServer($conn);
                        $mailbox = new fMailbox('imap', $conn, $srv);
                        $m = $mailbox->countMessages();
                        DebugEcho("Successful " . strtoupper($config['input_protocol']) . " connection on port {$config['mail_server_port']}", true);
                        DebugEcho("# of waiting messages: $m", true);
                        $mailbox->close();
                    } catch (Exception $e) {
                        EchoError("Unable to connect. The server said: " . $e->getMessage());
                    }
                    break;

                case 'pop3':
                case 'pop3-ssl':
                    try {
                        if ($config['input_connection'] == 'curl') {
                            $conn = new pCurlConnection('pop3', $conninfo['mail_server'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_port'], ($conninfo['mail_protocol'] == 'imap-ssl' || $conninfo['mail_protocol'] == 'pop3-ssl'), 30);
                        } else {
                            $conn = new pSocketConnection('pop3', $conninfo['mail_server'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_port'], ($conninfo['mail_protocol'] == 'imap-ssl' || $conninfo['mail_protocol'] == 'pop3-ssl'));
                        }
                        $srv = new pPop3MailServer($conn);
                        $mailbox = new fMailbox('pop3', $conn, $srv);
                        $m = $mailbox->countMessages();
                        DebugEcho("Successful " . strtoupper($config['input_protocol']) . " connection on port {$config['mail_server_port']}", true);
                        DebugEcho("# of waiting messages: $m", true);
                        $mailbox->close();
                    } catch (Exception $e) {
                        EchoError("Unable to connect. The server said:");
                        EchoError($e->getMessage());
                    }
                    break;
                default:
                    EchoError("Unable to connect. Invalid setup");
                    break;
            }
            ?>
        </div>
        <?php
        DebugEcho("Test complete");
    }

}

global $g_postie; //need to declare as global for wp cli
$g_postie = new Postie();
