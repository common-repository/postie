<?php

class PostieMessage {

    private $email = array();
    private $config = array();
    private $poster = 0;
    private $post_id = 0;
    private $is_reply = false;
    public $content = '';
    public $subject = '';

    public function __construct($email, $config) {

        $this->config = $config;

        if (is_array($email)) {
            if (!isset($email['html'])) {
                $email['html'] = '';
            }
            if (!isset($email['text'])) {
                $email['text'] = '';
            }
            if (!empty($email['html'])) {
                DebugEcho("getemails: html");
                $email['html'] = filter_CleanHtml($email['html']);
            } else {
                DebugEcho("getemails: no html");
            }

            if ($config['prefer_text_convert']) {
                if ($config['prefer_text_type'] == 'plain' && trim($email['text']) == '' && trim($email['html']) != '') {
                    DebugEcho('get_mail: switching to html');
                    $this->config['prefer_text_type'] = 'html';
                }
                if ($config['prefer_text_type'] == 'html' && trim($email['html']) == '' && trim($email['text']) != '') {
                    DebugEcho('get_mail: switching to plain');
                    $this->config['prefer_text_type'] = 'plain';
                }
            }
        }

        $this->email = $email;
    }

    function is_debugmode() {
        return (defined('POSTIE_DEBUG') && POSTIE_DEBUG == true);
    }

    function is_email_empty() {
        return $this->email == null;
    }

    function is_email_read() {
        return $this->email == 'already read';
    }

    function do_shortcodes($action, $details) {
        //get and save WP shortcodes
        global $shortcode_tags;
        $tmpshortcode = $shortcode_tags;
        $shortcode_tags = array();

        //add Postie specific shortcodes
        DebugEcho("process: filter: Before $action");
        do_action($action);

        //make the post details available to the shortcode handlers
        global $postie_post;
        $postie_post = $details;

        //fix quoting
        $content = $postie_post['post_content'];
        $char = array('&quot;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8242;', '&#8243;');
        $replace = array('"', "'", "'", '"', '"', "'", '"');
        $content = str_replace($char, $replace, $content);

        $newcontent = do_shortcode($content);
        $details = $postie_post; // need to copy values back in case a shortcode modified them
        $details['post_content'] = $newcontent;

        //restore the WP shortcodes
        $shortcode_tags = $tmpshortcode;

        return $details;
    }

    function preprocess() {
        DebugEcho("preprocess: Starting");
        $this->revisions_disable();

        /* in order to do attachments correctly, we need to associate the
          attachments with a post. So we add the post here, then update it */
        $tmpPost = array('post_title' => 'tmptitle', 'post_content' => 'tmpPost', 'post_status' => 'draft');
        $this->post_id = wp_insert_post($tmpPost, true);
        if (!is_wp_error($this->post_id)) {
            DebugEcho("preprocess: tmp post id is $this->post_id");
        } else {
            EchoError("preprocess: wp_insert_post failed: " . $this->post_id->get_error_message());
            DebugDump($this->post_id->get_error_messages());
            DebugDump($this->post_id->get_error_data());
            $this->email_error("preprocess: wp_insert_post failed creating placeholder", $this->post_id->get_error_message());
        }
        DebugEcho('preprocess: Done');
    }

    function process() {
        DebugEcho("process: Starting");

        if (is_wp_error($this->post_id)) {
            EchoError('process: Ignoring email - failed creating placeholder.');
            return;
        }

        //Check poster to see if a valid person
        $this->poster = $this->get_author();
        if (empty($this->poster)) {
            if ($this->config['forward_rejected_mail']) {
                $this->email_reject();
                DebugEcho("A copy of the message has been forwarded to the administrator.");
            }
            DebugEcho('process: not authorized, setting to draft.');
            $this->config['post_status'] = 'draft';
            DebugEcho("process: not authorized, looking up default user " . $this->config['admin_username']);
            $user = get_user_by('login', $this->config['admin_username']);
            $this->poster = $user->ID;
        }

        DebugEcho("process: filter: pre postie_post_pre");
        $this->email = apply_filters('postie_post_pre', $this->email);

        $this->extract_content();

        $this->extract_subject();

        $details = $this->create_post();

        DebugEcho('process: shortcodes: Before postie_register_shortcode_pre');
        $details = $this->do_shortcodes('postie_register_shortcode_pre', $details);
        DebugEcho("process: shortcodes: After do_shortcodes");
        DebugDump($details);

        DebugEcho('process: filter: Before postie_post_before');
        DebugEcho('process: header array');
        DebugDump($this->email['headers']);
        $details = apply_filters('postie_post', $details);
        $details = apply_filters('postie_post_before', $details, $this->email['headers']);
        DebugEcho("process: filter: After postie_post_before");
        DebugDump($details);

        if (empty($details) || $details['post_status'] == 'trash') {
            // It is possible that the filter or shortcode has removed the post, in which case, it should not be posted.
            // And if we created a placeholder post (because this was not a reply to an existing post),
            // then it should be removed
            if (!$this->is_reply) {
                wp_delete_post($this->post_id);
                DebugEcho("post_email: postie_post filter cleared the post, not saving. deleted $this->post_id");
            } else {
                DebugEcho("post_email: postie_post ended up with no post array.");
            }
            return;
        }

        $postid = $this->save_post($details, $this->is_reply);

        $recipients = array();
        $dest = $this->config['confirmation_email'];
        if ($dest == 'sender' || $dest == 'both') {
            $recipients[] = $details['email_author'];
        }
        if ($dest == 'admin' || $dest == 'both') {
            foreach (get_users(array('role' => 'administrator', 'blog_id' => get_current_blog_id())) as $user) {
                $recipients[] = $user->user_email;
            }
        }
        if (!($dest == 'admin' || $dest == 'sender' || $dest == 'both' || $dest == '')) {
            $user = get_user_by('login', $dest);
            $recipients[] = $user->user_email;
        }

        DebugEcho('post_email: sending notifications');
        $this->email_notify($recipients, $postid);

        if ($this->is_debugmode()) {
            $post = get_post($this->post_id);
            DebugEcho('post_email: resulting post');
            DebugDump($post);
        }

        DebugEcho("process: Done");

        return $details;
    }

    function postprocess() {

        $this->revisions_restore();
        DebugEcho("postprocess: Done");
    }

    /**
     * This method works around a problem with email address with extra <> in the email address
     * @param string
     * @return string
     */
    function get_clean_emailaddress($address) {
        $matches = array();
        if (preg_match('/^[^<>]+<([^<> ()]+)>$/', $address, $matches)) {
            $address = $matches[1];
            DebugEcho("RemoveExtraCharactersInEmailAddress: $address (1)");
            DebugDump($matches);
        } else if (preg_match('/<([^<> ()]+)>/', $address, $matches)) {
            $address = $matches[1];
            DebugEcho("RemoveExtraCharactersInEmailAddress: $address (2)");
        }

        return $address;
    }

