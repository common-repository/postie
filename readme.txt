=== Postie ===
Contributors: WayneAllen
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HPK99BJ88V4C2
Author URI: http://allens-home.com/
Plugin URI: http://PostiePlugin.com/
Tags: e-mail, email, post-by-email
Requires PHP: 7.0
Requires at least: 5.6
Tested up to: 6.4
Stable tag: 1.9.69
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Postie allows you to create posts via email, including many advanced features not found in WordPress's default Post by Email feature.

== Description ==
Postie offers many advanced features for creating posts by email, including the ability to assign categories by name, included pictures and videos, and automatically strip off signatures. 
Postie supports both IMAP and POP including SSL/TLS.
There is also an extensive set of filters/actions for developers to extend Postie's functionality.
For usage notes, see the [other notes](other_notes) page.

More info at http://PostiePlugin.com/

= Features =
* Supports IMAP or POP3 servers
* SSL and TLS supported
* Control who gets to post via email
* Set defaults for category, status, post format, post type and tags.
* Set title, category, status, post format, post type, date, comment control and tags in email to override defaults.
* Specify post excerpt (including excerpt only images).
* Use plain text or HTML version of email.
* Remove headers and footers from email (useful for posting from a mailing list).
* Optionally send emails on post success/failure.
* Control the types of attachments that are allowed by file name (wildcards allowed) and MIME type.
* Optionally make the first image the featured image.
* Gallery support.
* Control image placement with plain text email.
* Templates for images so they look the way you want.
* Templates for videos.
* Templates for audio files.
* Templates for other attachments.
* Email replies become comments.

= Developers =
* Several filter hooks available for custom processing of emails.
* More developer info at <a href="http://postieplugin.com/extending/">http://postieplugin.com/extending/</a>

== Screenshots ==

1. Postie server options
2. Postie user options
3. Postie message options
4. More message options
5. Even more message options
6. Image options
7. Video and Audio options
8. Attachment options

== Installation ==
* Install Postie either via the WordPress.org plugin directory, or by uploading the files to your server.
* Activate Postie through the Plugins menu in WordPress.
* Configure the plugin by going to the Postie menu that appears in your admin menu.
* Make sure you enter the mailserver information correctly, including the type of connection and the port number. 
* More information can be found at <a href="http://postieplugin.com/">http://postieplugin.com/</a>

== Usage ==

Please visit our site at <a href="http://postieplugin.com/">http://postieplugin.com/</a>

== Frequently Asked Questions ==

Please visit our FAQ page at <a href="http://postieplugin.com/faq/">http://postieplugin.com/faq/</a>
== Upgrade Notice ==

= 1.8.23 =
Postie now respects the blog timezone, this may require you to change the "Postie Time Correction" setting.

= 1.8.0 =
The php-imap library has been replaced with logic based on Flourish fMailbox et al, there are some differences in the structure of the mail header array. This affects the 
postie_filter_email3 and postie_post_before filters.
See http://flourishlib.com/docs/fMailbox

= 1.6.0 =
Remote cron jobs need to update the URL used to kick off a manual email check. The new URL is http://<mysite>/?postie=get-mail
Accessing http://<mysite>/wp-content/plugins/postie/get_mail.php will now receive a 403 error and a message stating what the new URL should be.
The Postie menu is now at the main level rather than a Settings submenu.

= 1.5.14 =
The postie_post filter has be deprecated in favor of postie_post_before.

= 1.5.3 =
Postie can now set the first image in an email to be the "Featured" image. There is a new setting "Use First Image as Featured Image" which is off by default.
Postie now supports Use Transport Layer Security (TLS)

= 1.5.0 =
New filter postie_filter_email. Used to map "from" to any other email. Allows custom user mapping.

= 1.4.41 =
Post format is now supported. You can specify any of the WordPress supported post formats using the Post type syntax.
Post status can now be specified using the status: tag.
Post status setting was renamed to Default Post Status and moved to the Message tab.

= 1.4.10 =
All script, style and body tags are stripped from html emails.

= 1.4.6 =
Attachments are now processed in the order they were attached.

== CHANGELOG ==
= 1.9.69 (2024-05-08) =
* Additional logging
* Make sure default user is set if no user is found

= 1.9.68 (2023-12-11) =
* Fix email subject and body of confirmation email if there are html entities in the blog name.

= 1.9.67 (2023-11-20) =
* Add option to suppress postie div

= 1.9.66 (2023-11-06) =
* Fix deprecation message for PHP 8.2
* Prevent post notification email if post was trashed

= 1.9.65 (2023-01-30) =
* remove calls to uname() as it typically isn't allowed on hosting platforms.

= 1.9.64 (2023-01-24) =
* fix for strange issue with PHP 8.1

= 1.9.63 (2022-12-29) =
* update simple_html_dom
* update requirements (WP5.6/PHP7.0)

= 1.9.62 (2022-09-21) =
* Timezone fix from https://wordpress.org/support/users/glenstewart/
* Add translation support to more strings

= 1.9.61 (2022-06-24) =
* Fix warning when MIME type is not recognized.

= 1.9.60 (2022-04-13) =
* Add translatable strings to email notification

= 1.9.59 (2022-01-02) =
* fix surrounding div with postie-post class name for plain text messages

= 1.9.58 (2021-12-27) =
* deal with possibility of no post formats (reported by @rogerlos)
* add surrounding div with postie-post class name for CSS rules for themes.

= 1.9.57 (2021-09-07) =
* if any attachment doesn't have a file extension use the secondary mime type

= 1.9.56 (2021-08-06) =
* remove Allow HTML In Mail Body from settings as it didn't do anything
* add Allow Duplicate Comments setting to deal with WordPress killing the import
* fix issue where unknown email was leaving tmppost, now creates draft message
* add setting to disable legacy commands

= 1.9.55 (2021-03-09) =
* Add post id to action postie_comment_after
* remove Create Alternate Image Sizes from settings as it didn't do anything
* Add compatibility for WordPress 5.7

= 1.9.54 (2020-10-18) =
* Add compatibility for WordPress 5.5

= 1.9.53 (2020-06-05) =
* Add postie_subject filter

= 1.9.52 (2020-05-19) =
* Fix issue with detecting categories when there are multiple colons in the subject line

= 1.9.51 (2020-05-08) =
* remove ob_end_flush from log_onscreen()

= 1.9.50 (2020-04-21) =
* Add more wp_insert_post failure logging

= 1.9.49 (2020-04-19) =
* Add more wp_insert_post failure logging

= 1.9.48 (2020-04-18) =
* Add more wp_insert_post failure logging

= 1.9.47 (2020-04-16) =
* Add wp_insert_post failure logging

= 1.9.46 (2020-04-11) =
* escape IMAP password
* fix logging in get_parent_postid
* ensure any modification by shortcode are retained

= 1.9.45 (2020-03-29) =
* Fix email notification bug

= 1.9.44 (2020-03-23) =
* refactoring to separate email fetch from email processing
* add postie_register_shortcode_pre action for registering Postie shortcodes

= 1.9.43 (2020-02-18) =
* Begin migration of shortcode support into Postie main

= 1.9.42 (2020-02-18) =
* Fix: signature stripping in html emails was failing sometimes

= 1.9.41 (2020-02-01) =
* Fix: different regex approach for html vs plain
* Only process 1 email at a time

= 1.9.40 (2019-11-13) =
* Fix issue with class-oembed.php upgrade

= 1.9.39 (2019-11-12) =
* Add Site Health checks
* PHP 7.3 compatibility
* Fix bug where if Remove matched categories is No then the default category is always set.
* Address deprecated file class-oembed.php

= 1.9.38 (2019-07-27) =
* Fix bug where filtering out an attachment prevented other attachments from being processed

= 1.9.37 (2019-06-20) =
* Update logging to output encoding info
* Fix bug where non-image titles are blank

= 1.9.36 (2019-06-11) =
* Fix bug where image titles are blank

= 1.9.35 (2019-05-31) =
* Allow for a single admin when sending post confirmation emails
 
= 1.9.34 (2019-05-15) =
* Workaround for WP bug #39521 to correctly set image caption from image metadata

= 1.9.33 (2019-04-29) =
* Assign author to all media items

= 1.9.32 (2019-03-02) =
* Move intermediate_image_sizes_advanced filter to only be effective when Postie is running

= 1.9.31 (2019-02-28) =
* Additional logging to chase down customer issue

= 1.9.30 (2019-02-25) =
* WP 5.1 compatibility testing
* Add setting to skip image resizing

= 1.9.29 (2019-02-18) =
* Fix delay, command was not being removed from content in all cases
* Additional debug output to track down attachment issue

