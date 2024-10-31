<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/autolink.php");

function filter_AttachmentTemplates($content, $mimeDecodedEmail, $post_id, $config) {
    global $g_postie;

    $matches = array();
    $isgallery = preg_match("/\[gallery[^\[]*\]/u", $content, $matches);

    DebugEcho("filter_AttachmentTemplates: custom_image_field: " . $config['custom_image_field']);
    DebugEcho("filter_AttachmentTemplates: auto_gallery: " . $config['auto_gallery']);
    DebugEcho("filter_AttachmentTemplates: [gallery]: " . ($isgallery ? 'yes' : 'no'));

    $addimages = !($config['custom_image_field'] || $config['auto_gallery'] || $isgallery);

    DebugEcho("filter_AttachmentTemplates: addimages: " . ($addimages ? 'yes' : 'no'));

    $featuredimageid = -1;

    DebugEcho("filter_AttachmentTemplates: looking for attachments to add to post");
    $html = '';
    if (!$config['include_featured_image']) {
        //find the image to exclude
        $featuredimageid = get_post_thumbnail_id($post_id);
    }
    DebugEcho("filter_AttachmentTemplates: featured image: $featuredimageid");

    DebugEcho("filter_AttachmentTemplates: # attachments: " . count($mimeDecodedEmail['attachment']));
    foreach ($mimeDecodedEmail['attachment'] as $attachment) {
        if (isset($attachment['wp_filename'])) {
            DebugEcho("filter_AttachmentTemplates: image: " . $attachment['wp_filename']);
            DebugEcho("filter_AttachmentTemplates: skip: $featuredimageid, " . $attachment['wp_id'] . ', ' . $config['include_featured_image'] . ', ' . $attachment['exclude']);
            $skip = ($featuredimageid == $attachment['wp_id'] && !$config['include_featured_image']) || $attachment['exclude'];
            if (!$skip) {
                if (!$addimages && $attachment['primary'] == 'image') {
                    DebugEcho("filter_AttachmentTemplates: skip image " . $attachment['wp_filename']);
                } else {
                    $template = $attachment['template'];
                    DebugEcho("filter_AttachmentTemplates: pre filter '$template'");
                    if ($config['images_append']) {
                        DebugEcho("filter_AttachmentTemplates: pre postie_place_media_after");
                        $template = apply_filters('postie_place_media_after', $template, $attachment['wp_id']);
                    } else {
                        DebugEcho("filter_AttachmentTemplates: pre postie_place_media_before");
                        $template = apply_filters('postie_place_media_before', $template, $attachment['wp_id']);
                    }
                    DebugEcho("filter_AttachmentTemplates: post filter '$template'");
                    $template = mb_str_replace('{CAPTION}', '', $template);
                    DebugEcho("filter_AttachmentTemplates: post caption '$template'");
                    $html .= $template;
                }
            } else {
                DebugEcho("filter_AttachmentTemplates: skip attachment " . $attachment['wp_filename']);
            }
        } else {
            DebugEcho("filter_AttachmentTemplates: skipped attachment " . $attachment['filename']);
        }
    }

    DebugEcho("filter_AttachmentTemplates: # inline: " . count($mimeDecodedEmail['inline']));
    DebugEcho("filter_AttachmentTemplates: # related: " . count($mimeDecodedEmail['related']));
    foreach (array_merge($mimeDecodedEmail['inline'], $mimeDecodedEmail['related']) as $attachment) {
        if (isset($attachment['wp_filename'])) {
            DebugEcho("filter_AttachmentTemplates: image: " . $attachment['wp_filename']);
            $skip = ($featuredimageid == $attachment['wp_id'] && !$config['include_featured_image']) || $attachment['exclude'];
            if (!$skip) {
                if (!$addimages && $attachment['primary'] == 'image') {
                    DebugEcho("filter_AttachmentTemplates: skip image (alt) " . $attachment['wp_filename']);
                } else {
                    $template = $attachment['template'];
                    if ($config['images_append']) {
                        DebugEcho("filter_AttachmentTemplates: pre postie_place_media_after");
                        $template = apply_filters('postie_place_media_after', $template, $attachment['wp_id']);
                    } else {
                        DebugEcho("filter_AttachmentTemplates: pre postie_place_media_before");
                        $template = apply_filters('postie_place_media_before', $template, $attachment['wp_id']);
                    }
                    $template = mb_str_replace('{CAPTION}', '', $template);
                    DebugEcho("filter_AttachmentTemplates: post filter (alt) '$template'");
                    $html .= $template;
                }
            } else {
                DebugEcho("filter_AttachmentTemplates: skip attachment (alt) " . $attachment['wp_filename']);
            }
        } else {
            DebugEcho("filter_AttachmentTemplates: skip attachment (removed by filter) " . $attachment['filename']);
        }
    }

    $html = trim($html);
    if (!empty($html)) {
        DebugEcho("filter_AttachmentTemplates: attachments generated: $html");
        if ($config['images_append']) {
            $content = $content . '<div class="postie-attachments">' . $html . '</div>';
        } else {
            $content = '<div class="postie-attachments">' . $html . '</div>' . $content;
        }
    } else {
        DebugEcho("filter_AttachmentTemplates: no attachments generated");
    }

    //strip featured image from html
    if ($featuredimageid > 0 && $config['prefer_text_type'] == 'html' && !$config['include_featured_image']) {
        DebugEcho("filter_AttachmentTemplates: remove featured image from post");
        $html = str_get_html($content);
        if ($html) {
            $elements = $html->find('img[src=' . wp_get_attachment_url($featuredimageid) . ']');
            foreach ($elements as $e) {
                DebugEcho('filter_AttachmentTemplates: outertext:' . $e->outertext);
                $e->outertext = '';
            }
            $content = $html->save();
        }
    }

    $imagecount = 0;
    foreach (array_merge($mimeDecodedEmail['attachment'], $mimeDecodedEmail['inline'], $mimeDecodedEmail['related']) as $attachment) {
        if (isset($attachment['primary']) && $attachment['primary'] == 'image' && $attachment['exclude'] == false) {
            $imagecount++;
        }
    }
    DebugEcho("filter_AttachmentTemplates: image count $imagecount");

    if (($imagecount > 0) && $config['auto_gallery']) {
        $linktype = strtolower($config['auto_gallery_link']);
        DebugEcho("filter_AttachmentTemplates: Auto gallery: link type $linktype");
        $g_postie->show_filters_for('postie_gallery');
        if ($linktype == 'default') {
            DebugEcho("filter_AttachmentTemplates: pre postie_gallery (default)");
            $imageTemplate = apply_filters('postie_gallery', '[gallery]', $post_id);
        } else {
            DebugEcho("filter_AttachmentTemplates: pre postie_gallery ($linktype)");
            $imageTemplate = apply_filters('postie_gallery', "[gallery link='$linktype']", $post_id);
        }
        DebugEcho("filter_AttachmentTemplates: Auto gallery: template '$imageTemplate'");
        if ($config['images_append']) {
            $content .= "\n$imageTemplate";
            DebugEcho("filter_AttachmentTemplates: Auto gallery: append");
        } else {
            $content = "$imageTemplate\n" . $content;
            DebugEcho("filter_AttachmentTemplates: Auto gallery: prepend");
        }
    } else {
        DebugEcho("filter_AttachmentTemplates: Auto gallery: none");
    }

    return $content;
}