    /**
     * This compares the current address to the list of authorized addresses
     * @param string - email address
     * @return boolean
     */
    function is_emailaddress_authorized($address, $authorized_addresses) {
        $r = false;
        if (is_array($authorized_addresses)) {
            $a = strtolower(trim($address));
            if (!empty($a)) {
                $r = in_array($a, array_map('strtolower', $authorized_addresses));
            }
        }
        return $r;
    }

    /**
     * Determines if the sender is a valid user.
     * @return integer|NULL
     */
    function get_author() {
        $poster = null;
        $from = '';

        if (array_key_exists('headers', $this->email) && array_key_exists('from', $this->email['headers'])) {
            $from = $this->email['headers']['from']['mailbox'] . '@' . $this->email['headers']['from']['host'];
            DebugEcho("validate_poster: initial '$from'");

            $from = apply_filters('postie_filter_email', $from);
            DebugEcho("validate_poster: post postie_filter_email '$from'");

            $toEmail = '';
            if (isset($this->email['headers']['to'])) {
                $toEmail = $this->email['headers']['to'][0]['mailbox'] . '@' . $this->email['headers']['to'][0]['host'];
            }

            $replytoEmail = '';
            if (isset($this->email['headers']['reply-to'])) {
                $replytoEmail = $this->email['headers']['reply-to']['mailbox'] . '@' . $this->email['headers']['reply-to']['host'];
            }

            $from = apply_filters("postie_filter_email2", $from, $toEmail, $replytoEmail);
            DebugEcho("validate_poster: post postie_filter_email2 '$from'");
        } else {
            DebugEcho("validate_poster: No 'from' header found");
            DebugDump($this->email['headers']);
        }

        if (array_key_exists('headers', $this->email)) {
            $from = apply_filters("postie_filter_email3", $from, $this->email['headers']);
            DebugEcho("validate_poster: post postie_filter_email3 '$from'");
        }

        $resentFrom = '';
        if (array_key_exists('headers', $this->email) && array_key_exists('resent-from', $this->email['headers'])) {
            $resentFrom = $this->get_clean_emailaddress(trim($this->email['headers']['resent-from']));
            DebugEcho("validate_poster: resent-from '$resentFrom'");
        }

        //See if the email address is one of the special authorized ones
        $user_ID = '';
        if (!empty($from)) {
            DebugEcho("validate_poster: Confirming Access For $from ");
            $user = get_user_by('email', $from);
            if ($user !== false) {
                if (is_user_member_of_blog($user->ID)) {
                    $user_ID = $user->ID;
                } else {
                    DebugEcho("validate_poster: $from is not user of blog " . get_current_blog_id());
                    EchoError('Invalid sender: ' . htmlentities($from) . "! Not adding email!");
                }
            }
        }

        if (!empty($user_ID)) {
            $user = new WP_User($user_ID);
            if ($user->has_cap('post_via_postie')) {
                DebugEcho("validate_poster: $user_ID has 'post_via_postie' permissions");
                $poster = $user_ID;

                DebugEcho("validate_poster: pre postie_author $poster");
                $poster = apply_filters("postie_author", $poster);
                DebugEcho("validate_poster: post postie_author $poster");
            } else {
                DebugEcho("validate_poster $user_ID does not have 'post_via_postie' permissions");
                $user_ID = '';
            }
        }

        if (empty($user_ID) && ($this->config['turn_authorization_off'] || $this->is_emailaddress_authorized($from, $this->config['authorized_addresses']) || $this->is_emailaddress_authorized($resentFrom, $this->config['authorized_addresses']))) {
            DebugEcho("validate_poster: looking up default user " . $this->config['admin_username']);
            $user = get_user_by('login', $this->config['admin_username']);
            if ($user === false) {
                EchoError("Your 'Default Poster' setting '" . $this->config['admin_username'] . "' is not a valid WordPress user (2)");
                $poster = 1;
            } else {
                $poster = $user->ID;
                DebugEcho("validate_poster: pre postie_author (default) $poster");
                $poster = apply_filters("postie_author", $poster);
                DebugEcho("validate_poster: post postie_author (default) $poster");
            }
            DebugEcho("validate_poster: found user '$poster'");
        }

        if ($poster) {
            //actually log in as the user
            if ($this->config['force_user_login'] == true) {
                $user = get_user_by('id', $poster);
                if ($user) {
                    DebugEcho("validate_poster: logging in as {$user->user_login}");
                    wp_set_current_user($poster);
                    //wp_set_auth_cookie($poster);
                    do_action('wp_login', $user->user_login, $user);
                } else {
                    DebugEcho("validate_poster: couldn't find $poster to force login");
                }
            }
        }

        return $poster;
    }

    function email_header_encode($value) {
        return '=?utf-8?b?' . base64_encode($value) . '?=';
    }

    function email_reject() {
        DebugEcho('email_reject: start');

        $recipients = array(get_option('admin_email'));
        $returnToSender = $this->config['return_to_sender'];

        $blogname = get_option('blogname');
        $from = $this->email['headers']['from']['mailbox'] . '@' . $this->email['headers']['from']['host'];

        $subject = $this->email['headers']['subject'];
        if ($returnToSender) {
            DebugEcho("email_reject: return to sender $from");
            array_push($recipients, $from);
        }

        $eblogname = $this->email_header_encode($blogname);
        $adminemail = get_option('admin_email');

        $headers = array();
        $headers[] = "From: $eblogname <$adminemail>";

        DebugEcho("email_reject: To:");
        DebugDump($recipients);
        DebugEcho("email_reject: header:");
        DebugDump($headers);

        $message = "An unauthorized message has been sent to $blogname.\n";
        $message .= "Sender: $from\n";
        $message .= "Subject: $subject\n";
        $message .= "\n\nIf you wish to allow posts from this address, please add " . $from . " to the registered users list and manually add the content of the email found below.";
        $message .= "\n\nOtherwise, the email has already been deleted from the server and you can ignore this message.";
        $message .= "\n\nIf you would like to prevent postie from forwarding mail in the future, please change the FORWARD_REJECTED_MAIL setting in the Postie settings panel";
        $message .= "\n\nThe original content of the email has been attached.\n\n";

        $recipients = apply_filters('postie_email_reject_recipients', $recipients, $this->email);
        if (count($recipients) == 0) {
            DebugEcho("email_reject: no recipients after postie_email_reject_recipients filter");
            return;
        }
        DebugEcho("email_reject: post postie_email_reject_recipients");
        DebugDump($recipients);

        $subject = $blogname . ": Unauthorized Post Attempt from $from";
        $subject = apply_filters('postie_email_reject_subject', $subject, $this->email);
        DebugEcho("email_reject: post postie_email_reject_subject: $subject");

        $message = apply_filters('postie_email_reject_body', $message, $this->email);
        DebugEcho("email_reject: post postie_email_reject_body: $message");

        $attachTxt = wp_tempnam() . '.txt';
        file_put_contents($attachTxt, $this->email['text']);

        $attachHtml = wp_tempnam() . '.htm';
        file_put_contents($attachHtml, $this->email['html']);

        wp_mail($recipients, $subject, $message, $headers, array($attachTxt, $attachHtml));

        unlink($attachTxt);
        unlink($attachHtml);
    }