= 1.9.28 (2019-01-10) =
* fix IMAP end of message detection
* remove insecure cURL settings
* fix simple_html_dom regex to be compatible with PHP 7.3

= 1.9.27 (2018-12-20) =
* Add new filter postie_parent_post to allow custom parent post detection
* Existing parent post detection now only looks at the configured post type and uses the most recent if multiple are found

= 1.9.26 (2018-11-23) =
* If no category is specified and the post type is "page" then the default category is not added
* Additional CID changes

= 1.9.25 (2018-10-02) =
* Add support for CIDs with surrounding <>
* Add support for URLs with unusual characters

= 1.9.24 (2018-07-12) =
* log functions that use Postie filters
* remove empty tags

= 1.9.23 (2018-05-28) =
* Fix issue when attachment doesn't conform to MIME standard

= 1.9.22 (2018-05-28) =
* Remove Text for Message Start and Text for Message End defaults as they conflicted with some CSS

= 1.9.21 (2018-05-08) =
* Fix: WP is removing backslashes so double them up before saving

= 1.9.21 (2018-04-11) =
* Fix: Compatibility with PHP 5.2-5.4

= 1.9.20 (2018-04-11) =
* Fix: Don't emit attachment div if there is no content after template generation

= 1.9.19 (2018-03-21) =
* Fix: Recognize paragraph line ending in text mode for both styles

= 1.9.18 (2018-03-13) =
* Fix: new signature removal logic removed line endings in some cases

= 1.9.17 (2018-03-12) =
* Fix: Plain text with < > being processed as html causing loss of content during signature removal

= 1.9.16 (2018-02-21) =
* Fix: don't linkify URLs in style attributes

= 1.9.15 (2018-01-25) =
* Fix: accommodate extra spaces in content type header when charset is specified 

= 1.9.14 (2017-12-01) =
* Feature: Additional logging of graphics library in use.

= 1.9.13 (2017-11-22) =
* Feature: New filter postie_include_attachment to allow/prevent attachment to be added to post. See http://postieplugin.com/filter-postie_include_attachment/

= 1.9.12 (2017-11-15) =
* Fix: use wp native function to detect MySQL version

= 1.9.11 (2017-11-10) =
* Fix: remove encoding option, everything is UTF-8.
* Feature: add MySQL version to debug output, suppress many email parsing debug messages
* Fix: don't set actual post format if "standard"
* Feature: Hides WordPress post-by-email feature if Postie is active

= 1.9.10 (2017-10-31) =
* Fix: WP CLI compatibility

= 1.9.9 (2017-10-30) =
* Fix: remove assumption that there is a user with the administrator role

= 1.9.8 (2017-10-23) =
* Fix: check that global is initialized before using

= 1.9.7 (2017-10-19) =
* Fix: more robustness if iconv fails

= 1.9.6 (2017-10-19) =
* Fix: Post format was not being correctly set.

= 1.9.5 (2017-10-13) =
* Feature: change post status to pending/draft if the user doesn't have publish_posts capability
* Fix: process postie_email_notify_recipients filter regardless of the "Send post confirmation email to" setting
* Fix: process postie_email_reject_recipients filter regardless of the "Send post confirmation email to" setting

= 1.9.4 (2017.08.10) =
* Fix: Incorrectly looking for links in html head style blocks

= 1.9.3 (2017.08.04) =
* Feature: Add "every 2 hours" option
* Fix: Include actual response when IMAP authentication fails
* Fix: Incorrectly processing text/html sections as attachments in some cases

= 1.9.2 (2017.07.24) =
* Fix: Uninitialized variable in Postie::create_post()
* Fix: More robust tag detection

= 1.9.1 (2017-06-28) =
* Feature: Add postie_file_added_pre action
* Fix: improvements to tag detection
* Fix: tags may be specified multiple times
* Fix: significant code restructure to bring Postie in line with current coding practices
* Fix: move signature removal to before newline filtering
* Fix: signature removal was deleting all text if the matching regex had whitespace characters

= 1.8.45 (2017-06-21) =
* Feature: If image placement directive has a caption, update the image metadata to include the caption.
* Fix: Only process delay tag if date was found in header
* Fix: Date tag overrides header and no offsets are applied
* Fix: Check for term slug as well as name

= 1.8.44 (2017-06-14) =
* Ensure only users from current site are validated.
* Process newlines earlier in process.
* WordPress 4.8 compatibility testing.

= 1.8.43 (2017-05-10) =
* Fix "postie-attachments" div to wrap the right content
* Fix time zone adjustment

= 1.8.42 (2017-05-10) =
* Wrap all attachments in div with css class "postie-attachments"
* Remove APOP authentication attempt as some servers disconnect on failure.

= 1.8.41 (2017-05-05) =
* Organizational changes in prep for move to more OO approach
* Fix error when time offset is negative.

= 1.8.40 (2017-04-24) =
* Ensure date set to current date when configured
* Compatibility testing with WordPress 4.7.4

= 1.8.39 (2017-04-18) =
* Ensure the time zone is set when calculating the post time.
* Additional debug output

= 1.8.38 (2017-04-06) =
* Update postie_notify_error to be less noisy.

= 1.8.37 (2017-04-05) =
* Remove DateTimeImmutable references to be compatible with old versions of PHP

= 1.8.36 (2017-04-05) =
* Allow user to specify a IMAP folder (supplied by Alejandro Liu)
* Fix postie_file_added action
* New setting "use time offset" which controls whether or not the postie time offset is applied.
* New setting "ignore_email_date" which controls whether or not the email "from" date is used.
* Other email to post date improvements
* New setting "Notify on Error" to control who gets notified on significant errors.
* Email admins on some errors
* Fix bug when parsing headers that have multiple encoded sections

= 1.8.35 (2017-03-21) =
* Check for blank image file extension and replace with mime subtype to get around new filetype checking in WordPress 4.7.2 https://core.trac.wordpress.org/ticket/39550
* Additional logging of message and part headers
* Fix base64 header decoding so trailing space isn't removed
* Fix header decoding to remove extra utf-8 byte order mark
* Add current WordPress time to logging

= 1.8.34 (2017-03-15) =
* Fix bug when sending reject email

= 1.8.33 (2017-03-13) =
* New setting "Remove matched categories" allows category specifiers to remain in post title.
* New filter postie_cleanhtml to determine if Postie should clean up the html or if some other AddOn will do it.
* Specifically call out public methods

= 1.8.32 (2017-03-07) =
* Ensure password not in logs for POP3.
* Additional POP3 header checks.
* Additional POP3 logging.
* Ensure postie_raw always gets called.
* Default to GMT when timezone is blank.
* Use WordPress function to upload images rather than old custom function.
* Remove Generate Thumbnails option as now handled by WordPress.
* ATT00001.txt and its ilk are now always considered a banned attachment regardless of MIME type.
* Removed call to date_default_timezone_set()
* Send notifications to all WP admins if configured.
* Add support for postie_category filter.

= 1.8.31 (2017-02-07) =
* Remove timezone warning if GMT+0
* Add postie_bare_link filter

= 1.8.30 (2017-02-06) =
* Fixed bug where Postie won't run on PHP versions prior to 5.5.

= 1.8.29 (2017-02-06) =
* Fixed bug where "Include Featured Image in Post" = No was not being respected with inline images (iOS mail).

= 1.8.28 (2017-01-26) =
* Fixed bug where having ignored files can cause email body to be blank.

= 1.8.27 (2017-01-26) =
* Added postie_raw action.

= 1.8.26 (2017-01-24) =
* Fix bug where WordPress timezone was not being taken into effect.

= 1.8.25 (2017-01-20) =
* Fix bug where server settings page not compatible with PHP 5.3

= 1.8.24 (2017-01-20) =
* Fix bug where debug output was displaying plaintext passwords.
* Fix bug where categories specified with a colon required a space after the colon.
* Postie now works off blog time rather than GMT.
* Fix bug where oEmbed::get_provider() is not available on WP versions prior to 4.0.

= 1.8.23 (2017-01-12) =
* Fix bug where long subject lines are missing a space.
* Fix bug where {CAPTION} placeholder not being removed if no caption.

= 1.8.22 (2016-12-07) =
* Fix bug where Image Place Holder Tag setting was not respected 

= 1.8.21 (2016-12-07) =
* Clarified the behavior of the "Use custom image field for images" setting and moved it to the Image tab.
* Clarified text on some settings.
* Fix bug which prevented captions from working correctly
* Fix bug which prevented more than 9 #img# references from working.
* Account for unicode non-breaking spaces in regular expressions.

