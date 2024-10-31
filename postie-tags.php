<?php

function postie_is_html($str, $config = null) {
    if (empty($config)) {
        $h = (1 === preg_match('/<[a-z][\s\S]*>/i', $str));
        DebugEcho('postie_is_html: content: ' . $h ? 'true' : 'false');
        return $h;
    } else {
        $h = $config['prefer_text_type'] === 'html';
        DebugEcho('postie_is_html: config: ' . $h ? 'true' : 'false');
        return $h;
    }
}

function postie_content_lines($content, $config = null) {
    global $g_postie;

    $lines = array();
    if (postie_is_html($content, $config)) {
        DebugEcho("postie_content_lines: html");
        $html = $g_postie->load_html($content);
        if ($html !== false) {
            $es = $html->find('text');
            //DebugEcho("postie_content_lines: html " . count($es));
            foreach ($es as $e) {
                $lines[] = trim($e->plaintext);
            }
        }
    } else {
        DebugEcho("postie_content_lines: plain");
        $lines = explode("\n", $content);
    }
    return $lines;
}

function postie_lookup_taxonomy_name($termid) {
    global $wpdb;
    $tax_sql = 'SELECT taxonomy FROM ' . $wpdb->term_taxonomy . ' WHERE term_id = ' . $termid;
    $tax = $wpdb->get_var($tax_sql);
    DebugEcho("lookup_taxonomy: $termid is in taxonomy $tax");
    return $tax;
}

function postie_lookup_category_id($trial_category, $category_match = true) {
    global $wpdb;
    DebugEcho("lookup_category: $trial_category");

    $term = get_term_by('name', esc_attr($trial_category), 'category');
    if (!empty($term)) {
        DebugEcho("lookup_category: found by name $trial_category");
        //DebugDump($term);
        //then category is a named and found 
        return $term->term_id;
    } else {
        DebugEcho("lookup_category: not found by name $trial_category");
    }

    $term = get_term_by('slug', esc_attr($trial_category), 'category');
    if (!empty($term)) {
        DebugEcho("lookup_category: found by slug $trial_category");
        return $term->term_id;
    } else {
        DebugEcho("lookup_category: not found by slug $trial_category");
    }

    if (is_numeric($trial_category)) {
        DebugEcho("lookup_category: looking for id '$trial_category'");
        $cat_id = intval($trial_category);
        $term = get_term_by('id', $cat_id, 'category');
        if (!empty($term) && $term->term_id == $trial_category) {
            DebugEcho("lookup_category: found by id '$cat_id'");
            DebugDump($term);
            //then category was an ID and found 
            return $term->term_id;
        } else {
            DebugEcho("lookup_category: not found by id '$cat_id'");
        }
    }

    $found_category = NULL;
    if ($category_match) {
        DebugEcho("category wildcard lookup: $trial_category");
        $sql_sub_name = 'SELECT term_id FROM ' . $wpdb->terms . ' WHERE name LIKE \'' . addslashes(esc_attr($trial_category)) . '%\' OR slug LIKE \'' . addslashes(esc_attr($trial_category)) . '%\' limit 1';
        $found_category = $wpdb->get_var($sql_sub_name);
        DebugEcho("lookup_category: wildcard found: $found_category");
    } else {
        DebugEcho("lookup_category: wildcard not found: $found_category");
    }

    return intval($found_category); //force to integer
}

/**
 * Checks for the comments tag
 * @return boolean
 */
function tag_AllowCommentsOnPost(&$content, $config) {
    $comments_allowed = get_option('default_comment_status'); // 'open' or 'closed'

    if (true == $config['legacy_commands']) {
        foreach (postie_content_lines($content) as $line) {
            $matches = array();
            if (preg_match("/^\s*comments:\s*([0|1|2])/imu", $line, $matches)) {
                $content = preg_replace("/comments:$matches[1]/i", "", $content);
                if ($matches[1] == '1') {
                    $comments_allowed = 'open';
                } else if ($matches[1] == '2') {
                    $comments_allowed = 'registered_only';
                } else {
                    $comments_allowed = 'closed';
                }
                break;
            }
        }
    } else {
        DebugEcho("tag_AllowCommentsOnPost: disabled - ignore legacy commands");
    }

    return $comments_allowed;
}