    function revisions_disable() {
        global $_wp_post_type_features, $_postie_revisions;

        $_postie_revisions = false;
        if (isset($_wp_post_type_features['post']) && isset($_wp_post_type_features['post']['revisions'])) {
            $_postie_revisions = $_wp_post_type_features['post']['revisions'];
            unset($_wp_post_type_features['post']['revisions']);
        }
    }

    function revisions_restore() {
        global $_wp_post_type_features, $_postie_revisions;

        if ($_postie_revisions) {
            $_wp_post_type_features['post']['revisions'] = $_postie_revisions;
        }
    }

    function create_post() {
        DebugEcho("create_post: prefer_text_type: " . $this->config['prefer_text_type']);

        $fulldebug = $this->is_debugmode();
        $fulldebugdump = false;

        if (array_key_exists('message-id', $this->email['headers'])) {
            DebugEcho("Message Id is :" . htmlentities($this->email['headers']['message-id']));
        }

        if ($fulldebugdump) {
            DebugDump($this->email);
        }

        $this->save_attachments($this->post_id, $this->poster);

        $this->content = filter_RemoveSignature($this->content, $this->config);
        if ($fulldebug) {
            DebugEcho("post filter_RemoveSignature: $this->content");
        }

        $this->content = filter_Newlines($this->content, $this->config);
        if ($fulldebug) {
            DebugEcho("post filter_Newlines: $this->content");
        }

        $post_excerpt = tag_Excerpt($this->content, $this->config);
        if ($fulldebug) {
            DebugEcho("post tag_Excerpt: $this->content");
        }

        $postAuthorDetails = $this->get_author_details();
        if ($fulldebug) {
            DebugEcho("post getPostAuthorDetails: $this->content");
        }

        $message_date = NULL;
        $delay = 0;
        if (array_key_exists('date', $this->email['headers']) && !empty($this->email['headers']['date'])) {
            DebugEcho("date header: {$this->email['headers']['date']}");
            if ($this->config['ignore_email_date']) {
                $message_date = current_time('mysql');
                DebugEcho("system date: $message_date");
            } else {
                $message_date = $this->email['headers']['date'];
                DebugEcho("decoded date: $message_date");
                list($message_date, $delay) = tag_Delay($this->content, $message_date, $this->config);
                if ($fulldebug) {
                    DebugEcho("post tag_Delay: $this->content");
                }
            }
        } else {
            DebugEcho('date header missing');
            $message_date = current_time('mysql');
        }

        $post_date = tag_Date($this->content, $message_date, $this->config);
        if ($fulldebug) {
            DebugEcho("post tag_Date: $this->content");
        }


        //do post type before category to keep the subject line correct
        $post_type_format = tag_PostType($this->subject, $this->config);
        if ($fulldebug) {
            DebugEcho("post tag_PostType: $this->content");
        }

        $default_categoryid = $this->config['default_post_category'];

        DebugEcho("pre postie_category_default: '$default_categoryid'");
        $default_categoryid = apply_filters('postie_category_default', $default_categoryid);
        DebugEcho("post postie_category_default: '$default_categoryid'");

        $post_categories = tag_Categories($this->subject, $default_categoryid, $this->config, $this->post_id);
        if ($fulldebug) {
            DebugEcho("post tag_Categories: $this->content");
        }

        $post_tags = tag_Tags($this->content, $this->config);
        if ($fulldebug) {
            DebugEcho("post tag_Tags: $this->content");
        }

        $comment_status = tag_AllowCommentsOnPost($this->content, $this->config);
        if ($fulldebug) {
            DebugEcho("post tag_AllowCommentsOnPost: $this->content");
        }

        $post_status = tag_Status($this->content, $this->config);
        if ($fulldebug) {
            DebugEcho("post tag_Status: $this->content");
        }

        //handle CID before linkify
        $this->content = filter_ReplaceImageCIDs($this->content, $this->email);
        if ($fulldebug) {
            DebugEcho("post filter_ReplaceImageCIDs: $this->content");
        }

        if ($this->config['converturls']) {
            $this->content = filter_Linkify($this->content);
            if ($fulldebug) {
                DebugEcho("post filter_Linkify: $this->content");
            }
        }

        if ($this->config['reply_as_comment'] == true) {
            $id = $this->get_parent_postid($this->subject);
            if (empty($id)) {
                DebugEcho("Not a reply");
                $id = $this->post_id;
                $this->is_reply = false;
            } else {
                DebugEcho("Reply detected");
                $this->is_reply = true;
                if (true == $this->config['strip_reply']) {
                    // strip out quoted content
                    $lines = explode("\n", $this->content);
                    $newContents = '';
                    foreach ($lines as $line) {
                        if (preg_match("/^>.*/i", $line) == 0 &&
                                preg_match("/^(from|subject|to|date):.*?/iu", $line) == 0 &&
                                preg_match("/^-+.*?(from|subject|to|date).*?/iu", $line) == 0 &&
                                preg_match("/^on.*?wrote:$/iu", $line) == 0 &&
                                preg_match("/^-+\s*forwarded\s*message\s*-+/iu", $line) == 0) {
                            $newContents .= "$line\n";
                        }
                    }
                    if ((strlen($newContents) <> strlen($this->content)) && ('html' == $this->config['prefer_text_type'])) {
                        DebugEcho("Attempting to fix reply html (before): $newContents");
                        $newContents = $this->load_html($newContents)->__toString();
                        DebugEcho("Attempting to fix reply html (after): $newContents");
                    }
                    $this->content = $newContents;
                }
                wp_delete_post($this->post_id);
            }
        } else {
            $id = $this->post_id;
            DebugEcho("Replies will not be processed as comments");
        }

        if ($delay > 0 && $post_status == 'publish') {
            DebugEcho("publish in future");
            $post_status = 'future';
        }

        $this->content = filter_Start($this->content, $this->config);
        if ($fulldebug) {
            DebugEcho("post filter_Start: $this->content");
        }

        $this->content = filter_End($this->content, $this->config);
        if ($fulldebug) {
            DebugEcho("post filter_End: $this->content");
        }

        $this->content = filter_ReplaceImagePlaceHolders($this->content, $this->email, $this->config, $id, $this->config['image_placeholder']);
        if ($fulldebug) {
            DebugEcho("post filter_ReplaceImagePlaceHolders: $this->content");
        }

        if ($post_excerpt) {
            $post_excerpt = filter_ReplaceImagePlaceHolders($post_excerpt, $this->email, $this->config, $id, '#eimg%#');
            DebugEcho("excerpt: $post_excerpt");
            if ($fulldebug) {
                DebugEcho("post excerpt ReplaceImagePlaceHolders: $this->content");
            }
        }

        //handle inline images after linkify
        if ('plain' == $this->config['prefer_text_type']) {
            $this->content = filter_ReplaceInlineImage($this->content, $this->email, $this->config);
            if ($fulldebug) {
                DebugEcho("post filter_ReplaceInlineImage: $this->content");
            }
        }

        $this->content = filter_AttachmentTemplates($this->content, $this->email, $this->post_id, $this->config);

        if ($this->config[PostieConfigOptions::AddWrapperDiv]) {
            DebugEcho('Adding div');
            $this->content = '<div class="postie-post">' . $this->content . '</div>';
        }

        $details = array(
            'post_author' => $this->poster,
            'comment_author' => $postAuthorDetails['author'],
            'comment_author_url' => $postAuthorDetails['comment_author_url'],
            'user_ID' => $postAuthorDetails['user_ID'],
            'email_author' => $postAuthorDetails['email'],
            'post_date' => $post_date,
            'post_date_gmt' => get_gmt_from_date($post_date),
            'post_content' => $this->content,
            'post_title' => $this->subject,
            'post_type' => $post_type_format['post_type'],
            'ping_status' => get_option('default_ping_status'),
            'post_category' => $post_categories,
            'tags_input' => $post_tags,
            'comment_status' => $comment_status,
            'post_name' => $this->subject,
            'post_excerpt' => $post_excerpt,
            'ID' => $id,
            'post_status' => $post_status
        );

        //don't need to specify the post format to get a "standard" post
        if ($post_type_format['post_format'] !== 'standard') {
            //need to set post format differently since it is a type of taxonomy
            DebugEcho("Setting post format to {$post_type_format['post_format']}");
            wp_set_post_terms($this->post_id, $post_type_format['post_format'], 'post_format');
        }

        return $details;
    }