= 1.8.20 (2016-11-21) =
* Fix issue where attachment with Content-ID doesn't have a reference in the html not being added to the post.

= 1.8.19 (2016-11-16) =
* Fix issue where attachments not showing if both plain and html are blank and the attachment is part of a multipart (related) block.

= 1.8.18 (2016-11-16) =
* Fix issue where inline PDF was not being detected as a PDF.
* Fix issue where outlook.com is setting an incorrect mime type on images.

= 1.8.17 (2016-11-03) =
* Fix bugs in cURL connection.

= 1.8.16 (2016-11-03) =
* Fix bug where postie_place_media_before and postie_place_media_after were being called incorrectly.

= 1.8.15 (2016-11-02) =
* Fix bug where inline images were not being replaced correctly if WordPress changed the name of the attachment via sanitize_file_name()

= 1.8.14 (2016-11-01) =
* Fix bug where OS detection failure was preventing email processing

= 1.8.13 (2016-10-31) =
* Fix bug where inline images were not being found due to case differences, e.g. img_4180.jpg vs. IMG_4180.JPG

= 1.8.12 (2016-10-25) =
* Fix bug in postie_email_notify_body filter where the modified body is not used for the email.

= 1.8.11 (2016-10-24) =
* Support Dovecot IMAP server better

= 1.8.10 (2016-10-24) =
* New filter postie_place_media - allows markup to be changed when #img# references are being replaced.
* New filter postie_place_media_before - allows markup to be changed when attachment templates are placed before the content.
* New filter postie_place_media_after - allows markup to be changed when attachment templates are placed after the content.

= 1.8.9 (2016-10-23) =
* Fix bug when calling new postie_email_* filters
* Fix bug where inline and related attachments were not being considered for #img#

= 1.8.8 (2016-10-21) =
* Fix bug where success/fail email notifications had missing info.
* Fix bug where galley wan't being inserted on some plain text messages
* Enhance auto linkification to recognize links like <http://example.com/>
* Separate success and failure emails.
* Fix readme
* New filter: postie_email_reject_recipients - allows recipients list to be altered when sending failure message
* New filter: postie_email_reject_subject - allows the subject to be altered when sending failure message
* New filter: postie_email_reject_body - allows message content to be altered when sending failure message
* New filter: postie_email_notify_recipients - allows recipients list to be altered when sending success message
* New filter: postie_email_notify_subject - allows the subject to be altered when sending success message
* New filter: postie_email_notify_body - allows message content to be altered when sending success message

= 1.8.7 (2016-10-20) =
* Make code compatible with PHP 5.2
* Fix bug where the connection tried to use TLS even though it shouldn't
* Switch to stream API vs sockets since sockets don't seem to be installed typically and the stream API is core.
* Allow self signed certificates with socket connections
* Don't check peer name with socket connections, many hosting companies aren't configured correctly

= 1.8.6 (2016-10-19) =
* Fix bug where the featured image was not included even though config said it should be.
* Ensure Socket connections try to connect with TLS 1.0, 1.1 and 1.2 if SSL is specified.
* Additional logging to help diagnose cURL issue.

= 1.8.5 (2016-10-18) =
* Fix to check if cURL is installed before using

= 1.8.4 (2016-10-17) =
* General release

= 1.8.3 (beta 4) =
* Refactor attachment handling
* Gallery shortcode handling is now correct when there are both images and non-images.

= 1.8.2 (beta 3) =
* New icon set thanks to Chris Lacey
* php-imap replaced by cURL and Socket connection - work sponsored by xsell.net
* TLS automatically detected, setting removed
* Ignore mail state no longer supported, setting removed
* Header array in filter_email3 and postie_post_before filters have changed format
* Fixed paragraph detection in plain text when removing newlines
* Removed old partially functioning forward detection logic
* Transform "[cid:xxx-xx-xx]" references gmail adds to image references.
* Add new filter: postie_post_pre that runs after the email is parsed, but before any changes are made.
* Fixed improper decoding of encoded headers when multiple 'encoded-words' are present.
* Fix to allow multiple #img# reference to the same image.
* The default video 1 template is now 'vshortcode'
* New option: Text fallback. Falls back to plain if html is blank and vice versa.
* Support removing featured image from html

= 1.8.1 (beta 2) =

= 1.8.0 (beta 1) =

= 1.7.32 (2016-04-15) =
* Deal with incorrectly formatted date headers

= 1.7.31 (2016-03-05) =
* Suppress warning message when accessing POP and there are no messages.

= 1.7.30 (2016-02-16) =
* prevent auto-linkifying inside shortcodes

= 1.7.29 (2016-02-15) =
* fixed email header parsing bug in PHP7

= 1.7.28 (2016-02-09) =
* better tag detection with html
* Don't skip image processing when Include Featured Image in Post is set to "No" and the Preferred Text Type is HTML.
* Use the blog name as the "from" text in any emails.
* New filter: postie_filter_email3
* Email headers now available in postie_post_before filter
* When looking for a parent post to add comments ensure the comments are open

= 1.7.27 (2015-12-28) =
* Fix category match settings not saving

= 1.7.26 (2015-12-28) =
* Detect oEmbedable links and don't linkify
* New filter postie_preconnect. http://postieplugin.com/filter-postie_preconnect/

= 1.7.25 (2015-12-15) =
* Fix settings page for new category matching flavors

= 1.7.24 (2015-12-15) =
* Don't process youtube and vimeo links specially.
* New setting to turn off category matching flavors

= 1.7.23 (2015-12-09) =
* Fix bug where emails inside shortcodes were being linkified
* Lookup categories by slug as well as by name
* WordPress 4.4 testing
* Added new video template for using video shortcode
* Added new template variable - FILETYPE which is the file extension

= 1.7.22 (2015-11-09) =
* Update admin screen to match current WP style
* Remove dependence on simple tabs jQuery-UI library and use WP admin tab style
* Remove email password from logging

= 1.7.21 (2015-10-27) =
* Refix bug where "Ignore mail state" setting was being ignored

= 1.7.20 (2015-10-26) =
* Fixed bug where debug info was not being displayed according to settings
* Fix bug where "Ignore mail state" setting was being ignored
* Fix bug where empty post was being generated when already read mail was in the inbox and "Ignore mail state" was "Yes"
* Added postie_session_start and postie_session_end actions

= 1.7.19 (2015-10-13) =
* Fixed bug where allowed mime types was not being respected.

= 1.7.18 (2015-10-13) =
* Fix bug where linkify was messing up CID reference

= 1.7.17 (2015-10-12) =
* New action, postie_log_error
* New action, postie_log_debug
* New feature to turn off all logging
* Only errors logged by default

= 1.7.16 (2015-10-08) =
* Ensure comments are valid html after striping if preferred text type is html
* Add setting to control comment content (strip_reply)

= 1.7.15 (2015-10-02) =
* Completely replace linkify logic
* Support youtu.be links

= 1.7.14 (2015-10-01) =
* Fix bug in new linkify logic

= 1.7.13 (2015-10-01) =
* Fix support for "Automatically convert urls to links" with html
* Fix support for "date" tag with html
* Prep for upcoming translation

= 1.7.12 (2015-09-25) =
* Add new setting to ignore email read/unread state
* Fix support for "tag" tags in html and plain messages

= 1.7.11 (2015-09-18) =
* Add FILEID to image and video templates

= 1.7.10 (2015-09-16) =
* added 15 and 30 second cron schedules

= 1.7.9 (2015-09-14) =
* revert tags logic as html version was messing up the content.
* revert video linkify logic as html version was messing up the content.
* revert linkify logic as html version was messing up the content.

= 1.7.8 (2015-09-08) =
* Remove mbstring admin message. Added fallback if mbstring is not installed
* More explanation for signature regex
* Add 1 minute schedule to supplement new external cron recommendation
* Updates to support upcoming Language Packs feature for plugins
* Moved important warning to admin notices
* New filter postie_category_default

= 1.7.7 (2015-08-24) =
* Fixed bug where "To" and "Reply-To" emails were not parsed correctly for postie_filter_email2 filter

= 1.7.6 (2015-08-13) =
* Added setting to control whether the featured image is included in the post or not.
* Added 2 new filters, postie_comment_before and postie_comment_after 

= 1.7.5 (2015-08-06) =
* If featured image is enabled, the featured image will no longer appear in the post.

= 1.7.4 (2015-07-30) =
* Added additional output if wp_insert_post() fails
* Fixed image upload failure when image filename doesn't have an extension

= 1.7.3 (2015-07-22) =
* Fix parameter order bug in postie_gallery filter

= 1.7.2 (2015-07-19) =
* Add filter postie_gallery when generating gallery shortcode