function tag_Status(&$content, $config) {

    $poststatus = $config['post_status'];

    if (true == $config['legacy_commands']) {
        foreach (postie_content_lines($content) as $lines) {
            $matches = array();
            if (preg_match("/^\s*status:\s*(draft|publish|pending|private|future)/imu", $lines, $matches)) {
                DebugEcho("tag_Status: found status $matches[1]");
                DebugDump($matches);
                $content = preg_replace("/$matches[0]/i", "", $content);
                $poststatus = $matches[1];
                break;
            }
        }
    } else {
        DebugEcho("tag_Status: disabled - ignore legacy commands");
    }

    if ($config['force_user_login']) {
        if (stristr('publish|future', $poststatus)) {
            if (!current_user_can('publish_posts')) {
                DebugEcho("tag_Status: user doesn't have publish_posts capability");
                $poststatus = 'draft';
            }
        }
    }

    return $poststatus;
}

function tag_Delay(&$content, $message_date, $config) {
    $offset = (!$config['ignore_email_date'] && $config['use_time_offset']) ? $config['time_offset'] : 0;

    DebugEcho("tag_Delay: start");
    $delay = 0;

    if (true == $config['legacy_commands']) {
        foreach (postie_content_lines($content) as $line) {
            $matches = array();
            if (preg_match("/^\s*delay:\s*(-?[0-9dhm]+)/imu", $line, $matches) && trim($matches[1])) {
                DebugEcho("tag_Delay: found delay: " . $matches[1]);
                $days = 0;
                $hours = 0;
                $minutes = 0;
                $dayMatches = array();
                if (preg_match("/(-?[0-9]+)d/iu", $matches[1], $dayMatches)) {
                    $days = $dayMatches[1];
                }
                $hourMatches = array();
                if (preg_match("/(-?[0-9]+)h/iu", $matches[1], $hourMatches)) {
                    $hours = $hourMatches[1];
                }
                $minuteMatches = array();
                if (preg_match("/(-?[0-9]+)m/iu", $matches[1], $minuteMatches)) {
                    $minutes = $minuteMatches[1];
                }
                $delay = (($days * 24 + $hours) * 60 + $minutes) * 60;
                $interval = 'P';
                if (abs($days) > 0) {
                    $interval .= abs($days) . 'D';
                }
                if (abs($hours) > 0) {
                    $interval .= 'T' . abs($hours) . 'H';
                }
                if (abs($minutes) > 0) {
                    if (abs($hours) > 0) {
                        $interval .= abs($minutes) . 'M';
                    } else {
                        $interval .= 'T' . abs($minutes) . 'M';
                    }
                }

                DebugEcho("tag_Delay: calculated delay: $delay");
                DebugEcho("tag_Delay: interval: $interval");
                DebugEcho("tag_Delay: replacing: $matches[0]");
                $content = preg_replace("/$matches[0]/i", "", $content);
                break;
            }
        }
    } else {
        DebugEcho("tag_Delay: disabled - ignore legacy commands");
    }

    $tzs = get_option('timezone_string');
    if (empty($tzs)) {
        $tzs = 'GMT';
    }
    DebugEcho("tag_Delay: timezone: $tzs");

    if ($config['ignore_email_date']) {
        $dateInSeconds = new DateTime(current_time('mysql'), new DateTimeZone($tzs));
        DebugEcho("tag_Delay: ignoring date: " . $dateInSeconds->format(DATE_RFC2822));
    } else {
        if (empty($message_date)) {
            $dateInSeconds = new DateTime(current_time('mysql'), new DateTimeZone($tzs));
            DebugEcho("tag_Delay: using current date: " . $dateInSeconds->format(DATE_RFC2822));
        } else {
            DebugEcho("tag_Delay: using message date(1): $message_date");

            // From https://wordpress.org/support/users/glenstewart/ on 2022-09-12
            // Glen trying to fix "double timezone specification" error on dates like:
            // Mon, 06 Sep 2004 08:15:56 +0000 (America/Los_Angeles)
            if (strstr($message_date, " (", true)) {
                $message_date = strstr($message_date, " (", true); // Mon, 06 Sep 2004 08:15:56 +0000
            }
            // as documented at https://geeksqa.com/failed-to-parse-time-string-at-position-41-i-double-timezone-specification

            $dateInSeconds = new DateTime($message_date, new DateTimeZone($tzs));
            DebugEcho("tag_Delay: using message date(2): " . $dateInSeconds->format(DATE_RFC2822));
        }
    }

    if ($delay > 0) {
        $delayeddateInSeconds = $dateInSeconds->add(new DateInterval($interval));
    }
    if ($delay < 0) {
        $delayeddateInSeconds = $dateInSeconds->sub(new DateInterval($interval));
    }
    if ($delay == 0) {
        $delayeddateInSeconds = clone $dateInSeconds;
    }
    //$delayeddateInSeconds->setTimezone(new DateTimeZone($tzs));

    if ($offset > 0) {
        $corrected_date = $delayeddateInSeconds->add(new DateInterval('PT' . $offset . 'H'));
    }
    if ($offset < 0) {
        $corrected_date = $delayeddateInSeconds->sub(new DateInterval('PT' . abs($offset) . 'H'));
    }
    if ($offset == 0) {
        $corrected_date = clone $delayeddateInSeconds;
    }
    $corrected_date->setTimezone(new DateTimeZone($tzs));

    DebugEcho("tag_Delay: message date: " . $dateInSeconds->format(DATE_RFC2822));
    DebugEcho("tag_Delay: delay: $delay");
    DebugEcho("tag_Delay: offset: $offset");
    DebugEcho("tag_Delay: delayed date: " . $delayeddateInSeconds->format(DATE_RFC2822));
    DebugEcho("tag_Delay: delayed with offset: " . $corrected_date->format(DATE_RFC2822));

    return array($corrected_date->format(DATE_W3C), $delay);
}

