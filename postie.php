<?php

/*
  Plugin Name: Postie
  Plugin URI: http://PostiePlugin.com/
  Description: Create posts via email. Significantly upgrades the Post by Email features of WordPress.
  Version: 1.9.69
  Author: Wayne Allen
  Author URI: http://PostiePlugin.com/
  License: GPL3
  Text Domain: postie
 */

/*  Copyright (c) 2015-20  Wayne Allen  (email : wayne@postieplugin.com)

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
  $Id: postie.php 3083460 2024-05-08 16:35:13Z WayneAllen $
 */

if (!defined('WPINC')) {
    die; // Exit if accessed directly
}

$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$plugin_version = $plugin_data['Version'];

define('POSTIE_VERSION', $plugin_version);
define('POSTIE_ROOT', dirname(__FILE__));
define('POSTIE_URL', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)));

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'postie-compatibility.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'postie-api.php');

if (!class_exists('PostieInit')) {

    class PostieInit {

        public function __construct() {
            //WordPress Actions
            add_action('init', array($this, 'init_action'), 20);
            add_action('parse_request', array($this, 'parse_request_action'));
            add_action('admin_init', array($this, 'admin_init_action'));
            add_action('admin_menu', array($this, 'admin_menu_action'));
            add_action('admin_head', array($this, 'admin_head_action'));
            add_action('plugins_loaded', array($this, 'plugins_loaded_action'));
            add_action('pre_post_update', array($this, 'pre_post_update_action'), 10, 2);

            //WordPress filters
            if (version_compare(get_bloginfo('version'), '5.5', '>=')) {
                add_filter('allowed_options', array($this, 'whitelist_options_filter'));
            } else {

                add_filter('whitelist_options', array($this, 'whitelist_options_filter'));
            }
            add_filter('cron_schedules', array($this, 'cron_schedules_filter'));
            add_filter('query_vars', array($this, 'query_vars_filter'));
            add_filter("plugin_action_links_" . plugin_basename(__FILE__), array($this, 'plugin_action_links_filter'));
            add_filter('plugin_row_meta', array($this, 'plugin_row_meta_filter'), 10, 2);
            add_filter('enable_post_by_email_configuration', array($this, 'enable_post_by_email_configuration'));
            add_filter('site_status_tests', array($this, 'site_status_tests_filter'));
            add_filter('query', array($this, 'query_filter'));

            //WordPress Hooks
            register_activation_hook(__FILE__, array('PostieInit', 'postie_activate_hook'));
            register_activation_hook(__FILE__, array('PostieInit', 'postie_cron_hook'));
            register_deactivation_hook(__FILE__, array('PostieInit', 'postie_decron_hook'));

            //Postie actions
            add_action('check_postie_hook', 'postie_check');

            if (is_admin()) {
                $this->postie_warnings();
            }
        }

        static function postie_activate_hook() {
            /*
             * called by WP when activating the plugin
             * Note that you can't do any output during this funtion or activation
             * will fail on some systems. This means no DebugEcho, EchoInfo or DebugDump.
             */
        }

        static function postie_cron_hook($interval = false) {
            //Do not echo output in filters, it seems to break some installs
            //error_log("postie_cron: setting up cron task: $interval");
            //$schedules = wp_get_schedules();
            //error_log("postie_cron\n" . print_r($schedules, true));

            if (empty($interval)) {
                $interval = 'hourly';
                //error_log("Postie: setting up cron task: defaulting to hourly");
            }

            if ($interval == 'manual') {
                PostieInit::postie_decron_hook();
                //error_log("postie_cron: clearing cron (manual)");
            } else {
                if ($interval != wp_get_schedule('check_postie_hook')) {
                    PostieInit::postie_decron_hook(); //remove existing
                    //try to create the new schedule with the first run in 5 minutes
                    if (false === wp_schedule_event(time() + 5 * 60, $interval, 'check_postie_hook')) {
                        //error_log("postie_cron: Failed to set up cron task: $interval");
                    } else {
                        //error_log("postie_cron: Set up cron task: $interval");
                    }
                } else {
                    //error_log("postie_cron: OK: $interval");
                    //don't need to do anything, cron already scheduled
                }
            }
        }

        static function postie_decron_hook() {
            //don't use DebugEcho or EchoInfo here as it is not defined when called as a hook
            //error_log("postie_decron: clearing cron");
            wp_clear_scheduled_hook('check_postie_hook');
        }

        function site_status_tests_filter($tests) {
            $tests['direct']['postie1'] = array(
                'label' => __('Postie Test'),
                'test' => array($this, 'test_delete_mail_after_processing'),
            );
            $tests['direct']['postie2'] = array(
                'label' => __('Postie Test'),
                'test' => array($this, 'test_turn_authorization_off'),
            );
            return $tests;
        }

        function query_filter($query) {
            DebugEcho("Query: $query");

            return $query;
        }

        //https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
        function test_delete_mail_after_processing() {
            $config = postie_config_read();
            $enabled = $config['delete_mail_after_processing'];
            $result = array(
                'label' => __('Postie should delete emails after processing'),
                'status' => $enabled ? 'good' : 'recommended',
                'badge' => array(
                    'label' => __('Performance'),
                    'color' => 'blue',
                ),
                'description' => sprintf('<p>%s</p>', __('If emails are not deleted they will be imported every time Postie runs.')
                ),
                'actions' => '',
                'test' => 'postie_delete_mail_after_processing',
            );

            return $result;
        }

        function test_turn_authorization_off() {
            $config = postie_config_read();
            $enabled = !$config['turn_authorization_off'];
            $result = array(
                'label' => __('Postie should only allow authorized users to post'),
                'status' => $enabled ? 'good' : 'critical',
                'badge' => array(
                    'label' => __('Security'),
                    'color' => 'blue',
                ),
                'description' => sprintf('<p>%s</p>', __('Allowing anyone who knows your Postie email address to post can result in unwanted posts.')
                ),
                'actions' => '',
                'test' => 'postie_turn_authorization_off',
            );

            return $result;
        }

        function pre_post_update_action($post_id, $post_data) {
            DebugEcho('pre_post_update');
            DebugDump($post_data);
        }

        function plugins_loaded_action() {
            load_plugin_textdomain('postie', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        function init_action() {
            global $wp_version;
            if (version_compare($wp_version, '5.3') == -1) {
                require_once( ABSPATH . WPINC . '/class-oembed.php' );
            } else {
                require_once( ABSPATH . WPINC . '/class-wp-oembed.php' );
            }

            remove_filter('content_save_pre', 'wp_filter_post_kses');
        }

        function enable_post_by_email_configuration($enabled) {
            return false;
        }

        function plugin_row_meta_filter($links, $file) {
            if (strpos($file, strval(plugin_basename(__FILE__))) !== false) {
                $new_links = array(
                    '<a href="http://postieplugin.com/" target="_blank">Support</a>',
                    '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HPK99BJ88V4C2" target="_blank">Donate</a>'
                );

                $links = array_merge($links, $new_links);
            }

            return $links;
        }

        function parse_request_action($wp) {
            if (array_key_exists('postie', $wp->query_vars)) {
                switch ($wp->query_vars['postie']) {
                    case 'get-mail':
                        postie_get_mail();
                        die();
                    case 'test-config':
                        postie_test_config();
                        die();
                    default :
                        dir('Unknown option: ' . $wp->query_vars['postie']);
                }
            }
        }

        function admin_init_action() {
            wp_register_style('postie-style', plugins_url('css/style.css', __FILE__));
            register_setting('postie-settings', 'postie-settings', array(new PostieConfig(), 'config_ValidateSettings'));
        }

        function admin_menu_action() {
            $page = add_menu_page('Postie', 'Postie', 'manage_options', 'postie-settings', array($this, 'postie_show_config_page'));
            add_action('admin_print_styles-' . $page, array($this, 'postie_admin_styles'));
        }

        function admin_head_action() {
            ?>
            <style type="text/css">
                #adminmenu #toplevel_page_postie-settings div.wp-menu-image:before {
                    content: "\f466";
                }    
            </style>
            <?php

        }

        function whitelist_options_filter($options) {
            $added = array('postie-settings' => array('postie-settings'));

            if (function_exists('add_allowed_options')) {
                $options = add_allowed_options($added, $options);
            } else {
                $options = add_option_whitelist($added, $options); // deprecated in WP 5.5
            }

            return $options;
        }

        function cron_schedules_filter($schedules) {
            //Do not echo output in filters, it seems to break some installs
            //error_log("cron_schedules_filter: setting cron schedules");
            $schedules['weekly'] = array('interval' => (60 * 60 * 24 * 7), 'display' => __('Once Weekly', 'postie'));
            $schedules['twohours'] = array('interval' => 60 * 60 * 2, 'display' => __('Every 2 hours', 'postie'));
            $schedules['twiceperhour'] = array('interval' => 60 * 30, 'display' => __('Twice per hour', 'postie'));
            $schedules['tenminutes'] = array('interval' => 60 * 10, 'display' => __('Every 10 minutes', 'postie'));
            $schedules['fiveminutes'] = array('interval' => 60 * 5, 'display' => __('Every 5 minutes', 'postie'));
            $schedules['oneminute'] = array('interval' => 60 * 1, 'display' => __('Every 1 minute', 'postie'));
            $schedules['thirtyseconds'] = array('interval' => 30, 'display' => __('Every 30 seconds', 'postie'));
            $schedules['fifteenseconds'] = array('interval' => 15, 'display' => __('Every 15 seconds', 'postie'));
            return $schedules;
        }

        function query_vars_filter($vars) {
            $vars[] = 'postie';
            return $vars;
        }

        function plugin_action_links_filter($links) {
            $links[] = '<a href="admin.php?page=postie-settings">Settings</a>';
            return $links;
        }

        function postie_show_config_page() {
            global $g_postie;
            require_once POSTIE_ROOT . '/config_form.php';
        }

        function postie_admin_styles() {
            wp_enqueue_style('postie-style');
        }

        /**
         * This function looks for markdown which causes problems with postie
         */
        function postie_isMarkdownInstalled() {
            if (in_array("markdown.php", get_option("active_plugins"))) {
                return true;
            }
            return false;
        }

        function postie_markdown_warning() {
            echo "<div id='postie-lst-warning' class='error'><p><strong>";
            _e("You currently have the Markdown plugin installed. It will cause problems if you send in HTML email. Please turn it off if you intend to send email using HTML.", 'postie');
            echo "</strong></p></div>";
        }

        function postie_enter_info() {
            echo "<div id='postie-info-warning' class='updated fade'><p><strong>" . __('Postie is almost ready.', 'postie') . "</strong> "
            . sprintf(__('You must <a href="%1$s">enter your email settings</a> for it to work.', 'postie'), "admin.php?page=postie-settings")
            . "</p></div> ";
        }

        function postie_iconv_warning() {
            echo "<div id='postie-lst-warning' class='error'><p><strong>";
            _e("Warning! Postie requires that iconv be enabled.", 'postie');
            echo "</strong></p></div>";
        }

        function postie_php_warning() {
            echo "<div id='postie-lst-warning' class='error'><p><strong>";
            _e("Warning! Postie requires that PHP be verion 5.2 or higher. You have version " . phpversion(), 'postie');
            echo "</strong></p></div>";
        }

        function postie_curl_warning() {
            $cv = curl_version();
            echo "<div id='postie-lst-warning' class='error'><p><strong>";
            _e("Warning! Postie requires cURL 7.30.0 or newer be installed. {$cv['version']} is installed.", 'postie');
            echo "</strong></p></div>";
        }

        function postie_adminuser_warning() {
            echo "<div id='postie-mbstring-warning' class='error'><p><strong>";
            echo __('Warning: the Default Poster is not a valid WordPress login. Postie may reject emails if this is not corrected.', 'postie');
            echo "</strong></p></div>";
        }

        function postie_IsIconvInstalled($display = true) {
            $function_list = array('iconv');
            return($this->postie_HasFunctions($function_list, $display));
        }

        /**
         * Handles verifying that a list of functions exists
         * @return boolean
         * @param array
         */
        function postie_HasFunctions($function_list, $display = true) {
            foreach ($function_list as $function) {
                if (!function_exists($function)) {
                    if ($display) {
                        EchoError("Missing $function");
                    }
                    return false;
                }
            }
            return true;
        }

        function postie_warnings() {
            $config = postie_config_read();

            if ((empty($config['mail_server']) ||
                    empty($config['mail_server_port']) ||
                    empty($config['mail_userid']) ||
                    empty($config['mail_password'])
                    ) && !isset($_POST['submit'])) {

                add_action('admin_notices', array($this, 'postie_enter_info'));
            }

            if ($this->postie_isMarkdownInstalled() && $config['prefer_text_type'] == 'html') {
                add_action('admin_notices', array($this, 'postie_markdown_warning'));
            }

            if (!$this->postie_IsIconvInstalled()) {
                add_action('admin_notices', array($this, 'postie_iconv_warning'));
            }

            if (!fCore::checkVersion('5.2.0')) {
                add_action('admin_notices', array($this, 'postie_php_warning'));
            }

            if (function_exists('curl_version')) {
                $cv = curl_version();
            } else {
                $cv = '1.0.0';
            }
            if ($config['input_connection'] == 'curl' && !version_compare($cv['version'], '7.30.0', 'ge')) {
                add_action('admin_notices', array($this, 'postie_curl_warning'));
            }

            $userdata = WP_User::get_data_by('login', $config['admin_username']);
            if (!$userdata) {
                add_action('admin_notices', array($this, 'postie_adminuser_warning'));
            }
        }

    }

    global $g_postie_init; //need to declare as global for wp cli
    $g_postie_init = new PostieInit();
}
