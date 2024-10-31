<?php

require_once 'bootstrap.php';

use PHPUnit\Framework\TestCase;

class inlineimageTest extends TestCase {

    function parse_message_raw($message) {
        $mb = new fMailbox('imap', null, null, NULL);
        return $mb->parseMessage($message);
    }

    function parse_message_serialized($message) {
        return $this->parse_message_raw(unserialize($message));
    }

    function process_serialized_file($filepath, $config) {
        $message = file_get_contents($filepath);
        $mimeDecodedEmail = $this->parse_message_raw(unserialize($message));

        $m = new PostieMessage($mimeDecodedEmail, $config);

        $m->preprocess();
        $post = $m->process();
        $m->postprocess();

        return $post;
    }

    function process_raw_file($filepath, $config) {
        $message = file_get_contents($filepath);
        $mimeDecodedEmail = $this->parse_message_raw($message);
        $m = new PostieMessage($mimeDecodedEmail, $config);

        $m->preprocess();
        $post = $m->process();
        $m->postprocess();

        return $post;
    }

    function testSimpleHtmlDomWithWhitespace() {
        $html = str_get_html("\n", true, true, DEFAULT_TARGET_CHARSET, false);
        $r = $html->save();
        $this->assertEquals("", $r);

        $html = str_get_html("<div>\n<p>some text</p>\n</div>\n", true, true, DEFAULT_TARGET_CHARSET, false);
        $r = $html->save();
        $this->assertEquals("<div>\n<p>some text</p>\n</div>", $r);
    }

    function testBase64Subject() {
        $decoded = $this->parse_message_serialized(file_get_contents("data/b-encoded-subject.var"));
        $this->assertEquals("テストですよ", $decoded['headers']['subject']);
    }

    function testQuotedPrintableSubject() {
        $message = file_get_contents("data/q-encoded-subject.var");
        $mb = new fMailbox('imap', null, null, NULL);
        $decoded = $mb->parseMessage(unserialize($message));
        $this->assertEquals("Pár minut před desátou a jsem v práci první", $decoded['headers']['subject']);
    }

    function testInlineImage() {
        global $g_option;
        $g_option = 'GMT';
        $config = config_GetDefaults();
        $config['prefer_text_type'] = 'html';
        $config['imagetemplate'] = '<a href="{FILELINK}">{FILENAME}</a>';
        $config[PostieConfigOptions::TurnAuthorizationOff] = true;

        $post = $this->process_serialized_file("data/inline.var", $config);
        $this->assertEquals('<div class="postie-post">test<div><br></div><div><img src="http://example.net/wp-content/uploads/filename" alt="Inline image 1"><br></div><div><br></div><div>test</div></div>', $post['post_content']);
        $this->assertEquals('inline', $post['post_title']);
    }

    function testLineBreaks() {
        global $g_option;
        $g_option = 'GMT';
        //should add <br> tags from plain text source
        $config = config_GetDefaults();
        $config['convertnewline'] = true;
        $config['filternewlines'] = true;

        $post = $this->process_serialized_file("data/linebreaks.var", $config);
        echo "testLineBreaks: original:\n" . $post['post_content'];
        $this->assertEquals('<div class="postie-post">' . "Test<br />\n<br />\nEen stuck TekstEen stuck TekstEen stuck TekstEen stuck Tekst<br />\n<br />\nEen stuck TekstEen stuck Tekst<br />\n<br />\n<br />\nEen stuck TekstEen stuck Tekst</div>", $post['post_content']);
    }

    function testIcsAttachement() {
        global $g_option;
        $g_option = 'GMT';
        $config = config_GetDefaults();
        $config['prefer_text_type'] = 'html';

        $post = $this->process_serialized_file("data/ics-attachment.var", $config);
        $this->assertNotEqualsIgnoringCase("<div dir='ltr'>sample text<div><br></div></div><div class=\"postie-attachments\"><a href='http://example.net/wp-content/uploads/filename'><img src='http://localhost/postie/wp-content/plugins/postie/icons/silver/default-32.png' alt='default icon' />sample.ics</a></div>", trim($post['post_content']));
    }

    function testCidImagePdf() {
        global $g_option;
        $g_option = 'GMT';
        $config = config_GetDefaults();
        $config['prefer_text_type'] = 'html';
        $config['images_append'] = true;

        $post = $this->process_serialized_file("data/cid-image-pdf.var", $config);
        $this->assertNotEqualsIgnoringCase('<div dir="ltr">testing image and pdf<div><br></div><div><img src="http://example.net/wp-content/uploads/filename" width="544" height="389"><br>​<br></div></div><div class="postie-attachments"><a href="http://example.net/wp-content/uploads/filename"><img src=\'http://localhost/postie/wp-content/plugins/postie/icons/silver/default-32.png\' alt=\'default icon\' /> The product value Ladder.pdf</a><br /></div>', trim($post['post_content']));
    }

