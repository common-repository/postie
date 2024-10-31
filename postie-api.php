<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'postie-config.class.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'postie.class.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'postie-message.php');

/*
 * These are the only official public methods for accessing postie functionality
 */

function lookup_taxonomy($termid) {
    return postie_lookup_taxonomy_name($termid);
}

function lookup_category($trial_category, $category_match) {
    return postie_lookup_category_id($trial_category, $category_match);
}

function RemoveExtraCharactersInEmailAddress($address) {
    $c = new PostieConfig();
    $m = new PostieMessage(array(), $c->config_read());
    return $m->get_clean_emailaddress($address);
}

function EchoError($v) {
    global $g_postie;
    if (!empty($g_postie)) {
        $g_postie->log_error($v);
    }
    do_action('postie_log_debug', $v);
}

function DebugDump($v) {
    global $g_postie;
    $d = print_r($v, true);
    if (defined('POSTIE_DEBUG') && true == POSTIE_DEBUG && !empty($g_postie)) {
        $g_postie->log_onscreen($d);
    }
    do_action('postie_log_debug', $d);
}

function DebugEcho($v, $force = false) {
    global $g_postie;
    if ($force || (defined('POSTIE_DEBUG') && true == POSTIE_DEBUG)) {
        if (!empty($g_postie)) {
            $g_postie->log_onscreen($v);
        }
    }
    do_action('postie_log_debug', $v);
}

function postie_config_read() {
    $pconfig = new PostieConfig();
    return $pconfig->config_read();
}

/**
 * called by WP cron
 */
function postie_check() {
    //don't use DebugEcho or EchoInfo here as it is not defined when called as an action
    //error_log("check_postie");
    global $g_postie;
    if (!empty($g_postie)) {
        $g_postie->get_mail();
    }
}

function postie_get_mail() {
    //don't use DebugEcho or EchoInfo here as it is not defined when called as an action
    //error_log("check_postie");
    global $g_postie;
    if (!empty($g_postie)) {
        $g_postie->get_mail();
    }
}