= 1.7.1 (2015-07-16) =
* Fixed issue where multiple custom taxonomy terms were not correctly being saved

= 1.7.0 (2015-07-07) =
* Fixed attachment uploading bug when the type & extension weren't available.
* Clarified "Filter newlines" setting description.
* Better support for Exchange 2010+ thanks to Andrew Chaplin
* New action hook - postie_file_added

= 1.6.19 (2015-05-04) =
* Reduced the number of messages sent to the log for successful runs
* Rename the disable_kses_content() function to postie_disable_kses_content() to fix a conflict with DAP WP LiveLinks Plugin

= 1.6.18 (2015-04-27) =
* Fixed a bug that prevented Postie from detecting categories with an ampersand in them.
* Added support for "future" post status 
* Move "Use shortcode for embedding video" setting to Video tab and clarified usage.
* Added setting to specify the link type used with galleries

= 1.6.17 (2015-03-28) =
* Add a setting to attempt a user login based on the from address of the email if a matching Wordpress user exists.

= 1.6.16 (2015-03-17) =
* If using the #img# feature and you supply a caption it will be added to the attachment's alt text.

= 1.6.15 (2015-03-04) =
* Remove "Wrap content in pre tags" option as it was defaulted to "yes" but never correctly applied.
* Allow time correction values of 0.5
* New setting: "Treat Replies As" allows user to specify if replies should be processed as comments or new posts.
* Fix bug in reply detection
* Fix bug when saving custom image field and there was only 1 image

= 1.6.14 (2015-02-26) =
* Fully support custom taxonomies

= 1.6.13 (2015-02-25) =
* Add some additional checks and error messages to postie_media_handle_upload for cases where the TMP directory isn't writable.
* Any user with "Roles that can post" can now be the default poster.

= 1.6.12 (2015-02-09) =
* Fix confirmation emails that were always sending to administrator.
* Fix regression in 1.6.11 which prevented attachments from being displayed in some cases.

= 1.6.11 (2015-01-30) =
* Call wp_set_current_user() so that other WP functions that depend on the current user work correctly. (custom taxonomy)
* Only do image template processing if the preferred text type is plain
* Removed http_response_code() call since it is only supported by PHP 5.4 or newer.

= 1.6.10 (2015-1-2) =
* Testing against 4.1
* New icon in admin

= 1.6.9 (2014.12.1) =
* Post status list is now read from WordPress settings, rather than being a hard-coded list.
* Settings look & feel matching WP default style.
* Testing against WP 4.0.1

= 1.6.8 (2014.11.14) =
* Fixed bug where the #img tag was being used as the subject if it is the very first line in the email.
* Fixed bug where the allow subject in body setting was being ignored.

= 1.6.7 (2014.11.05) =
* Fixed bug where base64 text with utf-8 charset was trying to convert encoding to utf-8

= 1.6.6 (2014.10.29) =
* Add additional debugging to isValidSmtpServer
* Add Japanese translation
* Fixed bug where a subject present in the body is not recognized

= 1.6.5 (2014.10.22) =
* Fixed charset encoding bug when there wasn't a content-transfer-encoding header
* Upgraded simple_html_dom 

= 1.6.4 (2014.10.21) =
* Provide post url in success email

= 1.6.3 (2014.10.03) =
* Added postie_filter_email2 filter which includes To and Reply-To headers
* Added postie_author filter
* Revised help/support page

= 1.6.2 (2014.09.22) =
* Moved FAQ and Help to PostiePlugin.com
* Fixed file type issue with wp_handle_upload_prefilter()
* Fixed cron issue

= 1.6.1 (2014.09.19) =
* Allow negative time corrections
* Fix links to settings page
* Misc UI updates

= 1.6.0 (2014.09.18) =
* Updated remote cron url to be more compatible with Wordpress
* The Postie menu has been moved out of the Settings menu to the top level
* Tested with Wordpress 4.0

= 1.5.24 (2014.08.12) =
* Fix attachment renaming bug
* Test with Wordpress 3.9.2

= 1.5.23 (2014.08.11) =
* Remove PEAR/PEAR5 dependency which was causing errors on some systems
* Call wp_handle_upload_prefilter before adding images to WP
* Temporary new post is now draft status

= 1.5.22 (2014.06.10) =
* Fix missing attachments for html messages

= 1.5.21 (2014.06.06) =
* Fixed spelling errors
* Added more debugging around image sizes
* Clarified some image options on settings page
* Removed call to wp_set_post_terms()

= 1.5.20 (2014.05.29) =
* Added logic to prevent appending images when preferred text type is HTML.
* Improved help text on several options and clarified options.
* Additional logging around attachment uploading

= 1.5.19 (2014.04.30) =
* Updated image preview to recognize all variables.
* Updated "wordpress_default" images template to match WP 3.8.
* Clarified logging messages for IMAP/IMAP-SSL/POP3-SSL
* Removed POSTTITLE from templates since we don't know the actual title at template time.
* Verified WordPress 3.9 compatibility

= 1.5.18 (2014.01.12) =
* Reverted text encoding change in 1.5.17.

= 1.5.17 (2013.12.19) =
* Fixed date calculation in test screen.
* Fixed text encoding issues for systems with PHP 5.4
* Updated CSS to better match WordPress 3.8 styles.
* Removed reference to pluggable.php to allow other plugins to plug these functions.
* Verified compatibility with WP 3.8

= 1.5.16 (2013.09.15) =
* Fixed date detection bug in forwarded messages.
* Possible fix for blank screen issue (flush buffers rather than check for sent headers).
* Fixed images showing up in excerpts when not specified.
* Fixed bug where time offset was applied twice.
* Fixed bug where excerpts where not getting the newline settings applied.
* Fixed bug where the attachment template was not getting set on new installs.
* Fixed bug where mp3 files were causing failures when getting meta-data.
* Verified compatibility with WP 3.6 and 3.6.1

= 1.5.15 (2013.07.01) =
* Added message warning that filter 'postie_post' has been deprecated.
* Fixed bug where a category was detected if there happened to be 2 dash (-) characters and a number was the first thing after the first dash.
* Simplified header decoding for non-ASCII languages

= 1.5.14 (2013.06.21) =
* Added PHP version to debug output
* Added PHP version check when disabling GSSAPI
* Added new filter "postie_post_before" to replace "postie_post"
* Added new filter "postie_post_after"

= 1.5.13 (2013.06.18) =
* Added more robust charset conversion to deal with malformed emails.
* Ensure the default title is used when category etc parsing results in blank title
* Consolidate procedure for locating wp-config.php
* Additional debug output.
* Fixed a bug where the default author was being used even though the email had a valid author.
* Added feature to disable IMAP authentication with GSSAPI or NTLM to improve MS Exchange compatibility.

= 1.5.12 (2013.06.08) =
* Added full paths to includes in config_form due to some hosts having include_path set in a way that breaks Postie.
* Added some checks for emails that aren't correctly formatted (AirMail/WinLink)
* Consolidated environmental checks
* Added logic for Debian location of wp-config.php

= 1.5.11 (2013.06.02) =
* Moved test files out of main repository to decrease plugin size
* Fixed issue with readme file (Wordpress readme validator moved)

= 1.5.10 (2013.05.31) =
* Added template field descriptions to image tab.
* Fixed a bug where caption placeholder in templates wasn't being properly set.
* Additional test to see if wp-config.php can be found.

= 1.5.9 (2013.05.18) =
* Fixed a bug where valid users can post via email even though they don't have role permissions.
* Fixed a bug where nice_name was being used in the settings screen even though user_login was needed.
* Fixed the error message when the default poster is not valid.
* Fixed bug where gallery short tag was getting set when non-image attachments were found.

= 1.5.8 (2013.05.14) =
* Added additional default signature patterns.
* Fixed a bug where attachments were not showing up if :start or :end were used.

= 1.5.7 (2013.05.09) =
* Fixed bug where the admin user was not getting set as author in some cases.
* Fixed bug where file names were not being sanitized.
* Added setting to disable thumbnail generation.
* Updated the default signature patterns and help text.

= 1.5.6 (2013-05-07) =
* Fixed bug where default post format was empty when the theme didn't support any formats.
* Removed all hard coded references to wp-content.
* Fixed bug where caption wasn't being filled out in templates.
* Updated direct DB access to use standard methods.
* Added attachment template for non-audio/image/video attachments.
* Added additional checks to the test screen.
* Added new template variable {ICON}

= 1.5.5 (2013.05.02) =
* Added ability to run a manual check with debugging output.
* Added support for default post type.
* Added version number to settings page.
* Changed Admin User to Default Poster, clarified help text and changed input to combo box of valid values.
* Fixed bug where commas (,) were not allowed in signature detection.
* Fixed some CSS issues on admin page.