function tag_Excerpt(&$content, $config) {
    $post_excerpt = '';
    if (true == $config['legacy_commands']) {
        $matches = array();
        if (preg_match('/\s*:excerptstart ?(.*):excerptend/imus', $content, $matches)) {
            $content = str_replace($matches[0], "", $content);
            $post_excerpt = $matches[1];
            DebugEcho("tag_Excerpt: excerpt found: $post_excerpt");
            if ($config['filternewlines']) {
                DebugEcho("tag_Excerpt: filtering newlines from excerpt");
                $post_excerpt = filter_Newlines($post_excerpt, $config);
            }
        }
    } else {
        DebugEcho("tag_Excerpt: disabled - ignore legacy commands");
    }

    return $post_excerpt;
}

/**
 * This function determines the categories ids for the post
 * @return array
 */
function tag_Categories(&$subject, $defaultCategoryId, $config, $post_id) {
    DebugEcho("tag_Categories: start");
    $category_match = $config[PostieConfigOptions::CategoryMatch];
    $original_subject = $subject;
    $found = false;
    $post_categories = array();
    $matchtypes = array();
    $matches = array();

    if ($config[PostieConfigOptions::CategoryBracket]) {
        if (preg_match_all('/\[(.[^\[]*)\]/', $subject, $matches)) { // [<category1>] [<category2>] <Subject>
            $matchtypes[] = $matches;
        }
    }

    if ($config[PostieConfigOptions::CategoryDash]) {
        if (preg_match_all('/-(.[^-]*)-/', $subject, $matches)) { // -<category>- -<category2>- <Subject>
            $matchtypes[] = $matches;
        }
    }

    if ($config[PostieConfigOptions::CategoryColon]) {
        if (preg_match('/(.+?):\s?(.*)/', $subject, $matches)) { // <category>: <Subject>
            $matchtypes[] = array(array(0 => $matches[1] . ':'), array(1 => $matches[1]));
        }
    }

    DebugEcho("tag_Categories: looking up default: $defaultCategoryId");
    $defaultcat_name = '';
    $defaultcat = get_term_by('id', $defaultCategoryId, postie_lookup_taxonomy_name($defaultCategoryId));
    if (false !== $defaultcat) {
        $defaultcat_name = $defaultcat->name;
        DebugEcho("tag_Categories: default: $defaultcat_name");
    } else {
        DebugEcho("tag_Categories: default not found");
    }

    DebugEcho("tag_Categories: found categories");
    DebugDump($matchtypes);
    foreach ($matchtypes as $matches) {
        if (count($matches)) {
            $i = 0;
            foreach ($matches[1] as $match) {
                DebugEcho("tag_Categories: checking: $match");

                $trial_category = apply_filters('postie_category', trim($match), $category_match, $defaultcat_name);
                DebugEcho("tag_Categories: post postie_category: $trial_category");

                $categoryid = postie_lookup_category_id($trial_category, $category_match);
                if (!empty($categoryid)) {
                    $found = true;
                    $subject = str_replace($matches[0][$i], '', $subject);
                    DebugEcho("tag_Categories: subject: $subject");
                    $tax = postie_lookup_taxonomy_name($categoryid);
                    if ('category' == $tax) {
                        DebugEcho("tag_Categories: standard taxonomy $tax");
                        $post_categories[] = $categoryid;
                    } else {
                        DebugEcho("tag_Categories: custom taxonomy $tax");
                        wp_set_object_terms($post_id, $categoryid, $tax, true);
                    }
                }
                $i++;
            }
        }
    }
    if (!$found || !$config[PostieConfigOptions::CategoryRemove]) {
        if ($config[PostieConfigOptions::PostType] == 'page') {
            DebugEcho("tag_Categories: no default, page post type, not adding default category");
        } else {
            if (!$found) {
                DebugEcho("tag_Categories: using default: $defaultCategoryId for post type {$config[PostieConfigOptions::PostType]}");
                $post_categories[] = $defaultCategoryId;
            }
        }
        $subject = $original_subject;
    }
    $subject = trim($subject);
    return $post_categories;
}