// This function cleans up HTML in the email
function filter_CleanHtml($content) {
    $html = str_get_html($content);
    if ($html) {
        DebugEcho("filter_CleanHtml: checking filter postie_cleanhtml");
        if (apply_filters('postie_cleanhtml', true)) {
            DebugEcho("filter_CleanHtml: Looking for invalid tags");
            foreach ($html->find('script, style, head') as $node) {
                DebugEcho("filter_CleanHtml: Removing: " . $node->outertext);
                $node->outertext = '';
            }
            DebugEcho("filter_CleanHtml: " . $html->save());

            $html->load($html->save());

            $b = $html->find('body');
            if ($b) {
                DebugEcho("filter_CleanHtml: replacing body with div");
                $content = "<div>" . $b[0]->innertext . "</div>\n";
            }
        } else {
            DebugEcho("filter_CleanHtml: skipping clean html due to filter");
        }
    } else {
        DebugEcho("filter_CleanHtml: No HTML found");
    }
    return $content;
}

/**
 * Looks at the content for the start of the message and removes everything before that
 * If the pattern is not found everything is returned
 * @param string
 * @param string
 */
function filter_Start($content, $config) {
    $start = $config['message_start'];
    if (!empty($start)) {
        $pos = stripos($content, $start);
        if ($pos === false) {
            return $content;
        }
        DebugEcho("filter_Start: start filter $start");
        $content = substr($content, $pos + strlen($start), strlen($content));
    } else {
        DebugEcho("filter_Start: no start filter");
    }
    return $content;
}