= 1.5.4 (2013.05.01) =
* Added support for default post format
* Fixed bug where replies were getting attached to the wrong post
* Fixed bug where tags were not being detected correctly in html emails
* Fixed bug in reply detection

= 1.5.3 (2013.04.13) =
* Added support for Featured Images
* Added support for Use Transport Layer Security (TLS)
* Updated postie_filter_email filter to get unprocessed email address

= 1.5.2 (2013.04.12) =
* Fixed bug in post type/format detection when no valid post type/format was found
* Workaround for WP bug when POP3 account has no waiting messages
* Fixed bug where cron was running postie_check on every page load

= 1.5.1 (2013.04.10) =
* Turned on POP3 debug logging if POSTIE_DEBUG is TRUE.
* Disable autocomplete on some setup fields
* Fixed bug where confirmation emails were not being sent to authors
* Fixed bug where post were not saved if the default admin user didn't exist and the from user was not a WordPress user

= 1.5.0 (2013.04.05) =
* Apply Postie Time Correction to date: command
* Add support for Post Formats
* Add support for Post Status
* Add warning if Admin username is invalid
* Fixed bug where date: was not always being detected in html emails
* Improved handling of attachments and mapping to file extensions for template selection
* Video templates now include scale="tofit"
* Add support for older png mime type
* New filter postie_filter_email. Used to map "from" to any other email. Allows custom user mapping.


= 1.4.40 (2013.03.18) =
* Fixed bug where categories specified by ID were not being correctly identified

= 1.4.39 (2013.03.14) =
* Fixed bug where Postie supplied schedules were not always being correctly added to the cron

= 1.4.38 (2013.03.12) =
* Improved POP3 configuration test
* Fixed bug where :start and :end were removing commands like tags: and date: before they got a chance to be processed

= 1.4.37 (2013.03.11) =
* Fixed bug in tag handling
* Fixed bug in category detection
* Worked around a bug in WordPress that was mangling filenames that are not in ASCII, i.e. Arabic, Hebrew, Cyrillic, etc.

= 1.4.36 (2013.03.07) =
* Removed some debugging code from filters and hooks
* Fixed bug where the date command was being ignored
* Added 'quicktime' as a default type for the video1 template

= 1.4.35 (2013.02.22) =
* Consolidated logic for load configuration settings. Fixes bug where new settings were not having their defaults set properly.
* Fixed bug where attachment file name was not being correctly detected.

= 1.4.34 (2013.02.07) =
* Fixed bug in new category logic

= 1.4.33 (2013.02.05) =
* Fixed bug where non-category taxonomy was being selected as the post category.
* Added option to force categories to match exactly.
* Added logic to skip text attachments named "ATT00001.txt" where the numbers can be any sequence. This
  improves the previous fix by detecting all attachments added, not just the first one.

= 1.4.32 (2013.01.29) =
* Fixed bug in detecting need for imap extension.
* Added additional selections for "Maximum number of emails to process"
* Added logic to skip text attachments named "ATT00001.txt" which are added by MS Exchange virus scanning.
* Added option to check for email every 5 minutes.

= 1.4.31 (2013.01.25) =
* Enhanced category detection to be compatible with Polylang plugin.
* Enhanced prerequisite detection.
* Now using wp_mail() instead of mail()
* WordPress 3.5.1 compatibility

= 1.4.30 (2013.01.22) =
* Fixed bug that caused activation to fail or show a blank page.
* Fixed bug where WP media upload rejects a file.
* Added a check for mbstring.
* Fixed bug when attachment names were only supplied via d_parameters.

= 1.4.29 (2013.01.19) =
* Fixed bug where manually running Postie worked, but calling get_mail.php directly did not.

= 1.4.28 (2013.01.18) =
* Fixed bug in "reset settings to default" where the protocol wasn't being retained.
* More cleanup and clarification on settings screen.
* Fixed bug where excerpts weren't getting set if "Filter newlines" was set to "Yes"
* Removed logic to increase memory size.

= 1.4.27 (2013.01.17) =
* Updated sample plugin for extending Postie.
* Updated documentation for template variables.
* Fixed a bug where text/plain attachments were not being treated as attachments.
* Look for and include filterPostie.php in wp-content if it exists. (used for custom filters so they don't get deleted on upgrades)
* Cleanup of settings screen layout.
* Added additional error logging for mail connections.

= 1.4.26 (2013.01.15) =
* Fixed a bug where signatures were not removed in html emails.
* Added support for text attachments such as text/calendar.

= 1.4.25 (2013.01.15) =
* Fixed a bug where newlines were being removed erroneously.

= 1.4.24 (2013.01.13) =
* Fixed a bug where the original attachment name wasn't being used.
* Fixed a bug where the #eimg# tags in the excerpt were not getting expanded.

= 1.4.23 (2013.01.10) =
* Fixed a bug with embedded CID referenced images.

= 1.4.22 (2013.01.10) =
* Fixed a bug where the subject was not being properly decoded when Q-encoding was used.
* Fixed a bug in #img# caption detection.
* Fixed a bug where the tag command was picking up too much text.
* Enhanced the date command to allow times as well.

= 1.4.21 (2013.01.09) =
* Removed all Call-time pass-by-references to support PHP 5.4

= 1.4.20 (2013.01.08) =
* Added Date feature. You can now specify a specific publication date.
* Fixed a bug with embeded youtube/vimeo links when shortcodes are turned off

= 1.4.19 (2013.01.07) =
* Fixed a bug that prevented the settings from being saved

= 1.4.18 (2013.01.06) =
* Fixed a bug where linkifying was doing too much.
* Updated lots of method names in preparation for some significant structural changes.

= 1.4.17 (2013.01.03) =
* Fixed a bug where non image/video attachments were not getting added to the post.

= 1.4.16 (2013.01.03) =
* Fixed a bug where an extra div tag was getting added.
* Fixed a bug when linkifying URLs.
* Fixed a bug where inline images were not being detected.

= 1.4.15 (2013.01.02) =
* Fixed a bug when a category is specified with [] and a colon (:) is in the subject, but not specifying a category

= 1.4.14 (2012.12.29) =
* Fixed a bug where attached images were not being detected properly causing a "File is empty. Please upload something more substantial." error
* Tweaked some CSS.

= 1.4.13 (2012.12.26) =
* Fixed bug that was truncating content at the first html encoded character. 

= 1.4.12 (2012.12.17) =
* Added feature to limit the number of emails processed
* Fixed bug where #img# was not processing the caption correctly

= 1.4.11.(2012.12.14) =
* Fixed bug where having a colon in the subject caused the subject to get truncated
* Added donation link to admin screen
* Fixed bug where #img# captions with double-byte characters were not working
* Fixed bug where default settings were corrupt
* Fixed several bugs in tag detection logic
* Fixed bug where non-base64 and non-quoted-printable sections were not converted to utf-8
* Fixed bug where the end filter wasn't removed from the post
* Add additional logging to attachment upload process
* Changes to support WP 3.5

= 1.4.10 (2012.12.11) =
* Fixed warning when there is no subject
* Removed all script and style tags from HTML content in place of XSS warning
* Removed XSS warning
* Fixed bug where post type was not being detected if only case is different
* Fixed bug with custom post type and leading spaces in the subject
* Fixed bug where custom fields were not being populated for images

= 1.4.9 (2012.12.10) =
* Fixed bug where date, author, etc didn't get set.
* Fixed bug where Postie was treating attached images as strings
* Fixed bug where inline images were not being attached correctly
* Fixed bug where the subject was not being decoded correctly if the charset was different from the system charset
* Fixed bug where base64 strings were being double decoded.

= 1.4.8 (2012.12.09) =
* Fixed collisions with simple_html_dom
* Fixed bug when trying to get file name from MIME part
* Fixed bug causing Cannot modify header information warning 

= 1.4.7 (2012.12.07) =
* Fixed bug in cron setup that was preventing Weekly, twice an hour and every ten minute schedules from running.

= 1.4.6 (2012.12.06) =
* Changed XSS check to only emit warning until a better solution can be developed
* Fixed bug where authorized addresses were being checked with case sensitive rather than insensitive.
* Started using PHP Simple HTML DOM Parser http://simplehtmldom.sourceforge.net/
* Started process of logging all error messages to the log as well as the page (where appropriate)
* Fixed bug where "Preferred Text Type" was not displaying the saved value in settings page.
* Fixed bug where Postie was wiping out all other cron schedules.
* Fixed a number of missing internationalization strings (no translations added however)
* Added documentation for the comments: command
* Added feature to specify a custom post type in the subject line. Thanks to Raam Dev http://raamdev.com for his code.
* Removed a number of deprecated WordPress functions.
* Fixed numerous warning messages
* Added phpUnit tests
* Allow wp-config.php to be in alternate location as described here: http://codex.wordpress.org/Hardening_WordPress#Securing_wp-config.php
* Fixed a bug that didn't replace the #img# tags correctly.

= 1.4.5 (2012.11.14) =
* Fixed bug in XSS attack vulnerability code. Thanks to R Reid http://blog.strictly-software.com/2012/03/fixing-postie-plugin-for-wordpress-to.html
* Fixed bug where emails with multiple categories has the incorrect title
* Fixed bugs where PHP setting were not being changed correctly - thanks to Peter Chester http://tri.be/author/peter/
* New maintainer

= 1.4.4 (2012.08.10) =
* Fixed possible XSS attack vulnerability 

= 1.4.3 =
* Removed get_user_by function to make compatible with wp 3.3 - now requires
 2.8+

= 1.4.2 (2011.01.29) =
* Fixed mailto link bug (thanks to Jason McNeil) 
* Fixed bug with attachments with non-ascii characters in filename (thanks to
  mtakada)
* checking for socket errors when checking mail (thanks elysian)
* fixed issue with multiple files not being inserted correctly
* Added support for ISO 8859-15 (thanks paolog)
* fixed sql injection problem (thanks Jose P. Espinal for pointing it out)
* Fixed namespace clashing for get_config function

= 1.4.1 (2010.06.18) =
* Images appear in correct order when using images append = false
* Fixed formatting problem with wordpress_default image template
* Captions now correctly work with wordpress >3.0 and <3.0
* Fixed auto_gallery feature
* Default port is now 110
* Added more configuration tests
* Added background color to settings page to make input boxes more visible
* Removed extra quote character in captions from #img# placeholders (thanks
  SteelD for pointing out the error)
