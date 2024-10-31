<?php

class PostieAutolink {
#
# A PHP auto-linking library
#
# https://github.com/iamcal/lib_autolink
#
# By Cal Henderson <cal@iamcal.com>
# This code is licensed under the MIT license
#
# Modified by Wayne Allen to work with Postie
#  * Wrapped in class
#  * Global options removed
#
####################################################################

    function autolink($text, $oembed = null, $limit = 200, $tagfill = '', $auto_title = true) {

        $text = $this->autolink_do($text, '![a-z][a-z-]+:\/\/!i', $limit, $tagfill, $auto_title, null, $oembed);
        $text = $this->autolink_do($text, '!(mailto|skype):!i', $limit, $tagfill, $auto_title);
        $text = $this->autolink_do($text, '!www\.!i', $limit, $tagfill, $auto_title, 'http://', $oembed);
        return $text;
    }

    function autolink_do($text, $sub, $limit, $tagfill, $auto_title, $force_prefix = null, $oembed = null) {

        $text_l = strtolower($text);
        $cursor = 0;
        $loop = 1;
        $buffer = '';

        while (($cursor < strlen($text)) && $loop) {

            $ok = 1;
            $m = array();
            $matched = preg_match($sub, $text_l, $m, PREG_OFFSET_CAPTURE, $cursor);

            if (!$matched) {
                $loop = 0;
                $ok = 0;
            } else {

                $pos = $m[0][1];
                $sub_len = strlen($m[0][0]);

                $pre_hit = substr($text, $cursor, $pos - $cursor);
                $hit = substr($text, $pos, $sub_len);
                $pre = substr($text, 0, $pos);
                $post = substr($text, $pos + $sub_len);

                $fail_text = $pre_hit . $hit;
                $fail_len = strlen($fail_text);

                #
                # substring found - first check to see if we're inside a link tag already...
                #

                $bits = preg_split("!</a>!i", $pre);
                $last_bit = array_pop($bits);
                if (preg_match("!<a\s!i", $last_bit)) {

                    //DebugEcho("autolink_do: fail 1 at $cursor");

                    $ok = 0;
                    $cursor += $fail_len;
                    $buffer .= $fail_text;
                }

                if ($ok) {
                    #
                    # check to see if we're inside a shortcode
                    #
                    $bits = preg_split("!\]!i", $pre);
                    $last_bit = array_pop($bits);
                    DebugEcho("autolink_do: check this for '[' $last_bit");
                    if (preg_match("!\[!i", $last_bit)) {

                        DebugEcho("autolink_do: found '[' so not linkifying (in a shortcode)");

                        $ok = 0;
                        $cursor += $fail_len;
                        $buffer .= $fail_text;
                    }
                }

                if ($ok) {
                    #
                    # check to see if we're inside a style attribute
                    #
                    
                    $needle = 'url(';
                    $expectedPosition = strlen($pre) - strlen($needle);
                    if (strripos($pre, $needle, 0) === $expectedPosition) {
                        DebugEcho("autolink_do: found 'url(' so not linkifying (in a style attribute)");

                        $ok = 0;
                        $cursor += $fail_len;
                        $buffer .= $fail_text;
                    }
                }
            }

            #
            # looks like a nice spot to autolink from - check the pre
            # to see if there was whitespace before this match
            #

            if ($ok) {

                if ($pre) {
                    if (!preg_match('![\s\(\[\{><]$!s', $pre)) {

                        //DebugEcho("autolink_do: fail 2 at $cursor ($pre)");

                        $ok = 0;
                        $cursor += $fail_len;
                        $buffer .= $fail_text;
                    }
                }
            }

            #
            # we want to autolink here - find the extent of the url
            #

            if ($ok) {
                $matches = array();
                if (preg_match('/^([a-z0-9\-\.\/\-_%~!?=,:;&+*#@\(\)\$°’;]+)/i', $post, $matches)) {

                    $url = $hit . $matches[1];

                    $cursor += strlen($url) + strlen($pre_hit);
                    $buffer .= $pre_hit;

                    $url = html_entity_decode($url);

                    #
                    # remove trailing punctuation from url
                    #

                    while (preg_match('|[.,!;:?]$|', $url)) {
                        $url = substr($url, 0, strlen($url) - 1);
                        $cursor--;
                    }
                    foreach (array('()', '[]', '{}') as $pair) {
                        $o = substr($pair, 0, 1);
                        $c = substr($pair, 1, 1);
                        if (preg_match("!^(\\$c|^)[^\\$o]+\\$c$!", $url)) {
                            $url = substr($url, 0, strlen($url) - 1);
                            $cursor--;
                        }
                    }


                    #
                    # nice-i-fy url here
                    #

                    $link_url = $url;

                    $display_url = $url;

                    if ($force_prefix) {
                        $link_url = $force_prefix . $link_url;
                    }

                    if (preg_match('!^(http|https)://!i', $display_url, $m)) {
                        $display_url = substr($display_url, strlen($m[1]) + 3);
                    }

                    $display_url = $this->autolink_label($display_url, $limit);

                    #
                    # add the url
                    #

                    $currentTagfill = $tagfill;
                    if ($display_url != $link_url && !preg_match('@title=@msi', $currentTagfill) && $auto_title) {

                        $display_quoted = preg_quote($display_url, '!');

                        if (!preg_match("!^(http|https)://{$display_quoted}$!i", $link_url)) {

                            $currentTagfill .= ' title="' . $link_url . '"';
                        }
                    }

                    DebugEcho("autolink_do: link=$link_url");

                    $skip = false;

                    if (!empty($oembed) && method_exists($oembed, 'get_provider')) {
                        $provider = $oembed->get_provider($link_url, array('discover' => false));
                        if (false !== $provider) {
                            DebugEcho("autolink_do: provider=$provider");
                            $skip = true;
                        } else {
                            DebugEcho("autolink_do: no provider");
                        }
                    } else {
                        DebugEcho("autolink_do: no oembed");
                    }

                    $link_url_enc = htmlspecialchars($link_url);
                    $display_url_enc = htmlspecialchars($display_url);

                    if ($skip) {
                        DebugEcho("autolink_do: oembed source, skipping $link_url");
                        $link_url_enc = apply_filters('postie_bare_link', $link_url_enc, $link_url, true);
                        DebugEcho("autolink_do: post postie_bare_link: $link_url_enc");
                        $buffer .= $link_url_enc;
                    } else {
                        DebugEcho("autolink_do: linkifying $link_url");
                        $link_html = apply_filters('postie_bare_link', "<a href=\"{$link_url}\"$currentTagfill>{$display_url_enc}</a>", $link_url, false);
                        DebugEcho("autolink_do: post postie_bare_link: $link_html");
                        $buffer .= $link_html;
                    }
                } else {
                    //DebugEcho("autolink_do: fail 3 at $cursor");

                    $ok = 0;
                    $cursor += $fail_len;
                    $buffer .= $fail_text;
                }
            }
        }

        #
        # add everything from the cursor to the end onto the buffer.
        #

        $buffer .= substr($text, $cursor);

        return $buffer;
    }

####################################################################