function tag_CustomImageField($post_ID, $email, $config) {
    if ($config['custom_image_field']) {
        DebugEcho("Saving custom image post_meta");

        foreach (array_merge($email['attachment'], $email['inline'], $email['related']) as $attachment) {
            add_post_meta($post_ID, 'image', $attachment['wp_filename']);
            DebugEcho("Saving custom attachment '{$attachment['wp_filename']}'");
        }
    }
}

/*
 * Added by Raam Dev <raam@raamdev.com>
 * Adds support for handling Custom Post Types by adding the
 * Custom Post Type name to the email subject separated by
 * $custom_post_type_delim, e.g. "Movies // My Favorite Movie"
 * Also supports setting the Post Format.
 */

function tag_PostType(&$subject, &$config) {

    $post_type = $config[PostieConfigOptions::PostType];
    $post_format = $config[PostieConfigOptions::PostFormat];
    $separated_subject = array();
    $separated_subject[0] = "";
    $separated_subject[1] = $subject;

    $custom_post_type_delim = '//';
    $trial = '';
    if (strpos($subject, strval($custom_post_type_delim)) !== false) {
        // Captures the custom post type in the subject before $custom_post_type_delim
        $separated_subject = explode($custom_post_type_delim, $subject);
        $trial = trim(strtolower($separated_subject[0]));
        DebugEcho("post type: found possible type '$post_format'");
    }

    if (in_array($trial, array_map('strtolower', get_post_types()))) {
        DebugEcho("post type: found type '$trial'");
        $post_type = $trial;
        $subject = trim($separated_subject[1]);
        $config[PostieConfigOptions::PostType] = $trial;
    } elseif (in_array($trial, array_keys(get_post_format_strings()))) {
        DebugEcho("post type: found format '$trial'");
        $post_format = $trial;
        $subject = trim($separated_subject[1]);
        $config[PostieConfigOptions::PostFormat] = $trial;
    }

    return array('post_type' => $post_type, 'post_format' => $post_format);
}

function tag_Date(&$content, $message_date, $config) {

    DebugEcho("tag_Date: start");

    if (true == $config['legacy_commands']) {
        foreach (postie_content_lines($content) as $e) {
            $matches = array();
            if (1 === preg_match("/^\s*date:(.*)$/imu", $e, $matches)) {
                $possibledate = trim($matches[1]);
                DebugEcho("tag_Date: found date tag $matches[1]");
                $newdate = strtotime($possibledate);
                if (false !== $newdate) {
                    $t = date('H:i:s', $newdate);
                    DebugEcho("tag_Date: original time: $t");

                    $format = 'Y-m-d';
                    if ($t != '00:00:00') {
                        $format .= ' H:i:s';
                    }
                    $message_date = date($format, $newdate);
                    $content = str_replace($matches[0], '', $content);
                    break;
                } else {
                    DebugEcho("tag_Date: failed to parse '$possibledate' ($newdate)");
                }
            } else {
                //DebugEcho("tag_Date: nothing found");
            }
        }
    } else {
        DebugEcho("tag_Date: disabled - ignore legacy commands");
    }

    DebugEcho("tag_Date: end: $message_date");

    return $message_date;
}

function tag_Tags(&$content, $config) {
    $defaultTags = $config['default_post_tags'];
    DebugEcho("tag_Tags: starting");
    $post_tags = array();
    if (true == $config['legacy_commands']) {
        foreach (postie_content_lines($content, $config) as $line) {
            //DebugEcho("tag_Tags: line: $line");
            $matches = array();
            if (preg_match('/^\s*tags:\s*(.*)/imu', $line, $matches)) {
                if (!empty($matches[1])) {
                    DebugEcho("tag_Tags: Found tags: $matches[1]");
                    $content = str_replace($matches[0], "", $content);
                    $post_tags = array_merge($post_tags, array_filter(preg_split("/,\s*/", trim($matches[1]))));
                }
            }
        }
    } else {
        DebugEcho("tag_Tags: disabled - ignore legacy commands");
    }

    if (count($post_tags) == 0 && is_array($defaultTags)) {
        $post_tags = $defaultTags;
    }
    DebugEcho("tag_Tags: done");
    return $post_tags;
}