* Added support for big5 and gb-1232 encodings (thanks Chow)
* Fixed issue with configurations items stored as arrays, which caused
  problems with validating authorized addresses
* Fixed bug with replaceImageCIDs function
* On hosts which allow it, we set max execution time to 300 seconds and
  memory_limit to infinity to allow processing of large e-mails (especially
  with large attachments)
* Images are sorted in order of filename before inserting into post

= 1.4 (2010.04.25) =  
* Now using wordpress settings api (thanks for much help from Andrew S)
* Cronless postie is now integrated with postie instead of a separate plugin
* filterPostie.php moved to filterPostie.php.sample
* Can use fetchmails.php to fetch mail from multiple mailboxes
* Fixed problem with embedding youtube videos from html (richtext) e-mail
* Added support for embedding vimeo vidoes
* Fixed problem with selecting "none" as icon set for attachments (thanks
  tonyvitali)
* Fixed problems with cronless postie settings
* Fixed bug with embedding youtube and vimeo videos whose ID contains a -
  (thanks Jim Kehoe)
* Post_author is now included with attachments
* fixed confirmation_email settings so that now you can select between sender,
  admin, both, or none (thanks to redsalmon for pointing out bug)
* Added option to automatically insert galleries
* Updated FAQ and readme

= 1.3.4 (2009.10.05) =
* Fixed problem with images not posting under cron
* Fixed issue with disappearing password

= 1.3.3 (2009.09.11) =
* Fixed problem with double titles
* Fixed error in wp-mu
* Cronless postie now correctly updates when changing the setting in the
  postie settings
* Small fix in handling of names of attachments (thanks to Teejot)
* Fixed delay option (thanks to redbrandonk)
* Cronless option value is now correctly deleted when deactivating the
  cronless postie plugin

= 1.3.2 (2009.08.27) =
* tags are now always an array, even if no default tags are set 
* Subject is showing up again if you do not have the IMAP extension
  installed
* More information on the IMAP extension and more user-friendly
  installation
* Fixed problems with smtp server settings in 1.3.1
* Added russian translation (thanks to fatcow.com)

= 1.3.1 (2009.08.24) =
  * Changed GetContent filter to postie_post
  * Added database upgrade hook on activation
  * Fixed bug where content would be empty if trying to remove signature,
    and signature list was emtpy
  * Updated FAQ and readme