    function autolink_label($text, $limit) {

        if (!$limit) {
            return $text;
        }

        if (strlen($text) > $limit) {
            return substr($text, 0, $limit - 3) . '...';
        }

        return $text;
    }

####################################################################

    function autolink_email($text, $tagfill = '') {

        $atom = '[^()<>@,;:\\\\".\\[\\]\\x00-\\x20\\x7f]+'; # from RFC822
        #die($atom);

        $text_l = strtolower($text);
        $cursor = 0;
        $loop = 1;
        $buffer = '';

        while (($cursor < strlen($text)) && $loop) {

            #
            # find an '@' symbol
            #

            $ok = 1;
            $pos = strpos($text_l, '@', $cursor);

            if ($pos === false) {

                $loop = 0;
                $ok = 0;
            } else {

                $pre = substr($text, $cursor, $pos - $cursor);
                $hit = substr($text, $pos, 1);
                $post = substr($text, $pos + 1);

                $fail_text = $pre . $hit;
                $fail_len = strlen($fail_text);

                DebugEcho("autolink_email: \$pre: $pre");
                DebugEcho("autolink_email: \$hit: $hit");
                DebugEcho("autolink_email: \$post: $post");
                DebugEcho("autolink_email: \$fail_text: $fail_text");
                #
                # substring found - first check to see if we're inside a link tag already...
                #

                $bits = preg_split("!</a>!i", $pre);
                $last_bit = array_pop($bits);
                DebugEcho("autolink_email: check this for '<a' $last_bit");

                if (preg_match("!<a\s!i", $last_bit)) {

                    DebugEcho("autolink_email: found '<a' so not linkifying (already a link)");

                    $ok = 0;
                    $cursor += $fail_len;
                    $buffer .= $fail_text;
                }

                if ($ok) {
                    #
                    # check to see if we're inside a shortcode
                    #
                    $bits = preg_split("!\]!i", $pre);
                    $last_bit = array_pop($bits);
                    DebugEcho("autolink_email: check this for '[' $last_bit");
                    if (preg_match("!\[!i", $last_bit)) {

                        DebugEcho("autolink_email: found '[' so not linkifying (in a shortcode)");

                        $ok = 0;
                        $cursor += $fail_len;
                        $buffer .= $fail_text;
                    }
                }
            }

            #
            # check backwards
            #

            if ($ok) {
                $matches = array();
                if (preg_match("!($atom(\.$atom)*)\$!", $pre, $matches)) {

                    # move matched part of address into $hit

                    $len = strlen($matches[1]);
                    $plen = strlen($pre);

                    $hit = substr($pre, $plen - $len) . $hit;
                    $pre = substr($pre, 0, $plen - $len);
                } else {

                    DebugEcho("autolink_email: fail 2 at $cursor ($pre)");

                    $ok = 0;
                    $cursor += $fail_len;
                    $buffer .= $fail_text;
                }
            }

            #
            # check forwards
            #

            if ($ok) {
                if (preg_match("!^($atom(\.$atom)*)!", $post, $matches)) {

                    # move matched part of address into $hit

                    $len = strlen($matches[1]);

                    $hit .= substr($post, 0, $len);
                    $post = substr($post, $len);
                } else {
                    DebugEcho("autolink_email: fail 3 at $cursor ($post)");

                    $ok = 0;
                    $cursor += $fail_len;
                    $buffer .= $fail_text;
                }
            }

            #
            # commit
            #

            if ($ok) {

                $cursor += strlen($pre) + strlen($hit);
                $buffer .= $pre;
                $buffer .= "<a href=\"mailto:$hit\" $tagfill>$hit</a>";
            }
        }

        #
        # add everything from the cursor to the end onto the buffer.
        #

        $buffer .= substr($text, $cursor);

        return $buffer;
    }

}