/**
 * Looks at the content for the start of the signature and removes all text
 * after that point
 * @param string
 * @param array - a list of patterns to determine if it is a sig block
 */
function filter_RemoveSignature($content, $config) {
    global $g_postie;

    DebugEcho("filter_RemoveSignature: start");
    if ($config['drop_signature']) {
        if (empty($config['sig_pattern_list'])) {
            DebugEcho("filter_RemoveSignature: no sig_pattern_list");
            return $content;
        }
        DebugEcho("looking for signature in: $content");

        $html = $g_postie->load_html($content);
        if ($html !== false && $config['prefer_text_type'] == 'html') {
            DebugEcho("filter_RemoveSignature: html");
            $pattern = '/>\s*(' . implode('|', $config['sig_pattern_list']) . ')/miu';
            DebugEcho("filter_RemoveSignature: pattern: $pattern");
            filter_RemoveSignatureWorker($html->root, $pattern);
            //DebugEcho("filter_RemoveSignature: post worker: $html");
            $content = $html->save();
        } else {
            DebugEcho("filter_RemoveSignature: plain");
            $pattern = '/^(' . implode('|', $config['sig_pattern_list']) . ')\s?$/miu';
            DebugEcho("filter_RemoveSignature: pattern: $pattern");
            $arrcontent = explode("\n", $content);
            $strcontent = '';

            for ($i = 0; $i < count($arrcontent); $i++) {
                $line = trim($arrcontent[$i]);
                if (preg_match($pattern, trim($line))) {
                    DebugEcho("filter_RemoveSignature: signature found: removing");
                    break;
                }

                DebugEcho("filter_RemoveSignature: keeping '$line'");
                $strcontent .= $line . "\n";
            }
            $content = trim($strcontent);
        }
    } else {
        DebugEcho("filter_RemoveSignature: configured to skip");
    }
    return $content;
}

function filter_RemoveSignatureWorker(&$html, $pattern) {
    $found = false;
    $matches = array();
    $subject = trim($html);
    $pm = preg_match($pattern, $subject, $matches);
    if ($pm === 1) {
        $sig = trim($matches[1]);
        DebugEcho("filter_RemoveSignatureWorker: signature '$sig' found in:\n" . $subject);
        //DebugDump($matches);
        $found = true;
        $i = stripos($html->innertext, $sig);
        if (false !== $i) {
            DebugEcho("filter_RemoveSignatureWorker: signature index: $i");
            $presig = substr($html->innertext, 0, $i);
            DebugEcho("filter_RemoveSignatureWorker sig new text:\n$presig");
            $html->innertext = $presig;
        } else {
            //DebugEcho("filter_RemoveSignatureWorker: signature not found: '$sig' " . strlen($sig));
        }
    } else {
        if ($pm === false) {
            DebugEcho('filter_RemoveSignatureWorker: preg_match error ' . preg_last_error());
        }
        DebugEcho("filter_RemoveSignatureWorker: no matches " . preg_last_error() . " '$pattern' $subject");
        //DebugDump($matches);
    }

    foreach ($html->children() as $e) {
        //DebugEcho("sig: " . $e->plaintext);
        if (!$found && preg_match($pattern, trim($e->plaintext))) {
            DebugEcho("filter_RemoveSignatureWorker signature found: removing");
            $found = true;
        }
        if ($found) {
            $e->outertext = '';
        } else {
            $found = filter_RemoveSignatureWorker($e, $pattern);
        }
    }
    return $found;
}

/**
 * Looks at the content for the given tag and removes all text
 * after that point
 * @param string
 * @param filter
 */
function filter_End($content, $config) {
    $end = $config['message_end'];
    if (!empty($end)) {
        $pos = stripos($content, $end);
        if ($pos === false) {
            return $content;
        }
        DebugEcho("filter_End: end filter: $end");
        $content = substr($content, 0, $pos);
    } else {
        DebugEcho("filter_End: no end filter");
    }
    return $content;
}