    function save_post($details, $isReply) {
        $post_ID = 0;
        $details['post_content'] = str_replace('\\', '\\\\', $details['post_content']); //replace all backslashs with double backslashes since WP will remove single backslash
        if (!$isReply) {
            DebugEcho("postie_save_post: about to insert post");
            if ($this->is_debugmode() && !defined('SAVEQUERIES')) {
                define('SAVEQUERIES', true);
            }

            $post_ID = wp_insert_post($details, true);

            if (is_wp_error($post_ID)) {
                EchoError("PostToDB Error: " . $post_ID->get_error_message());
                DebugDump($post_ID->get_error_messages());
                DebugDump($post_ID->get_error_data());

                global $wpdb;
                EchoError('PostToDB last_error: ' . $wpdb->last_error);
                EchoError('PostToDB last_query: ' . $wpdb->last_query);
                DebugDump($wpdb->queries);

                wp_delete_post($details['ID']);

                $this->email_error("Failed to create {$details['post_type']}: {$details['post_title']}", "Error: " . $post_ID->get_error_message() . "\n\n" . $details['post_content']);

                $post_ID = null;
            } else {
                DebugEcho("postie_save_post: post inserted");
            }
        } else {
            DebugEcho("postie_save_post: inserting comment");
            $comment = array(
                'comment_author' => $details['comment_author'],
                'comment_post_ID' => $details['ID'],
                'comment_author_email' => $details['email_author'],
                'comment_date' => $details['post_date'],
                'comment_date_gmt' => $details['post_date_gmt'],
                'comment_content' => $details['post_content'],
                'comment_author_url' => $details['comment_author_url'],
                'comment_author_IP' => '',
                'comment_approved' => 1,
                'comment_agent' => '',
                'comment_type' => '',
                'comment_parent' => 0,
                'user_id' => $details['user_ID']
            );
            $comment = apply_filters('postie_comment_before', $comment);
            DebugEcho("postie_save_post: post postie_comment_before");
            DebugDump($comment);

            $post_ID = wp_new_comment($comment);

            DebugEcho("doing postie_comment_after");
            do_action('postie_comment_after', $comment, $post_ID);
        }

        if ($post_ID) {
            DebugEcho("doing postie_post_after");
            do_action('postie_post_after', $details);
        }

        return $post_ID;
    }

    function email_error($subject, $message) {
        $recipients = array();
        if ($this->config['postie_log_error_notify'] == '(Nobody)') {
            return;
        }
        if ($this->config['postie_log_error_notify'] == '(All Admins)') {
            foreach (get_users(array('role' => 'administrator', 'blog_id' => get_current_blog_id())) as $user) {
                $recipients[] = $user->user_email;
            }
            if (count($recipients) == 0) {
                return;
            }
        } else {
            $user = get_user_by('login', $this->config['postie_log_error_notify']);
            if ($user === false) {
                return;
            }
            $recipients[] = $user->user_login;
        }

        $message = "This message has been sent from " . get_site_url() . ". You can disable or control who receives them by changing the Postie 'Notify on Error' setting.\n\n" . $message;
        wp_mail($recipients, $subject, $message);
    }

    function email_notify($recipients, $postid) {
        DebugEcho("email_notify: start");

        if (empty($postid)) {
            DebugEcho("email_notify: no post id");
            return;
        }

        $myemailadd = get_option('admin_email');
        $blogname = get_option('blogname');
        $eblogname = '=?utf-8?b?' . base64_encode($blogname) . '?= ';
        $posturl = get_permalink($postid);
        $subject = $this->subject;

        $sendheaders = array("From: $eblogname <$myemailadd>");

        $post_status = get_post_status($postid);

        $mailtext = sprintf(__("Your email '%s' has been successfully imported into %s %s with the current status of '%s'.\n", 'postie'),
                $subject, html_entity_decode($blogname), $posturl, $post_status);

        $recipients = apply_filters('postie_email_notify_recipients', $recipients, $this->email, $postid);
        if (count($recipients) == 0) {
            DebugEcho("email_notify: no recipients after postie_email_notify_recipients filter");
            return;
        }
        DebugEcho("email_notify: post postie_email_notify_recipients");
        DebugDump($recipients);

        $subject = sprintf(__('Email imported to %s (%s)', 'postie'), html_entity_decode($blogname), $post_status);
        $subject = apply_filters('postie_email_notify_subject', $subject, $this->email, $postid);
        DebugEcho("email_notify: post postie_email_notify_subject: $subject");

        $mailtext = apply_filters('postie_email_notify_body', $mailtext, $this->email, $postid);
        DebugEcho("email_notify: post postie_email_notify_body: $mailtext");

        wp_mail($recipients, $subject, $mailtext, $sendheaders);
    }

    function save_attachments($post_id, $poster) {
        DebugEcho('save_attachments: ---- start');

        if (!isset($this->email['attachment'])) {
            $this->email['attachment'] = array();
        }
        if (!isset($this->email['inline'])) {
            $this->email['inline'] = array();
        }
        if (!isset($this->email['related'])) {
            $this->email['related'] = array();
        }

        DebugEcho("save_attachments: [attachment]");
        $this->save_attachments_worker($this->email['attachment'], $post_id, $poster);
        DebugEcho("save_attachments: [inline]");
        $this->save_attachments_worker($this->email['inline'], $post_id, $poster);
        DebugEcho("save_attachments: [related]");
        $this->save_attachments_worker($this->email['related'], $post_id, $poster);

        DebugEcho("save_attachments: ==== end");
    }