    function testCidImagePdf2() {
        global $g_option;
        $g_option = 'GMT';
        $config = config_GetDefaults();
        $config['prefer_text_type'] = 'html';
        $config['images_append'] = false;

        $post = $this->process_serialized_file("data/cid-image-pdf.var", $config);
        $this->assertNotEqualsIgnoringCase('<div class="postie-attachments"><a href="http://example.net/wp-content/uploads/filename"><img src=\'http://localhost/postie/wp-content/plugins/postie/icons/silver/default-32.png\' alt=\'default icon\' /> The product value Ladder.pdf</a><br /></div><div dir="ltr">testing image and pdf<div><br></div><div><img src="http://example.net/wp-content/uploads/filename" width="544" height="389"><br>​<br></div></div>', trim($post['post_content']));
    }

    function testImagePdfGallery() {
        global $g_option;
        $g_option = 'GMT';
        $config = config_GetDefaults();
        $config['images_append'] = true;
        $config['auto_gallery'] = true;
        $config['prefer_text_type'] = 'html';

        $post = $this->process_serialized_file("data/cid-image-pdf.var", $config);
        $this->assertNotEqualsIgnoringCase('<div dir="ltr">testing image and pdf<div><br></div><div><img src="http://example.net/wp-content/uploads/filename" width="544" height="389"><br>​<br></div></div><div class="postie-attachments"><a href="http://example.net/wp-content/uploads/filename"><img src=\'http://localhost/postie/wp-content/plugins/postie/icons/silver/default-32.png\' alt=\'default icon\' /> The product value Ladder.pdf</a><br /></div>', trim($post['post_content']));
    }

    function testTagsImg() {
        global $g_get_posts;
        $g_get_posts = 1;
        global $g_option;
        $g_option = 'GMT';
        echo "testTagsImg";
        $config = config_GetDefaults();
        $config['start_image_count_at_zero'] = true;
        $config['imagetemplate'] = '<a href="{FILELINK}">{FILENAME}</a>';
        $config['filternewlines'] = false;

        $post = $this->process_serialized_file("data/only-tags-img.var", $config);
        $this->assertEquals('tags test', $post['post_title']);
        $this->assertEquals(2, count($post['tags_input']));
        $this->assertEquals('test', $post['tags_input'][0]);
        $this->assertEquals('tag2', $post['tags_input'][1]);
        $this->assertEquals('<div class="postie-post">' . "\n" . '<a href="http://example.net/wp-content/uploads/filename">close_account.png</a><br />' . "\n</div>", $post['post_content']);
    }

    function testSig() {
        global $g_option;
        $g_option = 'GMT';
        echo "testSig";
        $config = config_GetDefaults();
        $config['prefer_text_type'] = 'plain';

        $post = $this->process_serialized_file("data/signature.var", $config);
        $this->assertEquals('<div class="postie-post">test content</div>', $post['post_content']);

        $config['prefer_text_type'] = 'html';
        $post = $this->process_serialized_file("data/signature.var", $config);
        $this->assertEquals('<div class="postie-post">test content<div><br></div><div></div>', $post['post_content']);
    }

    function testQuotedPrintable() {
        $str = quoted_printable_decode("ABC=C3=C4=CEABC=");
        $str = iconv('ISO-8859-7', 'UTF-8', $str);
        $this->assertEquals("ABCΓΔΞABC", $str);

        $str = quoted_printable_decode('<span style=3D"font-family:arial,sans-serif;font-size:13px">ABC=C3=C4=CEABC=</span><br>');
        $str = iconv('ISO-8859-7', 'UTF-8', $str);
        $this->assertEquals('<span style="font-family:arial,sans-serif;font-size:13px">ABCΓΔΞABC=</span><br>', $str);
    }

    function testGreek() {
        $message = file_get_contents("data/greek.var");
        $mb = new fMailbox('imap', null, null, NULL);
        $decoded = $mb->parseMessage($message);

        $this->assertEquals('ABCΓΔΞABC', $decoded['text']);
    }