//filter content for new lines
function filter_Newlines($content, $config) {
    if ($config['filternewlines']) {
        DebugEcho("filter_Newlines: filternewlines");
        $search = array(
            "/\r\n\r\n/",
            "/\r\n/",
            "/\n\n/",
            "/\r/",
            "/\n/"
        );
        $replace = array(
            'PARABREAK',
            'LINEBREAK',
            'PARABREAK',
            'LINEBREAK',
            'LINEBREAK'
        );

        $result = preg_replace($search, $replace, $content);
        DebugDump($result);

        if ($config['convertnewline']) {
            DebugEcho("filter_Newlines: converting newlines to <br>");
            $content = preg_replace('/(LINEBREAK)/', "<br />\n", $result);
            $content = preg_replace('/(PARABREAK)/', "<br />\n<br />\n", $content);
        } else {
            $content = preg_replace('/(LINEBREAK)/', " ", $result);
            $content = preg_replace('/(PARABREAK)/', "\r\n", $content);
        }
    }
    return $content;
}

//strip pgp stuff
function filter_StripPGP($content) {
    $search = array(
        '/-----BEGIN PGP SIGNED MESSAGE-----/',
        '/Hash: SHA1/'
    );
    $replace = array(
        ' ',
        ''
    );
    return preg_replace($search, $replace, $content);
}

function filter_Linkify($text) {
    global $g_postie;

    DebugEcho("filter_linkify: begin");
    $oe = _wp_oembed_get_object();

    $al = new PostieAutolink();

    if (postie_is_html($text)) {
        $html = $g_postie->load_html($text);
        if ($html !== false) {
            $es = $html->find('body');
            if (!empty($es)) {
                DebugEcho("filter_linkify: found body");
                $frag = $al->autolink($es[0]->innertext, $oe);
                $frag = $al->autolink_email($frag);
                $es[0]->innertext = $frag;
                return $html->__toString();
            } else {
                DebugEcho("filter_linkify: no body");
                $text = $al->autolink($text, $oe);
                return $al->autolink_email($text);
            }
        } else {
            DebugEcho("filter_linkify: html pase failed");
            $text = $al->autolink($text, $oe);
            return $al->autolink_email($text);
        }
    } else {
        DebugEcho("filter_linkify: plain text");
        $text = $al->autolink($text, $oe);
        return $al->autolink_email($text);
    }
}

/**
 * When sending in HTML email the html refers to the content-id(CID) of the image - this replaces
 * the cid place holder with the actual url of the image sent in
 * @param string - text of post
 * @param array - array of HTML for images for post
 */
function filter_ReplaceImageCIDs($content, &$email) {
    DebugEcho('filter_ReplaceImageCIDs: start');
    $content = preg_replace("/\[(cid:\S+)\]/i", '<img src="$1"/>', $content); //fix "[cid:xxx-xxx-xx]" image references gmail adds to plain text
    if (count($email['related'])) {
        DebugEcho("filter_ReplaceImageCIDs: # CID attachments: " . count($email['related']));
        foreach ($email['related'] as $cid => &$attachment) {
            if (false !== stripos($content, $cid)) {
                DebugEcho("filter_ReplaceImageCIDs: CID: $cid");
                if (isset($attachment['wp_id'])) {
                    $fileid = $attachment['wp_id'];
                    $url = wp_get_attachment_url($fileid);
                    $content = str_replace($cid, $url, $content);
                } else {
                    DebugEcho("filter_ReplaceImageCIDs: skipping {$attachment['filename']}");
                }
                $attachment['exclude'] = true;
            } else {
                DebugEcho("filter_ReplaceImageCIDs: CID not found: $cid");
            }
        }
    } else {
        DebugEcho("filter_ReplaceImageCIDs: no cid attachments");
    }
    return $content;
}

function filter_ReplaceInlineImage($content, &$email, $config) {
    DebugEcho('filter_ReplaceInlineImage: start');
    $i = 1;
    foreach ($email['inline'] as &$inlineImage) {
        if (($inlineImage['primary'] == 'image' && $config['auto_gallery']) ||
                $config['custom_image_field'] ||
                $config['featured_image'] && !$config['include_featured_image'] && $i == 1) {
            //remove inline placeholder if we're not showing the image here
            DebugEcho('filter_ReplaceInlineImage: do not add inline due to config');
            $template = '';
        } else {
            $template = $inlineImage['template'];
        }
        $inlinemarker = '<:inline ' . $inlineImage['filename'] . ' inline:>'; //use the original non-sanitized name
        if (false !== stripos($content, $inlinemarker)) {
            DebugEcho('filter_ReplaceInlineImage: ' . $inlineImage['filename']);
            $content = str_ireplace($inlinemarker, $template, $content);
            $inlineImage['exclude'] = !empty($template); //don't exclude if we didn't add
        } else {
            DebugEcho('filter_ReplaceInlineImage: not found: ' . $inlineImage['filename']);
        }
        $i++;
    }
    return $content;
}