    function save_attachments_worker(&$attachments, $post_id, $poster) {
        DebugEcho("save_attachments_worker: start");
        foreach ($attachments as &$attachment) {
            foreach ($attachment as $key => $value) {
                if ($key != 'data') {
                    DebugEcho("save_attachments_worker: [$key]: $value");
                }
            }
            if (array_key_exists('filename', $attachment) && !empty($attachment['filename'])) {
                DebugEcho('save_attachments_worker: ' . $attachment['filename']);

                if ($this->is_filename_banned($attachment['filename'])) {
                    DebugEcho("save_attachments_worker: skipping banned filename " . $attachment['filename']);
                    continue;
                }

                if (false === apply_filters('postie_include_attachment', true, $attachment)) {
                    DebugEcho("save_attachments_worker: skipping filename by filter " . $attachment['filename']);
                    continue;
                }
            } else {
                DebugEcho('save_attachments_worker: un-named attachment');
            }

            $this->save_attachment($attachment, $post_id, $poster);

            $filename = $attachment['wp_filename'];
            $fileext = $attachment['ext'];
            $mparts = explode('/', $attachment['mimetype']);
            $mimetype_primary = $mparts[0];
            $mimetype_secondary = $mparts[1];
            DebugEcho("save_attachments_worker: mime primary: $mimetype_primary");

            $attachment['primary'] = $mimetype_primary;
            $attachment['exclude'] = false;

            $file_id = $attachment['wp_id'];
            $file = wp_get_attachment_url($file_id);

            switch ($mimetype_primary) {
                case 'text':
                    DebugEcho("save_attachments_worker: text attachment");
                    $icon = $this->get_attachment_icon($file, $mimetype_primary, $mimetype_secondary, $this->config['icon_set'], $this->config['icon_size']);
                    $attachment['template'] = "<a href='$file'>" . $icon . $filename . '</a>' . "\n";
                    break;

                case 'image':
                    DebugEcho("save_attachments_worker: image attachment");
                    $attachment['template'] = $this->parse_template($file_id, $mimetype_primary, $this->config['imagetemplate'], $filename) . "\n";
                    break;

                case 'audio':
                    DebugEcho("save_attachments_worker: audio attachment");
                    if (in_array($fileext, $this->config['audiotypes'])) {
                        DebugEcho("save_attachments_worker: using audio template: $mimetype_secondary");
                        $audioTemplate = $this->config['audiotemplate'];
                    } else {
                        DebugEcho("save_attachments_worker: using default audio template: $mimetype_secondary");
                        $icon = $this->get_attachment_icon($file, $mimetype_primary, $mimetype_secondary, $this->config['icon_set'], $this->config['icon_size']);
                        $audioTemplate = '<a href="{FILELINK}">' . $icon . '{FILENAME}</a>';
                    }
                    $attachment['template'] = $this->parse_template($file_id, $mimetype_primary, $audioTemplate, $filename);
                    break;

                case 'video':
                    DebugEcho("save_attachments_worker: video attachment");
                    if (in_array($fileext, $this->config['video1types'])) {
                        DebugEcho("save_attachments_worker: using video1 template: $fileext");
                        $videoTemplate = $this->config['video1template'];
                    } elseif (in_array($fileext, $this->config['video2types'])) {
                        DebugEcho("save_attachments_worker: using video2 template: $fileext");
                        $videoTemplate = $this->config['video2template'];
                    } else {
                        DebugEcho("save_attachments_worker: using default template: $fileext");
                        $icon = $this->get_attachment_icon($file, $mimetype_primary, $mimetype_secondary, $this->config['icon_set'], $this->config['icon_size']);
                        $videoTemplate = '<a href="{FILELINK}">' . $icon . '{FILENAME}</a>';
                    }
                    $attachment['template'] = $this->parse_template($file_id, $mimetype_primary, $videoTemplate, $filename);
                    break;

                default :
                    DebugEcho("save_attachments_worker: generic attachment ($mimetype_primary)");
                    $icon = $this->get_attachment_icon($file, $mimetype_primary, $mimetype_secondary, $this->config['icon_set'], $this->config['icon_size']);
                    $attachment['template'] = $this->parse_template($file_id, $mimetype_primary, $this->config['generaltemplate'], $filename, $icon) . "\n";
                    break;
            }
            DebugEcho("save_attachments_worker: done with $filename");
        }
        DebugEcho("save_attachments_worker: end");
    }