= 1.3.0 (2009.08.14) =
  * Features
      * Added mpeg4 to default list of videotypes
      * Added support for KOI8-R character set (cyrillic)
      * Added support for iso-8859-2 character set (eastern european)
      * Added option to include custom icons for attachments
      * Added option to send confirmation message to sender
      * Enhanced e-mails for unauthorized users
      * Added option to send unauthorized e-mail back to sender
      * Added option to only allow e-mails from a specified list of smtp
        servers
      * Added option to use shortcode for embedding videos (works with the
        videos plugin http://www.daburna.de/download/videos-plugin.zip
      * Better handling of comment authors (thanks to Petter for suggestion)
      * Simplified message options (now includes an advanced options section)
      * Added filter ability for post content
  * Bug fixes
      * No longer including wp-config.php
      * If tmpdir is not writable, try a different tmpdir
      * More subject encoding fixes
      * Updated image templates, which were causing problems for cron
      * Fixed in text captions
      * Fixed SQL problems when updating options
      * Fixed name clashes with other plugins
      * Fixed custom image field

=  1.3.beta (2009.07.01) =
  * Mores fixes for character issues in subject
  * Now handling Windows-1256 (arabic) character set
  * Fixed image uploading on windows servers
  * Fixed replying to message adds comment
  * Uploading pictures via MMS should now work
  * Fixed some issues with e-mails from outloook 12
  * Greatly reduced number of database queries
  * No longer requiring config_handler.php

=  1.3.alpha (2009.06.05) =
  * Now using default wordpress image and upload handling, which means:
      * No more creating special directories for postie
      * No more confusion about imagemagick
      * Can now use the [gallery] feature of wordpress
      * Attachments are now connected to posts in the database
      * All image resizing uses wordpress's default settings (under media)
  * Configuration, settings and documentation improvements
      * Completely redesigned settings page (mostly thanks to Rainman)
      * Reset configuration no longer deletes mailserver settings
      * Now including help files and faq directly in settings page
  * More media features
      * Automatically turn links to youtube into an embedded player
      * Added option to embed audio files with custom templates
      * Video options are now template based
      * Image options are now solely template based, with several new default
        templates
  * Bug fixes
      * Uploading images from vodafone phones should now work
      * Correctly handling Windows-1252 encoding
      * Correctly handling non-ascii characters in subject line

=  1.2.3 (2009.05.17) =
  * Fixed headers already sent bug
  * Converted shortcode `<?` to proper `<?php` (thanks brack)
  * Deleting mails after processing again

=  1.2.2 (2009.05.15) =
  * Show empty categories for default category in options
  * Image scaling fixed so that the smaller value of max image width and max
    image height is used
  * Fixed some issues with parsing html e-mail
  * Got rid of stupid mime tag (thanks Jeroen)
  * No longer adding slashes before calling wp_insert_post
  * When using custom image field, each image has a unique key


=  1.2.1 (2009.05.07) =
  * Got rid of stupid version checking
  * Improved cronless postie instructions and configuration
  * Internationalization is working now
  * Dutch localization (thanks to gvmelle http://gvmelle.com )
  * Fixed caption bug when using image magick
  * Added option to not filter new lines (when using markdown syntax)
  * Fixed autoplay option
  * Can now use wildcards in excluding filenames
  * Producing better quality thumbnails (thanks to robcarey)

=  1.2 (2009.04.22) =
  * More video options:
      * Can embed 3gp, mp4, mov videos
      * Can specify video width, video height, player width, and player height
        in the settings page
      * Can specify custom image template
  * Image handling improvements:
      * Only downscale images, not up-scale (thanks Jarven)
      * More custom image template options
      * IPTC captions now also work when not resizing images
      * Added option to use custom field for images (for Datapusher)
      * Fixed some issues with image templates and line break handling
      * Custom image template now works even when not resizing images
  * Documentation improvements:
      * Added links to settings, forum, and readme in plugin description
      * Updated readme (thanks to Venkatraman Dhamodaran)
      * Added better instructions on how to use cronless postie
  * Text processing improvements:
      * Added option to automatically convert urls into links
      * Added feature to include a custom excerpt
  * Miscellaneous improvements
      * Improved internationalization (thanks to Håvard Broberg
        (nanablag@nanablag.com))
  * Bug Fixes
      * Removed debugging info in get_mail.php (security issue) thanks to 
        [Jens]( http://svalgaard.net/jens/)
      * No longer directly including pluggable.php (should
        prevent conflicts with other plugins such as registerplus

=  1.1.5 (2009.03.10) =
  * Added option to have postie posts be pending review, published, or draft
  * Settings panel only shows up for administrators
  * Need not be user "admin" to modify settings or to post from non-registered
    users
  * Can now set administrator name. Authorized e-mail addresses which don't
    have a user get posted under this name
  * Will use IPTC captions if available
  * Added option to replace newline characters with <br />

=  1.1.4 (2009.03.06) =
  * Added more image options (open in new window, custom image template)
  * can now add captions to images
  * Can now add tags (including default tag option)

=  1.1.3 (2009.02.20) =
  * Fixed delayed posting
  * updated readme some

=  1.1.2 (2008.07.12) =
  * now maintained by Robert Felty
  * allow negative delays
  * will glean author information from forwarded or redirected e*mails
  * replying to an e*mail adds a comment to a post
  * fixed category handling to work with taxonomy
  * fixed one syntax error
  * added option to wrap posts and comments in <pre> tags

=  1.1.1 =

Below is all the of the version information. As far as I can tell there once was a guy named John Blade. He took some of the original wp-mail.php code 
and started hacking away on it. He actually got pretty far. About the time I discovered WordPress and his little hack - called WP-Mail at the time - he 
went on a vacation or something. There were some problems with the script, and it was missing some features I wanted. I hacked away at it and got it 
into a place where it did what I wanted. I started posting about it since I figured other people might want the features. 

John didn't release any more versions at least up til July 2005. So I started accepting submissions and feature requests from people to help make the 
code better. In June/July 2005 I discovered a little plugin by Chris J Davis (http://www.chrisjdavis.org/cjd-notepad/) called notepad. I added a small 
feature to it (basically a bookmarklet).  In the process I started looking at his code and realized how much you could do with the plugin system 
available in Word Press.

So I decided to make an official fork. I put up an article on my blog asking for new names. I picked Postie.  I then modified the code to be a proper 
plugin.  And the rest is history :)

* BUGFIX -problem with subject
* BUGFIX -cronless postie typo

=  1.1 =
* FEATURE: Updated and tested with WordPress 2.1
* BUGFIX:Removed deprecated functions
* FEATURE: Cronless Postie now uses the WordPress native Psuedo Cron.

=  1.0 =
* BUGFIX: TestWPVersion broke with 2.1
* FEATURE: end: now marks the end of a message (Dan Cunningham)
* FEATURE: Better Readme (Michael Rasmussen)
* FEATURE: Smart Sharpen Option -EXPERIMENTAL- (Jonas Rhodin)
* BUGFIX: Issue with google imap fixed (Jim Hodgson)
* BUGFIX: Fixed espacing issue in subjects (Paul Clip)
* BUGFIX: Typo in Div fixed (phil)

=  0.9.9.3.2 =
* BUGFIX: Typo

=  0.9.9.3.1 =
* BUGFIX: Removed debugging code

=  0.9.9.3 =
* BUGFIX: If your email address matches an existing user - then it will post as that user - even if you allow anyone to post.
* BUGFIX: Replaced get_settings('home') with get_settings('siteurl')
* BUGFIX: Better handling for Japanese character sets - Thanks to http://www.souzouzone.jp/blog/archives/009531.html
* BUGFIX: Better thumbnail window opening code - thanks to Gabi & Duntello!
* FEATURE: Added an option to set the MAX Height of an image - idea from Duntello
* BUGFIX: Modified the FilterNewLines for better flowed text handling - You now HAVE TO PUT TWO NEW LINES to end a paragraph.
* FEATURE: Added new CSS tags to support positioning images/attachments/3gp videos
* BUGFIX: Tries to use the date in the message (Thanks Ravan) I tried this once before and it never worked - hopefully this time it will.
* BUGFIX: Added a workaround to fix the problem with Subscribe2 - it will now notify on posts that are not set to show up in the future.

=  0.9.9.2 =
* BUGFIX: Looks for the NOOP error and disgards it
* FEATURE: Postie now detects the version of WordPress being used 
* FEATURE: Smarter Parsing of VodaPhone 
* FEATURE: Easy place to add new code to handle other brain-dead mail clients
* BUGFIX: Handles insertion of single quotes properly
* BUGFIX: Thumbnails should now link properly

=  0.9.9.1 =
* BUGFIX: Needed a strtolower in places to catch all iso-8859 - thx to Gitte Wange for the catch
* BUGFIX: Fixed issue with the category not being posted properly

=  0.9.9 =
* UPDATE TO WP 2.0
* BUGFIX: Config Page now works
* FEATURES: Supports role based posting
* BUGFIX: Posting updates the category counts.

=  0.9.8.6 =
* BUGFIX: Fixed problems with config page <%php became <?php
* 
=  0.9.8.5 =
* BUGFIX: onClick changed to onclick
* BUGFIX: strolower added to test for iso - thanks daniele
* BUGFIX: Added a class to the 3gp video tags
* FEATURE: Added the option to put the images before the article
* BUGFIX: Added in selection for charsets - thanks Psykotik - this may cause problems for other encodings
* FEATURE: Added option to turn of quoted printable decoding
* FEATURE: :start tag - now postie looks for this tag before looking for you message - handy if your service provider prepends a message 
* FEATURE: Template for translation now included

=  0.9.8.4 =
* BUGFIX: Fixed problem with config_form.php - select had "NULL" instead of ""
* BUGFIX: 3g2 now supported
* BUGFIX: More line break issues addressed
* BUGFIX: QuickTime controls are now visible even if the movie is done playing
* BUGFIX: Email addresses in the format <some@domain.com> (Full Name) supported
* BUGFIX: Some images that were not being resized - are now
* BUGFIX: HTML problems - if you posted plain text with HTML on it ignored all images
* BUGFIX: The test system blew up on the thumbnails 
* BUGFIX: Selected HTML for preferred text is now shown in the config form properly
* BUGFIX: Postie now complains if it is not in its own directory
* BUGFIX: Postie doesn't include PEAR if it is already available
* BUGFIX: In Test mode rejected emails are simply dropped
* BUGFIX: Markdown messes up Postie - it will warn you if you turn it on.
* 
=  0.9.8.3 =
* BUGFIX: Fixed issue with the line feed replacement
* BUGFIX: Added Banned File Config back in
* FEATURE: Added in a link around 3gp video embedded via QT
* BUGFIX: Email that has both Plain and HTML content will show the HTML content and not the plain if html is preferred

=  0.9.8.2 =
* BUGFIX: Fixed an extra new line after attaching non-image files.
* BUGFIX: The Test system now displays any missing gd functions
* BUGFIX: The test system was only using ImageMagick

=  0.9.8.1 =
* BUGFIX: The test images are now included in the zip 

=  0.9.8 =
* BUGFIX: New Lines detected and handled properly in cases where the mail client doesn't put a space before the new line (Miss Distance)
* BUGFIX: 3gp mime type added (Paco Cotera)
* BUGFIX: Authorized Email Addresses are not case-insensitive
* FEATURE: The larger image now does a proper pop up 
* BUGFIX: Fixed Timeing Issue - turns out it wasn't reading the db at all
* FEATURE: New Test Screen - to help track down problems

=  0.9.7 =
* BUGFIX: removed all short tags
* BUGFIX: There were spacing issues in the way I wrote the QT embed statements 
* FEATURE: Added calls to WP-Cron - should work with that properly now if you activate Cronless Postie
* FEATURE: ImageMagick version works without any calls to GD
* BUGFIX: Postie now correctly handles cases wjere tjere are multiple blogs in one db
* BUGFIX: Turned off warnings when using without GD
* FEATURE: add the rotate:X  to your message to rotate all images
* FEATURE: new filter_postie_thumbnail_with_full which makes it easy to show a thumbnail on the front page but full image on the single page - see FAQ

=  0.9.6 =
* BUGFIX: handles email addresses that are no name and just <email@email.com> (Steve Cooley Reported)
* FEATURE: Basic support for embedding flash files
* BUGFIX: Postie now handles creating the correct URL on non Unix platforms
* BUGFIX: Fixed problem with file attachments not being put in the right place.
* FEATURE: You can now choose to use imagemagick convert to handle making thumbnails
* BUGFIX: Rewrote Cronless Postie to use direct sockets
* BUGFIX: Time offset is now settable just for Postie - hopefully this will fix problems for cases where the normal time offset doesn't work properly.
* FEATURE: First draft of frame for a 3GP video
* FEATURE: Option to embed 3GP in QuickTime Controller.

=  0.9.5.2 =
* BUGFIX: gmt variable not being set correctly
* BUGFIX: Changed the name of the Check Mail button to fix an issue with mod_security
* BUGFIX: Fixed issue with Cronless-Postie
* BUGFIX: There was an argument passed by reference incorrectly
* FEATURE: Added in Cronless Postie Readme
* FEATURE: Added in Postie Readme

=  0.9.5.1 =
* BUGFIX: Confirmed POP3-SSL on debian-3.0
* BUGFIX: Updated the plugin version
* BUGFIX: Stopped displaying the email account
* 
=  0.9.5 =
* BUGFIX: Postie handles cases where you do not have GD
* FEATURE: You can now set the access level for posting - so other people can use the gate way
* BUGFIX: Fixed issue when admininstrator email is not tied to a user account.
* FEATURE: Can now reset all Postie configurations back to defaults
* BUGFIX: HTML Emails with embedded images are now handled properly.
* BUGFIX: The time difference should work correctly now
* BUGFIX: Postie's configs are completely seperate from Writing-By-Mail
* FEATURE: Warning if you use Gmail to make sure you turn on POP support
* BUGFIX: Manual Check Mail Button in interface
* BUGFIX: fixed issue of compatability with cjd-notepad
* BUGFIX: Windows Works Now

=  0.9.4 =
* BUGFIX: Cronless Postie - fixed the include statement
* BUGFIX: Authorized Addresses now supports a single address
* FEATURE: All configuration in Postie done in a single screen
* FEATURE: AUTHORIZATION can be completely overridden
* BUGFIX: line 1159 - didn't handle cases where the table didn't exist already very well
* FEATURE: Detects if you can do IMAP
* FEATURE: Added IMAP Support
* FEATURE: Added IMAP-SSL Support
* FEATURE: Added POP3-SSL Support

=  0.9.3 =
* Bug fixes for IIS

=  0.9.2 =
* Moved to more of a DIRECTORY_SEPARATOR structure 

=  0.9.1 =
* Added a define to fix a problem with over including

=  0.9 =
* Converted to an honest to god plugin
* BUGFIX: If you put a single category:subject it now works
* BUGFIX: ? Special characters may be supported?  The test post now shows a lot of umlats and accents?
* BUGFIX: The last ] in a subject with categories is now filtered out
* FEATURE: -1- subject - will put the post in category 1

=  0.312.13 =
* Code clean up - The main loop is finally readable by even non programmers
* FEATURE - You can now post to multiple categories at one time by using the [#],[Category Name], [Cat] in the subject
* FEATURE - You can now select a category by just including the begining characters [G] will select General 
* if you don't have any other categories that start with g
* FEATURE - Jay Talbot - added a new feature so you can have multiple email addresses be allowed in
* Make multi category posting more obvious
* BUG FIX: Timezones of GMT+? should now work properly
* BUG FIX: Able to handle mis-mime typed images as long as they are named with .jpg/.gif/.png

=  0.312.12 =
* Code clean up - slowing shrinking the main to make it easier to fix things
* FEATURE: Be able to turn on/off allowing comments in an email
* BUG FIX: AppleDouble now mostly supported 
* BUG FIX: MIME handling improved.
* BUG FIX: Fix issue with timing delay

=  0.312.11 =
* FEATURE: Patterns to define where a sig starts are user configurable
* FEATURE: Add filter options for banned file names
* BUG FIX: Made it possible to turn off posting to the db for testing purposes

=  0.312.10 =
* FEATURE: Added in code to diplay the mime type of the file being linked to
* BUG FIX: It now tests for the existance of the directories and makes sure
* that the web server can write to them

=  0.312.9 =
* FEATURE:Should handle jpg as well as jpeg as the file type
* BUG FIX: Now correctly handles the subject in the message
* BUG FIX: Should handle Text preferences correctly 

=  0.312.8 =
* Some general code tidying. 
* FEATURE: Can now have email from invalid email addresses automatically forwarded
* to the admin's email account. This forward includes all attachments. 
* Props to David Luden for getting this started.
* Minor change: The system will continue if it runs into a message that doesn't have 
* any content - it will also continue to process if it gets an email from 
* someone not in the system. In the past this could result in deleted mail
* if your cron job didn't run often enough.

=  0.312.7 =
* Confirm the handling of 3gp video for cell phones o
* Added in new directive SUPPORTED_FILE_TYPES -if the mime type is listed here then the system will try to make a link to it without making a thumb nail.

=  0.312.6 =
* Bug Fix: Ok the last bug I fixed - actually caused another bug - man I should set up some unit tests. Now it handles mail from the nokia mail client correctly.

=  0.312.5 =
* Bug Fix : The system was accepting all text/* types. Now you can set a preference (defaults to text/plain)
* to use as the main text for the post.

=  0.312.4  =
* Added in sanitize_title call suggested by Jemima
* Added in ability to provide a subject in an mms - by using #Subject#
* Fixed an issue with the time stamp system so it now automatically uses the gmt_offset from WordPress
* Fixed issue with the delay:1d1h tag that prevented it from being removed from the body.
* Fixed issue with the delay tag that caused problems if it was the last thing before an image.

=  0.312.3-HEY  (2005-05) =
* > Some changes and Bugfixes by Adrian Heydecker
* > Not (yet) in main development branch.
* Fixed bug: JPEG-thumbnails had a bigger filesize than full images caused by bad hardcoded compression value.
* Fixed bug: If images and signatures were present but no placeholder tags, the images were deleted together with the signature.
* Fixed bug: Generates valid postnames for users of mod_rewrite. Permalinks to posts should now work even when whitespaces are present in the subject line.
* Added support for Quoted Printable encoded mail.
* Added ability to encode Wordpress-posts in charset ISO-8859-1 instead of UTF-8.
* Added ability to choose JPEG-compression value for thumbnails.
* Added ability to add class="" and style="" to images.
* Added ability to use a different mailadress (eg. mobile) without setting up a new Wordpress-account.

=  0.312.2 =
* BUGFIX: It now removes the delay tag from the message

=  0.312.1 =
* Added modification for placeholder support for images (David Luden)
* Added in support to automatically scale down big images (Dirk Elmendorf)
* Fixed bug with multiple emails all getting the contents of the first image tag (Dirk Elmendorf)
* Added option to allow HTML in the body and subject of the email (Dirk Elmendorf)
* Switch config options to defines to reduce the number of global variables (Dirk Elmendorf)
* Added tests to make sure there is a trailing slash on the DIR definitions (Dirk Elmendorf)
* Add tests to see if they have gd installed (Dirk Elmendorf)
* Separate the scaling out to a function for easier usage (Dirk Elmendorf)
* Add delay feature for future posting. (Dirk Elmendorf)
* Added in ability to use strtotime if it is available (Dirk ELmendorf)

=  0.312 - 2005-03 =
*  CHANGE FOR DEFAULT E-mail Categories, instead of [General] Subject you can now use General: Subject in the subject line.  Less typing, and there must be a space after the colon. 
*  Fixed bugs with no default posting for categories and user 

=  0.311 - 2005-01 =
*  eep, major bug for pop3 server. Next time I test my code more before I released, fixed so that pop3 now works.`

=  0.31 - 2004-12 & 2005-01 =
* (Has it been this long, best get back into the swing of things... did most of this coding on my holiday as I didn't have a machine to play WoW on :)
*  moved the deletion of pop3 emails into a check so that e-mails aren't deleted without proper checking.
*  added HTML 'decoding' (basic support for Thunderbird & Outlook) 
*  updated the Category search so that it matches words as well as numbers (i.e. [General] Subjectname will work instead of just [1] Subjectname)
*  Changed time function from time to strtotime (as per Senior Pez's suggestion), but found out that strtotime isn't in default php distro so removed...

= 0.3 - 2004-09 =
*  Added UBB decoding support
*  Added default title (when there is no subject assigned)
*  Started doing a little code cleanup, been reading Advanced PHP Book :)
* 
=  0.2 - 2004-08 =
*  Stopped using pear body decoding in favour of own decoding (may be slower but more modifiable) because of enriched text decoding
*  Added base64_decode checking (may help mobile phone users)
*  Fixed Subject line for non-english users (htmlentities instead of just trim)
*  Fixed error in some pop hanging -> more graceful exit on event on no emails in inbox ($pop3->quit)
*  Added work around for email addresses with exta <> in field (ie: <blade@lansmash.com> instead of blade@lasmash.com
*  Added some ===basic=== enriched text support
*  Updated readme file for easier install
*  Easy modify of globals (such as PHOTOSDIR and FILESDIR)
*  Cleaned up some pear stuff in install
* 
=  0.1 - 2004-06 =
* First release
