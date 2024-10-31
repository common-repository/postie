<?php

$wp_version = '5.2.0';

define('WP_PLUGIN_URL', 'http://localhost/postie/wp-content/plugins');

class wpdb {

    public $t_get_var = "";
    public $terms = 'wp_terms';
    public $term_taxonomy = 'wp_taxonomy';

    public function get_var($sql) {
        if (is_array($this->t_get_var)) {
            if (count($this->t_get_var) > 0) {
                $r = $this->t_get_var[0];
                unset($this->t_get_var[0]);
                $this->t_get_var = array_values($this->t_get_var);
            } else {
                $r = null;
            }
        } else {
            $r = $this->t_get_var;
            $this->t_get_var = "";
        }
        DebugEcho("wpdb:get_var: sql: $sql");
        DebugEcho("wpdb:get_var: result: $r");
        return $r;
    }

}

$wpdb = new wpdb();

class WP_Error {
    
}

class WP_User {

    public $ID = 1;
    public $user_login = 'admin';
    public $user_url = '';

    function has_cap() {
        return true;
    }

    function get_user_by() {
        return get_user_by();
    }

}

function get_bloginfo() {
    return '5.6';
}

$g_user = new WP_User();

function plugin_dir_path() {
    return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
}

$g_option = 'open';

function get_option($opt) {
    global $g_option;
    if (is_array($g_option)) {
        return $g_option[$opt];
    } else {
        return $g_option;
    }
}

$g_current_user_can = array();

function current_user_can() {
    global $g_current_user_can;
    if (is_array($g_current_user_can) && count($g_current_user_can) > 0) {
        return array_shift($g_current_user_can);
    } else {
        throw new Exception('$g_current_user_can not initialized');
    }
}

function get_post_types() {
    return array("post", "page", "custom", "custom1", "Custom2");
}

function current_time() {
    return '2005-08-05 10:41:13';
}

function is_admin() {
    return false;
}

function get_site_url() {
    return "http://example.com/";
}

function get_post() {
    $r = new stdClass();
    $r->post_date = '';
    $r->post_parent = 0;
    $r->guid = '7b0d965d-b8b0-4654-ac9e-eeef1d8cf571';
    $r->post_title = '';
    $r->post_excerpt = '';
    return $r;
}

function __($t) {
    return $t;
}

function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) == $needle;
}

function wp_check_filetype($filename) {
    if (empty($filename))
        return null;
    $filename = strtolower($filename);
    if (endsWith($filename, ".png"))
        return array('ext' => 'png', 'type' => 'image/png');
    if (endsWith($filename, ".pdf"))
        return array('ext' => 'pdf', 'type' => 'application/pdf');
    if (endsWith($filename, ".ics"))
        return array('ext' => 'ics', 'type' => 'text/calendar');
    if (endsWith($filename, ".mp3"))
        return array('ext' => 'mp3', 'type' => 'audio/mpeg');
    return array('ext' => '', 'type' => '');
}

function wp_upload_dir() {
    return array(
        'path' => sys_get_temp_dir(),
        'url' => 'http://example.com/upload/',
        'subdir' => sys_get_temp_dir(),
        'basedir' => sys_get_temp_dir(),
        'baseurl' => 'http://example.com/',
        'error' => false
    );
}

function wp_unique_filename() {
    return uniqid("postie");
}

function wp_get_attachment_url() {
    return 'http://example.net/wp-content/uploads/filename';
}

function image_downsize() {
    return array('http://example.net/wp-content/uploads/filename.jpg', 10, 10, true);
}

function image_hwstring() {
    return 'width="10" height="10" ';
}

function get_attachment_link() {
    DebugEcho("get_attachment_link");
    return 'http://example.net/wp-content/uploads/filename.jpg';
}

function get_user_by() {
    global $g_user;
    return $g_user;
}

function register_activation_hook() {
    
}

function add_action() {
    
}

function do_action($action) {
    
}

function add_filter() {
    
}

function register_deactivation_hook() {
    
}

function apply_filters($filter, $value) {
    return $value;
}

function wp_insert_attachment() {
    return 1;
}

function wp_insert_post() {
    return 1;
}

function wp_update_attachment_metadata() {
    
}

function wp_generate_attachment_metadata() {
    
}

function is_wp_error() {
    return false;
}

function sanitize_title($title) {
    return $title;
}

function get_temp_dir() {
    return sys_get_temp_dir();
}

function sanitize_term($s) {
    return trim($s);
}

$g_get_term_by = new stdClass();
$g_get_term_by->term_id = 1;

function get_term_by() {
    global $g_get_term_by;
    return $g_get_term_by;
}

function get_post_format_strings() {
    return array('standard' => 'standard', 'video' => 'video', 'image' => 'image', 'aside' => 'aside');
}

function has_post_thumbnail() {
    return false;
}

function sanitize_file_name($name) {
    return $name;
}

$g_get_posts = array();

function get_posts() {
    global $g_get_posts;
    return $g_get_posts;
}

function SafeFileName() {
    
}

function plugin_basename() {
    return '';
}

function wp_set_object_terms() {
    
}

function esc_attr() {
    
}

function update_post_meta() {
    
}

function get_post_thumbnail_id() {
    return 0;
}

function _wp_oembed_get_object() {
    return null;
}

function wp_get_schedule() {
    return 'wp_get_schedule';
}

function wp_clear_scheduled_hook() {
    
}

function wp_schedule_event() {
    return true;
}

function get_users() {
    $o = new stdClass();
    $o->user_email = 'bob@example.com';
    return array($o);
}

function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
    if (!(is_string($to) || is_array($to))) {
        throw new Exception('invalid $to parameter');
    }
    if (!is_string($subject)) {
        throw new Exception('invalid $subject parameter');
    }
}

function media_handle_sideload() {
    return 1;
}

function get_attached_file() {
    return '/tmp/file';
}

function get_gmt_from_date($date) {
    return $date;
}

function get_current_blog_id() {
    return 1;
}

function wp_update_post() {
    
}

function wp_set_post_terms() {
    
}

function get_file_data() {
    return array('Version' => '1');
}

function wp_tempnam() {
    return 'tmp';
}

function do_shortcode($content) {
    return $content;
}

function is_user_member_of_blog() {
    return true;
}

function get_permalink() {
    return 'http://example.com/&postid=1';
}

function get_post_status() {
    return 'posted';
}