    function save_attachment(&$attachment, $post_id, $poster) {

        if (isset($attachment['filename']) && !empty($attachment['filename'])) {
            $filename = $attachment['filename'];
        } else {
            DebugEcho("save_attachment: generating file name");
            $filename = uniqid();
            $mparts = explode('/', $attachment['mimetype']);
            $attachment['filename'] = $filename . '.' . $mparts[1];
        }

        DebugEcho("save_attachment: pre sanitize file name '$filename'");
        //DebugDump($part);
        $filename = sanitize_file_name($filename);
        $attachment['wp_filename'] = $filename;

        DebugEcho("save_attachment: file name '$filename'");

        $mparts = explode('/', $attachment['mimetype']);
        $mimetype_primary = $mparts[0];
        $mimetype_secondary = $mparts[1];

        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $attachment['ext'] = $fileext;
        if (empty($fileext)) {
            $attachment['ext'] = $mimetype_secondary;
            $filename = $filename . '.' . $mimetype_secondary;
            $attachment['wp_filename'] = $filename;
            $fileext = $mimetype_secondary;
            DebugEcho("save_attachment: blank file extension, changed to $mimetype_secondary ($filename)");
        }
        DebugEcho("save_attachment: extension '$fileext'");

        $typeinfo = wp_check_filetype($filename);
        //DebugDump($typeinfo);
        if (!empty($typeinfo['type'])) {
            DebugEcho("save_attachment: secondary lookup found " . $typeinfo['type']);
            $mimeparts = explode('/', strtolower($typeinfo['type']));
            $mimetype_primary = $mimeparts[0];
            $mimetype_secondary = $mimeparts[1];
        } else {
            DebugEcho("save_attachment: secondary lookup failed, checking configured extensions");
            if (in_array($fileext, $this->config['audiotypes'])) {
                DebugEcho("save_attachment: found audio extension");
                $mimetype_primary = 'audio';
                $mimetype_secondary = $fileext;
            } elseif (in_array($fileext, array_merge($this->config['video1types'], $this->config['video2types']))) {
                DebugEcho("save_attachment: found video extension");
                $mimetype_primary = 'video';
                $mimetype_secondary = $fileext;
            } else {
                DebugEcho("save_attachment: found no extension");
            }
        }
        $attachment['mimetype'] = "$mimetype_primary/$mimetype_secondary";
        DebugEcho("save_attachment: mimetype $mimetype_primary/$mimetype_secondary");

        $attachment['wp_id'] = 0;

        switch ($mimetype_primary) {
            case 'text':
                DebugEcho("save_attachment: ctype_primary: text");
                //DebugDump($part);

                DebugEcho("save_attachment: text Attachement: $filename");
                $file_id = $this->media_handle_upload($attachment, $post_id, $poster);
                if (!is_wp_error($file_id)) {
                    $attachment['wp_id'] = $file_id;
                    DebugEcho("save_attachment: text attachment: adding '$filename'");
                } else {
                    EchoError($file_id->get_error_message());
                    $this->email_error("Failed to add text media file: $filename", $file_id->get_error_message());
                }

                break;

            case 'image':
                DebugEcho("save_attachment: image Attachement: $filename");
                $file_id = $this->media_handle_upload($attachment, $post_id, $poster);
                if (!is_wp_error($file_id)) {
                    $attachment['wp_id'] = $file_id;
                    //set the first image we come across as the featured image
                    if ($this->config['featured_image'] && !has_post_thumbnail($post_id)) {
                        DebugEcho("save_attachment: featured image: $file_id");
                        set_post_thumbnail($post_id, $file_id);
                    }
                } else {
                    EchoError("save_attachment image error: " . $file_id->get_error_message());
                    $this->email_error("Failed to add image media file: $filename", $file_id->get_error_message());
                }
                break;

            case 'audio':
                DebugEcho("save_attachment: audio Attachement: $filename");
                $file_id = $this->media_handle_upload($attachment, $post_id, $poster);
                if (!is_wp_error($file_id)) {
                    $attachment['wp_id'] = $file_id;
                } else {
                    EchoError("save_attachment audio error: " . $file_id->get_error_message());
                    $this->email_error("Failed to add audio media file: $filename", $file_id->get_error_message());
                }
                break;

            case 'video':
                DebugEcho("save_attachment: video Attachement: $filename");
                $file_id = $this->media_handle_upload($attachment, $post_id, $poster);
                if (!is_wp_error($file_id)) {
                    $attachment['wp_id'] = $file_id;
                } else {
                    EchoError("save_attachment video error: " . $file_id->get_error_message());
                    $this->email_error("Failed to add video file: $filename", $file_id->get_error_message());
                }
                break;

            default:
                DebugEcho("save_attachment: found file type: " . $mimetype_primary);
                if (in_array($mimetype_primary, $this->config['supported_file_types'])) {
                    //pgp signature - then forget it
                    if ($mimetype_secondary == 'pgp-signature') {
                        DebugEcho("save_attachment: found pgp-signature - done");
                        break;
                    }
                    $file_id = $this->media_handle_upload($attachment, $post_id, $poster);
                    if (!is_wp_error($file_id)) {
                        $attachment['wp_id'] = $file_id;
                        $file = wp_get_attachment_url($file_id);
                        DebugEcho("save_attachment: uploaded $file_id ($file)");
                    } else {
                        EchoError("save_attachment file error: " . $file_id->get_error_message());
                        $this->email_error("Failed to add media file: $filename", $file_id->get_error_message());
                    }
                } else {
                    EchoError("$filename has an unsupported MIME type '$mimetype_primary' and was not added.");
                    DebugEcho("save_attachment: Not in supported filetype list: '$mimetype_primary'");
                    DebugDump($this->config['supported_file_types']);
                    $this->email_error("Unsupported MIME type: $mimetype_primary", "$filename has an unsupported MIME type $mimetype_primary and was not added.\nSupported types:\n" . print_r($this->config['supported_file_types'], true));
                }
                break;
        }
    }