/**
 * This function handles replacing image place holder #img1# with the HTML for that image
 */
function filter_ReplaceImagePlaceHolders($content, &$email, $config, $post_id, $image_pattern) {
    DebugEcho("filter_ReplaceImagePlaceHolders: start");
    if (!$config['custom_image_field']) {
        $startIndex = $config['start_image_count_at_zero'] ? 0 : 1;

        $images = get_posts(array(
            'post_parent' => $post_id,
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_mime_type' => 'image',));
        //DebugEcho("filter_ReplaceImagePlaceHolders: images in post: " . count($images));
        //DebugDump($images);

        $i = 0;

        //TODO only call the worker if the attachment matches one of the images
        foreach ($email['attachment'] as &$attachment) {
            $content = filter_ReplaceImagePlaceHolders_worker($content, $attachment, $image_pattern, $startIndex, $i);
            $i++;
        }
        foreach ($email['inline'] as &$attachment) {
            $content = filter_ReplaceImagePlaceHolders_worker($content, $attachment, $image_pattern, $startIndex, $i);
            $i++;
        }
        foreach ($email['related'] as &$attachment) {
            $content = filter_ReplaceImagePlaceHolders_worker($content, $attachment, $image_pattern, $startIndex, $i);
            $i++;
        }
    } else {
        DebugEcho("filter_ReplaceImagePlaceHolders: Custom image field, not adding images");
    }
    DebugEcho("filter_ReplaceImagePlaceHolders: end");
    return $content;
}

function filter_ReplaceImagePlaceHolders_worker($content, &$attachment, $imagePattern, $startIndex, $currentIndex) {
    if (isset($attachment['wp_filename'])) {
        DebugEcho("filter_ReplaceImagePlaceHolders_worker: " . $attachment['wp_filename']);

        if (empty($attachment['template'])) {
            DebugEcho("filter_ReplaceImagePlaceHolders_worker: no template");
            return $content;
        }

        $matches = array();
        $pparts = explode('%', $imagePattern);
        if (count($pparts) != 2) {
            DebugEcho("filter_ReplaceImagePlaceHolders_worker: invalid image pattern: $imagePattern");
            return $content;
        }
        $pattern = '/' . $pparts[0] . (string) ($startIndex + $currentIndex) . '\s?(caption=[\'"]?(.*?)[\'"]?)?' . $pparts[1] . '/iu';
        DebugEcho("filter_ReplaceImagePlaceHolders_worker: pattern: $pattern");
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        //DebugEcho($content);
        DebugDump($matches);
        foreach ($matches as $match) {
            DebugEcho('filter_ReplaceImagePlaceHolders_worker: processing:');
            DebugDump($match);

            $attachment['exclude'] = true;
            $imageTemplate = $attachment['template'];

            $caption = '';
            if (count($match) > 2) {
                $caption = trim($match[2]);
                DebugEcho("filter_ReplaceImagePlaceHolders_worker: caption: '$caption'");
            }
            DebugEcho("filter_ReplaceImagePlaceHolders_worker: parameterize templete: $imageTemplate");
            $imageTemplate = mb_str_replace('{CAPTION}', htmlspecialchars($caption, ENT_QUOTES), $imageTemplate);
            DebugEcho("filter_ReplaceImagePlaceHolders_worker: captioned template: $imageTemplate");

            if (!empty($caption)) {
                $img = get_post($attachment['wp_id']);
                $img->post_excerpt = $caption;
                wp_update_post($img);
                DebugEcho("filter_ReplaceImagePlaceHolders_worker: caption added to metadata");
            }
            $imageTemplate = apply_filters('postie_place_media', $imageTemplate, $attachment['wp_id']);
            DebugEcho("filter_ReplaceImagePlaceHolders_worker: post postie_place_media: '$imageTemplate'");

            $content = str_ireplace($match[0], $imageTemplate, $content);
            DebugEcho("filter_ReplaceImagePlaceHolders_worker: post replace: $content");
        }
    } else {
        DebugEcho("filter_ReplaceImagePlaceHolders_worker: skipping {$attachment['filename']}");
    }
    return $content;
}