    public function test_filter_ReplaceImagePlaceHolders() {
        global $g_get_posts;
        $pconfig = new PostieConfig();

        $c = "";
        $config = $pconfig->defaults();
        $config['allow_html_in_body'] = true;

        $theemail = array('attachment' => array(), 'inline' => array(), 'related' => array());

        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals("", $cp);

        $theemail = array('attachment' => array(array('wp_filename' => 'image.jpg', 'wp_id' => 1, 'template' => '<img title="{CAPTION}" />')), 'inline' => array(), 'related' => array());
        $g_get_posts = array((object) array('ID' => 1));

        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('', $cp);

        $c = "#img1#";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('<img title="" />', $cp);

        $c = "test #img1# test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <img title="" /> test', $cp);

        $c = "test #img1 caption='1'# test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <img title="1" /> test', $cp);

        $c = "test #img1 caption=1# test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <img title="1" /> test', $cp);

        $c = "test #img1 caption='! @ % ^ & * ( ) ~ \"Test\"'# test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <img title="! @ % ^ &amp; * ( ) ~ &quot;Test&quot;" /> test', $cp);

        $c = "test <div>#img1 caption=&#39;! @ % ^ &amp; * ( ) ~ &quot;Test&quot;&#39;#</div> test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <div><img title="&amp;" />39;! @ % ^ &amp; * ( ) ~ &quot;Test&quot;&#39;#</div> test', $cp);

        $c = "test #img1 caption=\"I'd like some cheese.\"# test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <img title="I&#039;d like some cheese." /> test', $cp);

        $c = "test #img1 caption=\"Eiskernbrecher mögens laut\"# test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <img title="Eiskernbrecher mögens laut" /> test', $cp);

        $c = "test #img1 caption='[image-caption]'# test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <img title="[image-caption]" /> test', $cp);

        $c = "test #img1 caption='1'# test #img2 caption='2'#";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#");
        $this->assertEquals('test <img title="1" /> test #img2 caption=\'2\'#', $cp);

        $theemail = array(
            'attachment' => array(
                array('wp_filename' => 'image.jpg', 'wp_id' => 1, 'template' => 'template with {CAPTION}'),
                array('wp_filename' => 'image.jpg', 'wp_id' => 2, 'template' => 'template with {CAPTION}')
            ), 'inline' => array(), 'related' => array()
        );
        $c = "test #img1 caption='1'# test #img2 caption='2'#";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#", true);
        $this->assertEquals("test template with 1 test template with 2", $cp);

        $g_get_posts = array();
        $theemail = array('attachment' => array(), 'inline' => array(), 'related' => array());

        $c = "test";
        $cp = filter_ReplaceImagePlaceHolders($c, $theemail, $config, 1, "#img%#", true);
        $this->assertEquals("test", $cp);
    }

    function test_filter_AttachmentTemplates_gallery() {
        global $g_get_posts;

        $config = config_GetDefaults();
        $config['auto_gallery'] = true;
        $config['images_append'] = false;

        $theemail = array(
            'attachment' => array(
                array('wp_filename' => 'image1.jpg', 'template' => 'template with {CAPTION}', 'wp_id' => 1, 'exclude' => false, 'primary' => 'image'),
                array('wp_filename' => 'image2.jpg', 'template' => 'template with {CAPTION}', 'wp_id' => 2, 'exclude' => false, 'primary' => 'image')
            ),
            'inline' => array(),
            'related' => array()
        );
        $g_get_posts = array((object) array('ID' => 1), (object) array('ID' => 2));

        $c = "test";
        $cp = filter_AttachmentTemplates($c, $theemail, 1, $config);
        $this->assertEquals("[gallery]\ntest", $cp);

        $config['images_append'] = true;
        $c = "test";
        $cp = filter_AttachmentTemplates($c, $theemail, 1, $config);
        $this->assertEquals("test\n[gallery]", $cp);

        $config['images_append'] = true;
        $c = "test";
        $cp = filter_AttachmentTemplates($c, $theemail, 1, $config);
        $this->assertEquals("test\n[gallery]", $cp);
    }

    function test_iconvissue() {
        $mimeDecodedEmail = $this->parse_message_raw(file_get_contents("data/plaintexticonvissue.txt"));
        $this->assertEquals("BCN45:SONOMA CO.: SHERIFF CHALLENGES IMPLICATIONS THAT MAN", $mimeDecodedEmail['headers']['subject']);
        $this->assertEquals(4729, strlen($mimeDecodedEmail['text']));
    }

    function test_content_type_name_issue() {
        $mimeDecodedEmail = $this->parse_message_raw(file_get_contents("data/content-type-name.txt"));
        $this->assertEquals(1, count($mimeDecodedEmail['attachment']));
        $this->assertEquals('AOO_OptIn_Customers.csv', $mimeDecodedEmail['attachment'][0]['filename']);
    }