    /**
     * Choose an appropriate file icon based on the extension and mime type of
     * the attachment
     */
    function get_attachment_icon($file, $primary, $secondary, $iconSet = 'silver', $size = '32') {
        if ($iconSet == 'none') {
            return('');
        }
        $fileName = basename($file);
        $parts = explode('.', $fileName);
        $ext = $parts[count($parts) - 1];
        $docExts = array('doc', 'docx');
        $docMimes = array('msword', 'vnd.ms-word', 'vnd.openxmlformats-officedocument.wordprocessingml.document');
        $pptExts = array('ppt', 'pptx');
        $pptMimes = array('mspowerpoint', 'vnd.ms-powerpoint', 'vnd.openxmlformats-officedocument.');
        $xlsExts = array('xls', 'xlsx');
        $xlsMimes = array('msexcel', 'vnd.ms-excel', 'vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $iWorkMimes = array('zip', 'octet-stream');
        $mpgExts = array('mpg', 'mpeg', 'mp2');
        $mpgMimes = array('mpg', 'mpeg', 'mp2');
        $mp3Exts = array('mp3');
        $mp3Mimes = array('mp3', 'mpeg3', 'mpeg');
        $mp4Exts = array('mp4', 'm4v');
        $mp4Mimes = array('mp4', 'mpeg4', 'octet-stream');
        $aacExts = array('m4a', 'aac');
        $aacMimes = array('m4a', 'aac', 'mp4');
        $aviExts = array('avi');
        $aviMimes = array('avi', 'x-msvideo');
        $movExts = array('mov');
        $movMimes = array('mov', 'quicktime');
        if ($ext == 'pdf' && $secondary == 'pdf') {
            $fileType = 'pdf';
        } else if ($ext == 'pages' && in_array($secondary, $iWorkMimes)) {
            $fileType = 'pages';
        } else if ($ext == 'numbers' && in_array($secondary, $iWorkMimes)) {
            $fileType = 'numbers';
        } else if ($ext == 'key' && in_array($secondary, $iWorkMimes)) {
            $fileType = 'key';
        } else if (in_array($ext, $docExts) && in_array($secondary, $docMimes)) {
            $fileType = 'doc';
        } else if (in_array($ext, $pptExts) && in_array($secondary, $pptMimes)) {
            $fileType = 'ppt';
        } else if (in_array($ext, $xlsExts) && in_array($secondary, $xlsMimes)) {
            $fileType = 'xls';
        } else if (in_array($ext, $mp4Exts) && in_array($secondary, $mp4Mimes)) {
            $fileType = 'mp4';
        } else if (in_array($ext, $movExts) && in_array($secondary, $movMimes)) {
            $fileType = 'mov';
        } else if (in_array($ext, $aviExts) && in_array($secondary, $aviMimes)) {
            $fileType = 'avi';
        } else if (in_array($ext, $mp3Exts) && in_array($secondary, $mp3Mimes)) {
            $fileType = 'mp3';
        } else if (in_array($ext, $mpgExts) && in_array($secondary, $mpgMimes)) {
            $fileType = 'mpg';
        } else if (in_array($ext, $aacExts) && in_array($secondary, $aacMimes)) {
            $fileType = 'aac';
        } else {
            $fileType = 'default';
        }
        $fileName = "/icons/$iconSet/$fileType-$size.png";
        if (!file_exists(POSTIE_ROOT . $fileName)) {
            $fileName = "/icons/$iconSet/default-$size.png";
        }
        $iconHtml = "<img src='" . POSTIE_URL . $fileName . "' alt='$fileType icon' />";
        DebugEcho("icon: $iconHtml");
        return $iconHtml;
    }

    function parse_template($fileid, $type, $template, $orig_filename, $icon = "") {
        $template = trim($template);
        DebugEcho("parseTemplate: before '$template'");

        $attachment = get_post($fileid);
        if (!empty($attachment)) {
            $uploadDir = wp_upload_dir();
            $fileName = basename($attachment->guid);
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
            $absFileName = $uploadDir['path'] . '/' . $fileName;
            $relFileName = str_replace(ABSPATH, '', $absFileName);
            $fileLink = wp_get_attachment_url($fileid);
            $pageLink = get_attachment_link($fileid);

            $template = str_replace('{TITLE}', $attachment->post_title, $template);
            $template = str_replace('{ID}', $fileid, $template);

            if ($type == 'image') {
                $widths = array();
                $heights = array();
                $img_urls = array();

                /*
                 * Possible enhancement: support all sizes returned by get_intermediate_image_sizes()
                 */
                $sizes = array('thumbnail', 'medium', 'large');
                for ($i = 0; $i < count($sizes); $i++) {
                    $iinfo = image_downsize($fileid, $sizes[$i]);
                    if (false !== $iinfo) {
                        $img_urls[$i] = $iinfo[0];
                        $widths[$i] = $iinfo[1];
                        $heights[$i] = $iinfo[2];
                    }
                }
                DebugEcho('parseTemplate: Sources');
                DebugDump($img_urls);
                DebugEcho('parseTemplate: Heights');
                DebugDump($heights);
                DebugEcho('parseTemplate: Widths');
                DebugDump($widths);

                $template = str_replace('{THUMBNAIL}', $img_urls[0], $template);
                $template = str_replace('{THUMB}', $img_urls[0], $template);
                $template = str_replace('{MEDIUM}', $img_urls[1], $template);
                $template = str_replace('{LARGE}', $img_urls[2], $template);
                $template = str_replace('{THUMBWIDTH}', $widths[0] . 'px', $template);
                $template = str_replace('{THUMBHEIGHT}', $heights[0] . 'px', $template);
                $template = str_replace('{MEDIUMWIDTH}', $widths[1] . 'px', $template);
                $template = str_replace('{MEDIUMHEIGHT}', $heights[1] . 'px', $template);
                $template = str_replace('{LARGEWIDTH}', $widths[2] . 'px', $template);
                $template = str_replace('{LARGEHEIGHT}', $heights[2] . 'px', $template);
            }

            $template = str_replace('{FULL}', $fileLink, $template);
            $template = str_replace('{FILELINK}', $fileLink, $template);
            $template = str_replace('{FILETYPE}', $fileType, $template);
            $template = str_replace('{PAGELINK}', $pageLink, $template);
            $template = str_replace('{FILENAME}', $orig_filename, $template);
            $template = str_replace('{IMAGE}', $fileLink, $template);
            $template = str_replace('{URL}', $fileLink, $template);
            $template = str_replace('{RELFILENAME}', $relFileName, $template);
            $template = str_replace('{ICON}', $icon, $template);
            $template = str_replace('{FILEID}', $fileid, $template);

            $template = $template . (empty($template) ? '' : '<br />');
            DebugEcho("parseTemplate: after: '$template'");
            return $template;
        } else {
            EchoError("parseTemplate: couldn't get attachment $fileid");
            return '';
        }
    }

    /**
     * This function determines if the mime attachment is on the BANNED_FILE_LIST
     * @param string
     * @return boolean
     */
    function is_filename_banned($filename) {
        if (preg_match('/ATT\d\d\d\d\d.txt/i', $filename)) {
            return true;
        }

        $bannedFiles = $this->config['banned_files_list'];

        if (empty($filename) || empty($bannedFiles)) {
            return false;
        }

        foreach ($bannedFiles as $bannedFile) {
            if (fnmatch($bannedFile, $filename)) {
                EchoError("Ignoring attachment: $filename - it is on the banned files list.");
                $this->email_error("Ignoring attachment: $filename - it is on the banned files list.", "Ignoring attachment: $filename - it is on the banned files list.");
                return true;
            }
        }
        return false;
    }

    function extract_content() {
        $this->content = '';
        if ($this->config['prefer_text_type'] == 'html') {
            if (isset($this->email['html'])) {
                DebugEcho('get_content: html');
                $this->content = $this->email['html'];
            }
        } else {
            if (isset($this->email['text'])) {
                DebugEcho('get_content: plain');
                $this->content = $this->email['text'];
            }
        }
    }

    /**
     * This function looks for a subject at the beginning surrounded by # and then removes that from the content
     */
    function extract_subject_body() {
        DebugEcho("tag_Subject: Looking for subject in email body");

        if (strlen($this->content) == 0 || substr($this->content, 0, 1) != "#") {
            DebugEcho("tag_Subject: No inline subject found [1]");
            return;
        }
        //make sure the first line isn't #img
        if (strtolower(substr($this->content, 1, 3)) != 'img') {
            $subjectEndIndex = strpos($this->content, "#", 1);
            if (!$subjectEndIndex > 0) {
                DebugEcho("tag_Subject: No subject found [2]");
                return;
            }
            $this->subject = substr($this->content, 1, $subjectEndIndex - 1);
            $this->content = substr($this->content, $subjectEndIndex + 1, strlen($this->content));
            DebugEcho("tag_Subject: Subject found in body: $this->subject");
        }
    }

    /**
     * This function handles finding and setting the correct subject
     */
    function extract_subject() {
        //assign the default title/subject
        $this->subject = $this->config[PostieConfigOptions::DefaultTitle];

        if (empty($this->email['headers']['subject'])) {
            DebugEcho("get_subject: No subject in email");
            if ($this->config['allow_subject_in_mail']) {
                $this->extract_subject_body();
            }
            $this->email['headers']['subject'] = $this->subject;
        } else {
            $this->subject = $this->email['headers']['subject'];
            DebugEcho(("get_subject: Predecoded subject: $this->subject"));

            if ($this->config['allow_subject_in_mail']) {
                $this->extract_subject_body();
            }
        }
        if (!$this->config['allow_html_in_subject']) {
            DebugEcho("get_subject: subject before htmlentities: $this->subject");
            $this->subject = htmlentities($this->subject, ENT_COMPAT);
            DebugEcho("get_subject: subject after htmlentities: $this->subject");
        }

        //This is for ISO-2022-JP - Can anyone confirm that this is still neeeded?
        // escape sequence is 'ESC $ B' == 1b 24 42 hex.
        if (strpos($this->subject, "\x1b\x24\x42") !== false) {
            // found iso-2022-jp escape sequence in subject... convert!
            DebugEcho("get_subject: extra parsing for ISO-2022-JP");
            $this->subject = iconv("ISO-2022-JP", "UTF-8//TRANSLIT", $this->subject);
        }

        DebugEcho("get_subject: pre postie_subject: '$this->subject'");
        $this->subject = apply_filters('postie_subject', $this->subject);

        DebugEcho("get_subject: '$this->subject'");
    }

    function get_author_details() {

        $from = $this->email['headers']['from']['mailbox'] . '@' . $this->email['headers']['from']['host'];

        $wpuser = get_user_by('email', $from);
        if ($wpuser !== false) {
            $name = $wpuser->user_login;
            $url = $wpuser->user_url;
            $id = $wpuser->ID;
        } else {
            $name = $this->get_name_from_email($from);
            $url = '';
            $id = '';
        }

        $author_details = array(
            'author' => $name,
            'comment_author_url' => $url,
            'user_ID' => $id,
            'email' => $from
        );

        return $author_details;
    }

    /**
     * This function gleans the name from an email address if available. If not
     * it just returns the username (everything before @)
     */
    function get_name_from_email($address) {
        $name = "";
        $matches = array();
        if (preg_match('/^([^<>]+)<([^<> ()]+)>$/', $address, $matches)) {
            $name = $matches[1];
        } else if (preg_match('/<([^<>@ ()]+)>/', $address, $matches)) {
            $name = $matches[1];
        } else if (preg_match('/(.+?)@(.+)/', $address, $matches)) {
            $name = $matches[1];
        }

        return trim($name);
    }

    function load_html($text) {
        return str_get_html($text, true, true, DEFAULT_TARGET_CHARSET, false);
    }

    function media_handle_upload($attachment, $post_id, $poster) {

        $tmpFile = tempnam(get_temp_dir(), 'postie');
        if ($tmpFile !== false) {
            $fp = fopen($tmpFile, 'w');
            if ($fp) {
                fwrite($fp, $attachment['data']);
                fclose($fp);
                DebugEcho("media_handle_upload: wrote data to '$tmpFile'");
            } else {
                EchoError("media_handle_upload: Could not write to temp file: '$tmpFile' ");
                $this->email_error("media_handle_upload: Could not write to temp file: '$tmpFile' ", "media_handle_upload: Could not write to temp file: '$tmpFile' ");
            }
        } else {
            EchoError("media_handle_upload: Could not create temp file in " . get_temp_dir());
            $this->email_error("media_handle_upload: Could not create temp file in " . get_temp_dir(), "media_handle_upload: Could not create temp file in " . get_temp_dir());
        }

        $file_array = array(
            'name' => $attachment['wp_filename'],
            'type' => $attachment['mimetype'],
            'tmp_name' => $tmpFile,
            'error' => 0,
            'size' => filesize($tmpFile)
        );
        DebugDump($file_array);

        DebugEcho("doing postie_file_added_pre");
        do_action('postie_file_added_pre', $post_id, $file_array);

        DebugEcho("media_handle_sideload: adding " . $file_array['name']);

        $id = media_handle_sideload($file_array, $post_id);

        if (!is_wp_error($id)) {
            DebugEcho("media_handle_upload: changing post_author to $poster");

            $mediapath = get_attached_file($id);

            $title = $file_array['name'];
            $excerpt = '';
            //TODO once WordPress is fixed this can be removed. https://core.trac.wordpress.org/ticket/39521
            if (0 === strpos($attachment['mimetype'], 'image/') && $image_meta = wp_read_image_metadata($mediapath)) {
                if (trim($image_meta['title']) && !is_numeric(sanitize_title($image_meta['title']))) {
                    $title = $image_meta['title'];
                    DebugEcho("media_handle_upload: changing post_title to $title");
                }

                if (trim($image_meta['caption'])) {
                    $excerpt = $image_meta['caption'];
                    DebugEcho("media_handle_upload: changing post_excerpt to $excerpt");
                }
            }

            wp_update_post(array(
                'ID' => $id,
                'post_author' => $poster,
                'post_title' => $title,
                'post_excerpt' => $excerpt,
            ));

            $file_array['tmp_name'] = $mediapath;
            DebugEcho("media_handle_upload: doing postie_file_added");
            do_action('postie_file_added', $post_id, $id, $file_array);
        } else {
            EchoError("There was an error adding the attachment: " . $id->get_error_message());
            DebugDump($id->get_error_messages());
            DebugDump($id->get_error_data());
            $this->email_error("There was an error adding the attachment: " . $attachment['wp_filename'], $id->get_error_message());
        }

        return $id;
    }

    /* we check whether or not the email is a reply to a previously
     * published post. First we check whether it starts with Re:, and then
     * we see if the remainder matches an already existing post. If so,
     * then we add that post id to the details array, which will cause the
     * existing post to be overwritten, instead of a new one being
     * generated
     */

    function get_parent_postid(&$subject) {
        global $wpdb;

        $id = NULL;
        DebugEcho("get_parent_postid: Looking for parent '$subject'");
        // see if subject starts with Re:
        $matches = array();
        if (preg_match("/(^Re:)(.*)/iu", $subject, $matches)) {
            DebugEcho("get_parent_postid: Re: detected");
            $subject = trim($matches[2]);
            // strip out category info into temporary variable
            $tmpSubject = $subject;
            if (preg_match('/(.+): (.*)/u', $tmpSubject, $matches)) {
                $tmpSubject = trim($matches[2]);
                $matches[1] = array($matches[1]);
            } else if (preg_match_all('/\[(.[^\[]*)\]/', $tmpSubject, $matches)) {
                $tmpSubject_matches = array();
                preg_match("/](.[^\[]*)$/", $tmpSubject, $tmpSubject_matches);
                $tmpSubject = trim($tmpSubject_matches[1]);
            } else if (preg_match_all('/-(.[^-]*)-/', $tmpSubject, $matches)) {
                preg_match("/-(.[^-]*)$/", $tmpSubject, $tmpSubject_matches);
                $tmpSubject = trim($tmpSubject_matches[1]);
            }
            DebugEcho("get_parent_postid: tmpSubject: $tmpSubject");
            $checkExistingPostQuery = "SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s AND post_status = 'publish' AND comment_status = 'open' AND post_type=%s ORDER BY post_date DESC";
            $id = $wpdb->get_var($wpdb->prepare($checkExistingPostQuery, $tmpSubject, $this->config[PostieConfigOptions::PostType]));
            if (empty($id)) {
                DebugEcho("get_parent_postid: No parent id found");
            } else {
                DebugEcho("get_parent_postid: id: $id");
            }
        } else {
            DebugEcho("get_parent_postid: No parent found");
        }

        $id = apply_filters('postie_parent_post', $id, $this->email);
        DebugEcho("get_parent_postid: After postie_parent_post: $id");

        return $id;
    }
}