    function test_base64email() {
        $mimeDecodedEmail = $this->parse_message_raw(file_get_contents("data/base64 format.txt"));
        $this->assertEquals("СКИДКА 15% на  Dorothy Perkins!+24 часа Скидка 500 р. на ботинки!", $mimeDecodedEmail['headers']['subject']);
    }

    function test_base64email1() {
        $mimeDecodedEmail = $this->parse_message_raw(file_get_contents("data/141101113547D8.17694@mscreator239.fagms_.de.txt"));
        $this->assertEquals("Новинки изысканных брендов внутри: Bikkembergs, Fabi, Love Moschino!", $mimeDecodedEmail['headers']['subject']);
    }

    function test_cid_alt() {
        $mimeDecodedEmail = $this->parse_message_raw(file_get_contents("data/cid-alt.txt"));
        $this->assertEquals('Test message inline images', $mimeDecodedEmail['headers']['subject']);
        $this->assertEquals(4, count($mimeDecodedEmail['related']));
        $this->assertEquals(0, count($mimeDecodedEmail['attachment']));
        $this->assertEquals(0, count($mimeDecodedEmail['inline']));
        DebugEcho($mimeDecodedEmail['text']);
        DebugEcho($mimeDecodedEmail['html']);
    }

    function test_cid2_alt() {
        global $g_option;
        $g_option = 'GMT';

        $config = config_GetDefaults();
        $config[PostieConfigOptions::PreferTextType] = 'html';
        $post = $this->process_raw_file("data/cid-alt.txt", $config);
        $this->assertEquals(false, stripos($post['post_content'], 'cid:ii_jml1ro6w0'));
        DebugEcho($post['post_content']);
    }

    // Could not update post in the database
    function test_update_post_fails() {
        global $g_get_term_by;
        $g_get_term_by->name = 'term';
        global $g_option;
        $g_option = '';
        $config = config_GetDefaults();
        $config[PostieConfigOptions::PreferTextType] = 'html';

        $post = $this->process_raw_file('data/moonsit.co.uk-raw.txt', $config);

        $this->assertEquals(4018, strlen($post['post_content']));
        DebugEcho($post['post_content']);
    }

    function test_signature_plain() {
        global $g_option;
        $g_option = 'GMT';
        $config = config_GetDefaults();
        $config[PostieConfigOptions::TurnAuthorizationOff] = true;

        $post = $this->process_raw_file("data/mgnp.info-raw.txt", $config);
        $this->assertEquals("letter - only html", $post['post_title']);
        $this->assertEquals(348, strlen($post['post_content']));
    }

    function test_signature_html() {
        global $g_option;

        $g_option = 'GMT';
        $config = config_GetDefaults();
        $config[PostieConfigOptions::TurnAuthorizationOff] = true;
        $config['prefer_text_type'] = 'html';

        $post = $this->process_raw_file("data/mgnp.info-raw.txt", $config);

        $this->assertEquals("letter - only html", $post['post_title']);
        $this->assertEquals(700, strlen($post['post_content']));
    }

    function test_outlook_quoted_printable() {
        global $g_get_term_by;
        global $g_option;
        global $g_postie;

        $config = config_GetDefaults();
        $g_get_term_by = new stdClass();
        $g_get_term_by->term_id = 1;
        $g_get_term_by->name = 'term name';
        $g_option = 'GMT';

        $message = file_get_contents("data/outlook-encoding.var");
        $mb = new fMailbox('imap', null, null, NULL);
        $mimeDecodedEmail = $mb->parseMessage($message);

        print_r($mimeDecodedEmail);

        $this->assertEquals("RE: tryout 10", $mimeDecodedEmail['headers']['subject']);

        $m = new PostieMessage($mimeDecodedEmail, $config);

        $m->preprocess();
        $details = $m->process();
        $m->postprocess();

        $this->assertFalse(strpos($details['post_content'], '<='));
    }

    function testApple_mail() {
        global $g_option;
        $g_option = 'GMT';
        //message is text only
        //multi-part with text then image then text then image
        $config = config_GetDefaults();
        global $g_get_term_by;
        $g_get_term_by = new stdClass();
        $g_get_term_by->term_id = 1;
        $g_get_term_by->name = 'term name';

        $post = $this->process_serialized_file("data/apple-mail.var", $config);

        $this->assertEquals(6, substr_count($post['post_content'], "http://example.net/wp-content/uploads/filename.jpg"));
    }

}
