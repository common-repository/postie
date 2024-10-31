<?php

require_once 'bootstrap.php';

use PHPUnit\Framework\TestCase;

class postiefunctionsTest extends TestCase {

    public function testemail_notify() {
        $m = new PostieMessage(array(), config_GetDefaults());
        $m->email_notify(array('help@postieplugin.com'), 1);
        $this->assertTrue(true);
    }

    public function testemail_error() {
        $m = new PostieMessage(array(), config_GetDefaults());
        $m->email_error('test subject', 'test message');
        $this->assertTrue(true);
    }

    public function testRemoveExtraCharactersInEmailAddress() {
        $m = new PostieMessage(array(), config_GetDefaults());

        $this->assertEquals("", $m->get_clean_emailaddress(''));
        $this->assertEquals("wayne@postieplugin.com", $m->get_clean_emailaddress('wayne@postieplugin.com'));
        $this->assertEquals("wayne@postieplugin.com", $m->get_clean_emailaddress('Wayne <wayne@postieplugin.com>'));
        //$this->assertEquals("hacker@badsite.com", $m->remove_extra_characters_in_email_address('hacker@badsite.com<BODY ONLOAD=alert("XSS")>'));
    }

    public function testAllowCommentsOnPost() {
        global $g_option;
        $config = config_GetDefaults();

        $g_option = 'open';
        $original_content = "test content, no comment control";
        $modified_content = "test content, no comment control";
        $allow = tag_AllowCommentsOnPost($modified_content, $config);
        $this->assertEquals('open', $allow);
        $this->assertEquals($original_content, $modified_content);

        $original_content = "test content, comment control closed\n";
        $modified_content = "test content, comment control closed\ncomments:0";
        $allow = tag_AllowCommentsOnPost($modified_content, $config);
        $this->assertEquals('closed', $allow);
        $this->assertEquals($original_content, $modified_content);

        $original_content = "test content, comment control open\n";
        $modified_content = "test content, comment control open\ncomments:1";
        $allow = tag_AllowCommentsOnPost($modified_content, $config);
        $this->assertEquals('open', $allow);
        $this->assertEquals($original_content, $modified_content);

        $original_content = "test content, comment control registered only\n";
        $modified_content = "test content, comment control registered only\ncomments:2";
        $allow = tag_AllowCommentsOnPost($modified_content, $config);
        $this->assertEquals('registered_only', $allow);
        $this->assertEquals($original_content, $modified_content);

        $original_content = "test content, comment control registered only\ncomments:4";
        $modified_content = "test content, comment control registered only\ncomments:4";
        $allow = tag_AllowCommentsOnPost($modified_content, $config);
        $this->assertEquals('open', $allow);
        $this->assertEquals($original_content, $modified_content);
    }

    public function testBannedFileName_null() {
        $config = config_GetDefaults();
        $config['banned_files_list'] = null;
        $m = new PostieMessage(array(), $config);

        $this->assertFalse($m->is_filename_banned(""));
        $this->assertFalse($m->is_filename_banned("test"));
    }

    public function testBannedFileName_singlewildcard() {
        $config = config_GetDefaults();
        $config['banned_files_list'] = array("*.exe");
        $m = new PostieMessage(array(), $config);

        $this->assertTrue($m->is_filename_banned("test.exe"));
        $this->assertFalse($m->is_filename_banned("test.pdf"));
        $this->assertTrue($m->is_filename_banned("test test.exe"));
    }

    public function testBannedFileName_multiplewildcard() {
        $config = config_GetDefaults();
        $config['banned_files_list'] = array("*.exe", "*.js", "*.cmd");
        $m = new PostieMessage(array(), $config);

        $this->assertFalse($m->is_filename_banned("test.pdf"));
        $this->assertFalse($m->is_filename_banned("test.cmd.pdf"));
    }

    public function testget_subject_none() {
        $config = config_GetDefaults();
        $m = new PostieMessage(array(), $config);
        $m->content = '';
        $m->extract_subject();
        $this->assertEquals($m->subject, 'Live From The Field');
    }

    public function testget_subject_stdheader() {
        $config = config_GetDefaults();
        $config['allow_subject_in_mail'] = false;
        $m = new PostieMessage(array('headers' => array('subject' => 'test')), $config);
        $m->content = '';
        $m->extract_subject();
        $this->assertEquals($m->subject, 'test');
    }

    public function testBannedFileName_isbanned() {
        $config = config_GetDefaults();
        $config['banned_files_list'] = array("test");
        $m = new PostieMessage(array(), $config);

        $this->assertTrue($m->is_filename_banned("test"));
    }

    public function testBannedFileName_isnotbanned() {
        $config = config_GetDefaults();
        $config['banned_files_list'] = array("test");
        $m = new PostieMessage(array(), $config);

        $this->assertFalse($m->is_filename_banned("test1"));
    }

    public function testBannedFileName_blank() {
        $config = config_GetDefaults();
        $config['banned_files_list'] = '';
        $m = new PostieMessage(array(), $config);

        $this->assertFalse($m->is_filename_banned(''));
        $this->assertFalse($m->is_filename_banned("test"));
    }

    public function testBannedFileName_empty() {
        $config = config_GetDefaults();
        $config['banned_files_list'] = array();
        $m = new PostieMessage(array(), $config);

        $this->assertFalse($m->is_filename_banned(""));
        $this->assertFalse($m->is_filename_banned("test"));
    }

    public function testCheckEmailAddress() {
        $m = new PostieMessage(array(), config_GetDefaults());

        $this->assertFalse($m->is_emailaddress_authorized(null, null));
        $this->assertFalse($m->is_emailaddress_authorized(null, array()));
        $this->assertFalse($m->is_emailaddress_authorized("", array()));
        $this->assertFalse($m->is_emailaddress_authorized("", array("")));
        $this->assertFalse($m->is_emailaddress_authorized("bob", array("jane")));
        $this->assertTrue($m->is_emailaddress_authorized("bob", array("bob")));
        $this->assertTrue($m->is_emailaddress_authorized("bob", array("BoB")));
        $this->assertTrue($m->is_emailaddress_authorized("bob", array("bob", "jane")));
        $this->assertTrue($m->is_emailaddress_authorized("bob", array("jane", "bob")));
    }

    public function test_filter_Delay() {
        global $g_option;
        $g_option = 'GMT';
        $config = config_GetDefaults();

        $content = "test";
        $r = tag_Delay($content, null, $config);
        $this->assertTrue(is_array($r));
        $this->assertEquals(2, count($r));
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test delay:";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test delay:", $content);

        $content = "test\ndelay:1h";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(3600, $r[1]);
        $this->assertEquals("test\n", $content);

        $content = "test\ndelay:1d";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(86400, $r[1]);
        $this->assertEquals("test\n", $content);

        $content = "test\ndelay:1m";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(60, $r[1]);
        $this->assertEquals("test\n", $content);

        $content = "test\ndelay:m";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test\n", $content);

        $content = "test\ndelay:dhm";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test\n", $content);

        $content = "test\ndelay:x";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test\ndelay:x", $content);

        $content = "test\ndelay:-1m";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(-60, $r[1]);
        $this->assertEquals("test\n", $content);

        $content = "test\ndelay:1d1h1m";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(90060, $r[1]);
        $this->assertEquals("test\n", $content);

        $content = "test\ndelay:d1hm";
        $r = tag_Delay($content, null, $config);
        $this->assertEquals(3600, $r[1]);
        $this->assertEquals("test\n", $content);

        $content = "test";
        $r = tag_Delay($content, '2012-11-20 08:00', $config);
        $this->assertEquals('2012-11-20T08:00:00+00:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test";
        $r = tag_Delay($content, 'Mon, 06 Sep 2004 08:15:56 +0000 (America/Los_Angeles)', $config);
        $this->assertEquals('2004-09-06T08:15:56+00:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test";
        $r = tag_Delay($content, 'Wed, 19 Apr 2017 00:00:00 +0200 (CEST)', $config);
        $this->assertEquals('2017-04-18T22:00:00+00:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test";
        $config['ignore_email_date'] = true;
        $r = tag_Delay($content, 'Wed, 19 Apr 2017 00:00:00 +0200 (CEST)', $config);
        $this->assertEquals('2005-08-05T10:41:13+00:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test";
        $config['ignore_email_date'] = false;
        $config['use_time_offset'] = true;
        $config['time_offset'] = -1;
        $r = tag_Delay($content, '2012-11-20 08:00', $config);
        $this->assertEquals('2012-11-20T07:00:00+00:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test";
        $config['ignore_email_date'] = false;
        $config['use_time_offset'] = true;
        $config['time_offset'] = 1;
        $r = tag_Delay($content, '2012-11-20 08:00', $config);
        $this->assertEquals('2012-11-20T09:00:00+00:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test";
        $config['ignore_email_date'] = false;
        $config['use_time_offset'] = false;
        $config['time_offset'] = 1;
        $r = tag_Delay($content, '2012-11-20 08:00', $config);
        $this->assertEquals('2012-11-20T08:00:00+00:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test";
        $config['ignore_email_date'] = true;
        $config['use_time_offset'] = false;
        $config['time_offset'] = 1;
        $r = tag_Delay($content, '2012-11-20 08:00', $config);
        $this->assertEquals('2005-08-05T10:41:13+00:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);

        $content = "test";
        $config['ignore_email_date'] = true;
        $config['use_time_offset'] = false;
        $g_option = 'Europe/Vienna';
        $r = tag_Delay($content, 'Wed, 19 Apr 2017 00:00:00 +0200 (CEST)', $config);
        $this->assertEquals('2005-08-05T10:41:13+02:00', $r[0]);
        $this->assertEquals(0, $r[1]);
        $this->assertEquals("test", $content);
    }

    public function test_filter_Start() {
        $config = config_GetDefaults();
        $config['message_start'] = ':start';

        $c = "test";
        $cp = filter_Start($c, $config);
        $this->assertEquals("test", $cp);

        $c = ":start\ntest";
        $cp = filter_Start($c, $config);
        $this->assertEquals("\ntest", $cp);

        $c = "test/n:start\nsomething";
        $cp = filter_Start($c, $config);
        $this->assertEquals("\nsomething", $cp);

        $c = "<p>test</p><p>:start</p><p>something</p>";
        $cp = filter_Start($c, $config);
        $this->assertEquals("</p><p>something</p>", $cp);
    }

    public function test_filter_End() {
        $config = config_GetDefaults();
        $config['message_end'] = ':end';

        $c = "test";
        $cp = filter_End($c, $config);
        $this->assertEquals("test", $cp);

        $c = "test :end";
        $cp = filter_End($c, $config);
        $this->assertEquals("test ", $cp);

        $c = "test :end test";
        $cp = filter_End($c, $config);
        $this->assertEquals("test ", $cp);

        $c = "tags: Station, Kohnen, Flugzeug\n:end\n21.10.2012";
        $cp = filter_End($c, $config);
        $this->assertEquals("tags: Station, Kohnen, Flugzeug\n", $cp);

        $c = "This is a test :end";
        $cp = filter_End($c, $config);
        $this->assertEquals("This is a test ", $cp);

        $c = "<p>This is a test</p><p>:end</p><div>some footer</div>";
        $cp = filter_End($c, $config);
        $this->assertEquals("<p>This is a test</p><p>", $cp);
    }

    public function test_filter_Newlines() {
        $config = config_GetDefaults();

        $c = "test";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test", $pc);

        $c = "test";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test", $pc);

        $c = "test\n";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test ", $pc);

        $c = "test\r\n";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test ", $pc);

        $c = "test\r";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test ", $pc);

        $c = "test\n\n";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test\r\n", $pc);

        $c = "test\r\n\r\n";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test\r\n", $pc);

        $c = "test\r\n\r\ntest\n\ntest\rtest\r\ntest\ntest";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test\r\ntest\r\ntest test test test", $pc);

        $config['convertnewline'] = true;

        $c = "test\n";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test<br />\n", $pc);

        $c = "test\n\n";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test<br />\n<br />\n", $pc);

        $c = "test\r";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test<br />\n", $pc);

        $c = "test\r\n";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test<br />\n", $pc);

        $c = "test\r\n\r\n";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test<br />\n<br />\n", $pc);

        $c = "test\r\n\r\ntest\n\ntest\rtest\r\ntest\ntest";
        $pc = filter_Newlines($c, $config);
        $this->assertEquals("test<br />\n<br />\ntest<br />\n<br />\ntest<br />\ntest<br />\ntest<br />\ntest", $pc);
    }

    public function testGetNameFromEmail() {
        $m = new PostieMessage(array(), config_GetDefaults());

        $this->assertEquals("", $m->get_name_from_email(""));
        $this->assertEquals("Wayne", $m->get_name_from_email('Wayne <wayne@devzing.com>'));
        $this->assertEquals("wayne", $m->get_name_from_email('wayne@devzing.com'));
        $this->assertEquals("hacker", $m->get_name_from_email('hacker@badsite.com<BODY ONLOAD=alert("XSS")>'));
    }

    public function testGetPostType() {
        //use a fresh copy of config since it gets changed
        $subject = "test";
        $config = config_GetDefaults();
        $this->assertEquals('post', tag_PostType($subject, $config)['post_type']);
        $this->assertEquals('standard', tag_PostType($subject, $config)['post_format']);
        $this->assertEquals("test", $subject);
        $this->assertEquals('post', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('standard', $config[PostieConfigOptions::PostFormat]);

        $subject = "custom//test";
        $config = config_GetDefaults();
        $this->assertEquals("custom", tag_PostType($subject, $config)['post_type']);
        $this->assertEquals("standard", tag_PostType($subject, $config)['post_format']);
        $this->assertEquals("test", $subject);
        $this->assertEquals('custom', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('standard', $config[PostieConfigOptions::PostFormat]);

        $subject = "//test";
        $config = config_GetDefaults();
        $this->assertEquals("post", tag_PostType($subject, $config)['post_type']);
        $this->assertEquals("standard", tag_PostType($subject, $config)['post_format']);
        $this->assertEquals("//test", $subject);
        $this->assertEquals('post', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('standard', $config[PostieConfigOptions::PostFormat]);

        $subject = "//";
        $config = config_GetDefaults();
        $this->assertEquals("post", tag_PostType($subject, $config)['post_type']);
        $this->assertEquals("standard", tag_PostType($subject, $config)['post_format']);
        $this->assertEquals("//", $subject);
        $this->assertEquals('post', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('standard', $config[PostieConfigOptions::PostFormat]);

        $subject = "custom2//test";
        $config = config_GetDefaults();
        $this->assertEquals("custom2", tag_PostType($subject, $config)['post_type']);
        $this->assertEquals("standard", tag_PostType($subject, $config)['post_format']);
        $this->assertEquals("test", $subject);
        $this->assertEquals('custom2', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('standard', $config[PostieConfigOptions::PostFormat]);

        $subject = "Custom1 // test";
        $config = config_GetDefaults();
        $this->assertEquals("custom1", tag_PostType($subject, $config)['post_type']);
        $this->assertEquals("standard", tag_PostType($subject, $config)['post_format']);
        $this->assertEquals("test", $subject);
        $this->assertEquals('custom1', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('standard', $config[PostieConfigOptions::PostFormat]);

        $subject = "video//test";
        $config = config_GetDefaults();
        $this->assertEquals("post", tag_PostType($subject, $config)['post_type']);
        $this->assertEquals("video", tag_PostType($subject, $config)['post_format']);
        $this->assertEquals("test", $subject);
        $this->assertEquals('post', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('video', $config[PostieConfigOptions::PostFormat]);

        $subject = "//WL2K /Test Message";
        $config = config_GetDefaults();
        $this->assertEquals("post", tag_PostType($subject, $config)['post_type']);
        $this->assertEquals("standard", tag_PostType($subject, $config)['post_format']);
        $this->assertEquals("//WL2K /Test Message", $subject);
        $this->assertEquals('post', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('standard', $config[PostieConfigOptions::PostFormat]);

        //test w/ non-default post format
        $config = config_GetDefaults();
        $config['post_format'] = 'aside';
        $subject = 'test';
        $this->assertEquals('post', tag_PostType($subject, $config)['post_type']);
        $this->assertEquals('aside', tag_PostType($subject, $config)['post_format']);
        $this->assertEquals('test', $subject);
        $this->assertEquals('post', $config[PostieConfigOptions::PostType]);
        $this->assertEquals('aside', $config[PostieConfigOptions::PostFormat]);
    }

    public function testGetPostExcerpt() {
        $config = config_GetDefaults();

        $c = "test";
        $this->assertEquals("", tag_Excerpt($c, $config));

        $c = ":excerptstart test :excerptend test";
        $this->assertEquals("test ", tag_Excerpt($c, $config));

        $c = ":excerptstart test";
        $this->assertEquals("", tag_Excerpt($c, $config));

        $c = "test :excerptend test";
        $this->assertEquals("", tag_Excerpt($c, $config));
    }

    public function testGetPostCategoriesMultipleColons() {
        global $wpdb;
        $wpdb->t_get_var = array('category', 'category');

        global $g_get_term_by;
        $g_get_term_by->term_id = 1;
        $g_get_term_by->name = 'term name';

        $config = config_GetDefaults();
        $config['category_colon'] = true;

        $s = "category: something: else";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals(1, $c[0]);
        $this->assertEquals("something: else", $s);
    }

    public function testGetPostCategories() {
        global $wpdb;
        global $g_get_term_by;

        $config = config_GetDefaults();
        $config['category_match'] = false;

        $s = "test";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("test", $s);

        $s = ":test";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals("default", $c[0]);
        $this->assertEquals(":test", $s);

        $g_get_term_by->term_id = 1;
        $g_get_term_by->name = 'term name';
        $wpdb->t_get_var = array('category', 'category');
        $config['category_colon'] = true;
        $s = "1: test";
        $c = tag_categories($s, 1, $config, 1);
        $this->assertEquals(1, count($c));
        $this->assertEquals("1", $c[0]);
        $this->assertEquals("test", $s);

        $g_get_term_by = false;
        $s = "not a category: test";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("not a category: test", $s);

        $s = "[not a category] test";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("[not a category] test", $s);

        $s = "-not a category- test";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("-not a category- test", $s);

        $g_get_term_by = new stdClass();
        $g_get_term_by->term_id = 1;
        $g_get_term_by->name = 'term name';
        $wpdb->t_get_var = array('category', 'category');
        $s = "general: test";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals(1, $c[0]);
        $this->assertEquals("test", $s);

        $g_get_term_by = new stdClass();
        $g_get_term_by->term_id = 1;
        $g_get_term_by->name = 'term name';
        $wpdb->t_get_var = array('category', 'category');
        $s = "technology: Bluetooth®";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals(1, $c[0]);
        $this->assertEquals("Bluetooth®", $s);

        $s = "[general] test";
        $wpdb->t_get_var = array('category', 'category');
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals(1, $c[0]);
        $this->assertEquals("test", $s);

        $s = "-general- test";
        $wpdb->t_get_var = array('category', 'category');
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals(1, $c[0]);
        $this->assertEquals("test", $s);

        $g_get_term_by = false;
        $s = "specific: test";
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("specific: test", $s);

        $g_get_term_by = new stdClass();
        $g_get_term_by->term_id = 1;
        $g_get_term_by->name = 'term name';
        $s = "[1] [1] test";
        $wpdb->t_get_var = array('category', 'category', 'category', 'category');
        $c = tag_categories($s, "default", $config, 1);
        $this->assertEquals(2, count($c));
        $this->assertEquals("1", $c[0]);
        $this->assertEquals("1", $c[1]);
        $this->assertEquals("test", $s);
    }

    public function testHTML2HTML() {
        $this->assertEquals("", filter_CleanHtml(""));
        $this->assertEquals("test", filter_CleanHtml("test"));
        $this->assertEquals("<div>test</div>\n", filter_CleanHtml("<html lang='en'><body>test</body></html>"));
        $this->assertEquals("<div>test</div>\n", filter_CleanHtml("<html lang='en'><head><title>title</title></head><body>test</body></html>"));
        $this->assertEquals("<div>test</div>\n", filter_CleanHtml("<body>test</body>"));
        $this->assertEquals("<strong>test</strong>", filter_CleanHtml("<strong>test</strong>"));
    }

    public function test_remove_signature() {
        $config = config_GetDefaults();

        $c = "";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("", $cp);

        $c = "test";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("test", $cp);

        $c = "";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("", $cp);

        $c = "test";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("test", $cp);

        $c = "line 1\nline 2\n--\nsig line 1\nsig line 2";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("line 1\nline 2", $cp);

        $c = "line 1\nline 2\n---\nsig line 1\nsig line 2";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("line 1\nline 2", $cp);

        $c = "line 1\nline 2\n-- \nsig line 1\nsig line 2";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("line 1\nline 2", $cp);

        $c = "line 1\nline 2\n--\nsig line 1\nsig line 2";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("line 1\nline 2", $cp);

        $c = "line 1\nline 2\n--";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("line 1\nline 2", $cp);
    }

    public function test_remove_signature_html() {
        $config = config_GetDefaults();
        $config['prefer_text_type'] = 'html';
        $c = "<p>test content</p><div><br></div><div>--</div><div>signature</div>";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("<p>test content</p><div><br></div><div>", $cp);
    }

    public function test_remove_signature_html_whitespace() {
        $config = config_GetDefaults();
        $config['prefer_text_type'] = 'html';
        $c = "<p></p>\n\n-- <br>\nVisit us online at";
        $cp = filter_RemoveSignature($c, $config);
        $this->assertEquals("<p></p>\n\n", $cp);
    }

    public function test_postie_more_reccurences() {
        global $g_postie_init;
        $sched = array();
        $newsched = $g_postie_init->cron_schedules_filter($sched);
        $this->assertEquals(8, count($newsched));
    }

    public function test_config() {
        $config = config_GetDefaults();
        $this->assertEquals(true, $config['legacy_commands']);
    }

    public function test_tag_Tags() {
        $config = config_GetDefaults();
        $config['prefer_text_type'] = 'html';

        $c = "";
        $t = tag_Tags($c, $config);
        $this->assertEquals(0, count($t));
        $this->assertEquals("", $c);

        $c = "test";
        $t = tag_Tags($c, $config);
        $this->assertEquals(0, count($t));
        $this->assertEquals("test", $c);

        $c = "test";
        $config['default_post_tags'] = array('tag1');
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test", $c);
        $config['default_post_tags'] = null;

        $c = "test tags:";
        $t = tag_Tags($c, $config);
        $this->assertEquals(0, count($t));
        $this->assertEquals("test tags:", $c);

        $c = "test tags:\n";
        $t = tag_Tags($c, $config);
        $this->assertEquals(0, count($t));
        $this->assertEquals("test tags:\n", $c);

        $c = "test\ntags: tag1";
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("test\n", $c);

        $c = "test\ntags: tag1";
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("test\n", $c);

        $c = "test\ntags: tag1\n";
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test\n\n", $c);

        $c = "test\ntags:tag1";
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test\n", $c);

        $c = "test\ntags:Bluetooth®";
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("Bluetooth®", $t[0]);
        $this->assertEquals("test\n", $c);

        $c = "test\ntags:tag1";
        $config['default_post_tags'] = array('tagx');
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test\n", $c);
        $config['default_post_tags'] = null;

        $c = "test\ntags:tag1,tag2";
        $t = tag_Tags($c, $config);
        $this->assertEquals(2, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("tag2", $t[1]);
        $this->assertEquals("test\n", $c);

        $c = "test\ntags: tag3,tag4\nmore stuff\n:end";
        $t = tag_Tags($c, $config);
        $this->assertEquals(2, count($t));
        $this->assertEquals("tag3", $t[0]);
        $this->assertEquals("tag4", $t[1]);
        $this->assertEquals("test\n\nmore stuff\n:end", $c);

        $c = "test\ntags:tag1,tag2\nmore stuff\n:end";
        $t = tag_Tags($c, $config);
        $this->assertEquals(2, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("tag2", $t[1]);
        $this->assertEquals("test\n\nmore stuff\n:end", $c);

        $c = "test\ntags:tag1\ntags:tag2\nmore stuff\n:end";
        $config['default_post_tags'] = array('tagx');
        $config['prefer_text_type'] = 'plain';
        $t = tag_Tags($c, $config);
        $this->assertEquals(2, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("tag2", $t[1]);
        $this->assertEquals("test\n\n\nmore stuff\n:end", $c);
        $config['default_post_tags'] = null;
        $config['prefer_text_type'] = 'html';

        $c = "<p>test</p>\n<p>tags:tag1</p>\n<p>tags:tag2</p>\n<p>more stuff</p>\n";
        $t = tag_Tags($c, $config);
        $this->assertEquals(2, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("tag2", $t[1]);
        $this->assertEquals("<p>test</p>\n<p></p>\n<p></p>\n<p>more stuff</p>\n", $c);

        $c = '<div><font face=Calibri>tags: sample tag</font></div>';
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("sample tag", $t[0]);
        $this->assertEquals("<div><font face=Calibri></font></div>", $c);

        $c = '<div>tags: sample tag<br>more stuff</div>';
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("sample tag", $t[0]);
        $this->assertEquals("<div><br>more stuff</div>", $c);

        $c = "test";
        $config['default_post_tags'] = array("tag1");
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test", $c);

        $config['default_post_tags'] = array("tagx");
        $c = "test\ntags:tag1";
        $t = tag_Tags($c, $config);
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test\n", $c);
    }

    public function test_filter_ReplaceImagePlaceHolders() {
        global $g_get_posts;

        $pconfig = new PostieConfig();
        $config = $pconfig->defaults();

        $g_get_posts = array();
        $c = "";
        $a = array('attachment' => array(), 'inline' => array(), 'related' => array());
        $cp = filter_ReplaceImagePlaceHolders($c, $a, $config, 1, "#img%#", true);
        $this->assertEquals("", $cp);

        $g_get_posts = array(1);
        $c = "#img1#";
        $a = array('attachment' => array(array('wp_filename' => 'file1.png', 'wp_id' => 1, 'template' => 'template with {CAPTION}')), 'inline' => array(), 'related' => array());
        $cp = filter_ReplaceImagePlaceHolders($c, $a, $config, 1, "#img%#", true);
        $this->assertEquals("template with ", $cp);

        $g_get_posts = array((object) array('ID' => 1));
        $c = "#img1 caption='MY CAPTION'#";
        $a = array('attachment' => array(array('wp_filename' => 'file1.png', 'wp_id' => 1, 'template' => 'template with {CAPTION}')), 'inline' => array(), 'related' => array());
        $cp = filter_ReplaceImagePlaceHolders($c, $a, $config, 1, "#img%#", true);
        $this->assertEquals("template with MY CAPTION", $cp);

        $g_get_posts = array((object) array('ID' => 1), (object) array('ID' => 2));
        $c = "#img1# #img1 caption='MY CAPTION'#";
        $a = array('attachment' => array(array('wp_filename' => 'file1.png', 'wp_id' => 1, 'template' => '<img alt="{CAPTION}"/>')), 'inline' => array(), 'related' => array());
        $cp = filter_ReplaceImagePlaceHolders($c, $a, $config, 1, "#img%#", true);
        $this->assertEquals('<img alt=""/> <img alt="MY CAPTION"/>', $cp);

        $g_get_posts = array((object) array('ID' => 1), (object) array('ID' => 2));
        $c = "!!bild1!! !!bild2 caption='MY CAPTION'!!";
        $a = array('attachment' => array(array('wp_filename' => 'file1.png', 'wp_id' => 1, 'template' => '<img alt="{CAPTION}"/>'), array('wp_filename' => 'file2.png', 'wp_id' => 2, 'template' => '<img alt="{CAPTION}"/>')), 'inline' => array(), 'related' => array());
        $cp = filter_ReplaceImagePlaceHolders($c, $a, $config, 1, "!!bild%!!", true);
        $this->assertEquals('<img alt=""/> <img alt="MY CAPTION"/>', $cp);
    }

    public function test_filter_linkify() {
        $this->assertEquals("", filter_linkify(""));
        $this->assertEquals("test", filter_linkify("test"));
        $this->assertEquals('<a href="http://www.example.com">www.example.com</a>', filter_linkify("http://www.example.com"));
        $this->assertEquals('<a href="http://www.example.com">www.example.com</a>', filter_linkify("www.example.com"));
        $this->assertEquals('<a href="http://www.example.com">www.example.com</a> <a href="http://www.example.com">www.example.com</a>', filter_linkify("www.example.com www.example.com"));
        $this->assertEquals('<a href="mailto:bob@example.com" >bob@example.com</a>', filter_linkify("bob@example.com"));
        $this->assertEquals("<img src='http://www.example.com'/>", filter_linkify("<img src='http://www.example.com'/>"));
        $this->assertEquals("<html><head><title></title></head><body><img src='http://www.example.com'/></body></html>", filter_linkify("<html><head><title></title></head><body><img src='http://www.example.com'/></body></html>"));
        $this->assertEquals('<html><head><title></title></head><body><img src="http://www.example.com"/><a href="http://www.example.com">www.example.com</a></body></html>', filter_linkify('<html><head><title></title></head><body><img src="http://www.example.com"/>www.example.com</body></html>'));
        $this->assertEquals("<img src='http://www.example.com'/>", filter_linkify("<img src='http://www.example.com'/>"));
        $this->assertEquals('<div><a href="http://www.example.com">www.example.com</a></div>', filter_linkify("<div>http://www.example.com</div>"));
        $this->assertEquals('devZing Final Logo_300x100.jpg <<a href="https://www.google.com/d/0B3Z6M1cu/view?usp=drive_web">www.google.com/d/0B3Z6M1cu/view?usp=drive_web</a>>', filter_linkify('devZing Final Logo_300x100.jpg <https://www.google.com/d/0B3Z6M1cu/view?usp=drive_web>'));
        $this->assertEquals('[pcustom name="ticket_link" value="http://holdmyticket.com/event/298287"]', filter_linkify('[pcustom name="ticket_link" value="http://holdmyticket.com/event/298287"]'));

        $this->assertEquals('<a href="http://saildocs.com/map?ll=36°59.92’N,007°50.45’W&z=1">saildocs.com/map?ll=36°59.92’N,007°50.45’W&amp;z=1</a>', filter_linkify("http://saildocs.com/map?ll=36°59.92’N,007°50.45’W&z=1"));

        $this->assertEquals('<div style="background-image: url(https://gallery.mailchimp.com/03fee00bc9ca.jpg); ">', filter_linkify('<div style="background-image: url(https://gallery.mailchimp.com/03fee00bc9ca.jpg); ">'));
        //$this->assertEquals('<a href="mailto:auditalk@audi.com" title="auditalk@audi.com">auditalk@audi.com</a>', filter_linkify('<a href="mailto:auditalk@audi.com" title="auditalk@audi.com">auditalk@audi.com</a>'));
    }

    public function test_tag_Date() {
        $pconfig = new PostieConfig();
        $config = $pconfig->defaults();

        $c = "";
        $this->assertEquals(null, tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "date:";
        $this->assertEquals(null, tag_Date($c, null, $config));
        $this->assertEquals("date:", $c);

        $c = "date: nothing";
        $this->assertEquals(null, tag_Date($c, null, $config));
        $this->assertEquals("date: nothing", $c);

        $c = "date: 1";
        $this->assertEquals(null, tag_Date($c, null, $config));
        $this->assertEquals("date: 1", $c);

        $c = "date: 12/31/2013";
        $this->assertEquals("2013-12-31", tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "date:12/31/2013";
        $this->assertEquals("2013-12-31", tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "Date: 12/31/2013";
        $this->assertEquals("2013-12-31", tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "DATE: 12/31/2013";
        $this->assertEquals("2013-12-31", tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "date: 31-12-2013";
        $this->assertEquals("2013-12-31", tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "date: 31.12.2013";
        $this->assertEquals("2013-12-31", tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "date: Dec 31, 2013";
        $this->assertEquals("2013-12-31", tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "date: 12/31/2013\nstuff";
        $this->assertEquals("2013-12-31", tag_Date($c, null, $config));
        $this->assertEquals("\nstuff", $c);

        $c = "date: Dec 31, 2013 14:22";
        $this->assertEquals("2013-12-31 14:22:00", tag_Date($c, null, $config));
        $this->assertEquals("", $c);

        $c = "stuff\n\ndate: Dec 31, 2013 14:22\n\nmorestuff";
        $this->assertEquals("2013-12-31 14:22:00", tag_Date($c, null, $config));
        $this->assertEquals("stuff\n\n\n\nmorestuff", $c);

        $c = "<p>stuff</p><p>date: Dec 31, 2013 14:22</p><p>morestuff</p>";
        $this->assertEquals("2013-12-31 14:22:00", tag_Date($c, null, $config));
        $this->assertEquals("<p>stuff</p><p></p><p>morestuff</p>", $c);

        $c = "<div>date: Dec 31, 2013 14:22<br></div>";
        $this->assertEquals("2013-12-31 14:22:00", tag_Date($c, null, $config));
        $this->assertEquals("<div><br></div>", $c);
    }

    function test_tag_Excerpt() {
        $pconfig = new PostieConfig();
        $config = $pconfig->defaults();

        $c = "";
        $e = tag_Excerpt($c, $config);
        $this->assertEquals("", $c);
        $this->assertEquals("", $e);

        $c = ":excerptstart stuff";
        $e = tag_Excerpt($c, $config);
        $this->assertEquals(":excerptstart stuff", $c);
        $this->assertEquals("", $e);

        $c = "stuff :excerptend";
        $e = tag_Excerpt($c, $config);
        $this->assertEquals("stuff :excerptend", $c);
        $this->assertEquals("", $e);

        $c = ":excerptstart stuff :excerptend";
        $e = tag_Excerpt($c, $config);
        $this->assertEquals("", $c);
        $this->assertEquals("stuff ", $e);
    }

    function test_tag_Status() {
        $pconfig = new PostieConfig();
        $config = $pconfig->defaults();
        $config['post_status'] = 'publish';

        $config['force_user_login'] = false;
        $c = "";
        $s = tag_Status($c, $config);
        $this->assertEquals("", $c);
        $this->assertEquals("publish", $s);

        $c = "status:private";
        $s = tag_Status($c, $config);
        $this->assertEquals("", $c);
        $this->assertEquals("private", $s);

        $c = "status: private";
        $s = tag_Status($c, $config);
        $this->assertEquals("", $c);
        $this->assertEquals("private", $s);

        $c = "status:blah";
        $s = tag_Status($c, $config);
        $this->assertEquals("status:blah", $c);
        $this->assertEquals("publish", $s);

        $c = "multi\nstatus: private\nline";
        $s = tag_Status($c, $config);
        $this->assertEquals("multi\n\nline", $c);
        $this->assertEquals("private", $s);

        global $g_current_user_can;
        $config['force_user_login'] = true;
        $g_current_user_can = array(false);

        $c = "multi\nstatus: publish\nline";
        $s = tag_Status($c, $config);
        $this->assertEquals('draft', $s);

        $g_current_user_can = array(false);
        $c = "multi\nstatus: future\nline";
        $s = tag_Status($c, $config);
        $this->assertEquals('draft', $s);

        $g_current_user_can = array(false);
        $c = "multi\nstatus: draft\nline";
        $s = tag_Status($c, $config);
        $this->assertEquals('draft', $s);
    }

    function test_getPostAuthorDetails() {
        global $g_user;
        $g_user->user_login = 'wayne';
        $config = config_GetDefaults();

        $e = array();
        $e['headers'] = array();
        $e['headers']['date'] = "Jan 1, 2013";
        $e['headers']['from'] = array();
        $e['headers']['from']['mailbox'] = 'wayne';
        $e['headers']['from']['host'] = "postieplugin.com";
        $e['text'] = '';
        $e['html'] = '';

        $m = new PostieMessage($e, $config);
        $r = $m->get_author_details();

        $this->assertEquals($r['author'], 'wayne');
        $this->assertEquals($r['email'], 'wayne@postieplugin.com');
    }

    function test_tagSubject() {
        $config = config_GetDefaults();

        $m = new PostieMessage(array(), $config);
        $m->content = '';
        $m->extract_subject_body();
        $this->assertEquals($m->subject, '');
        $this->assertEquals($m->content, '');

        $m = new PostieMessage(array(), $config);
        $m->content = '#simple subject#';
        $m->extract_subject_body();
        $this->assertEquals($m->subject, 'simple subject');
        $this->assertEquals($m->content, '');

        $m = new PostieMessage(array(), $config);
        $m->content = 'just content';
        $m->extract_subject_body();
        $this->assertEquals($m->subject, '');
        $this->assertEquals($m->content, "just content");

        $m = new PostieMessage(array(), $config);
        $m->content = '#just content';
        $m->extract_subject_body();
        $this->assertEquals($m->subject, '');
        $this->assertEquals($m->content, '#just content');

        $m = new PostieMessage(array(), $config);
        $m->content = "just content #with other stuff#";
        $m->extract_subject_body();
        $this->assertEquals($m->subject, '');
        $this->assertEquals($m->content, "just content #with other stuff#");

        $m = new PostieMessage(array(), $config);
        $m->content = "test #img1 caption='a funny caption'#";
        $m->extract_subject_body();
        $this->assertEquals($m->subject, '');
        $this->assertEquals($m->content, "test #img1 caption='a funny caption'#");

        $m = new PostieMessage(array(), $config);
        $m->content = "#img1 caption='a funny caption'#";
        $m->extract_subject_body();
        $this->assertEquals($m->subject, '');
        $this->assertEquals($m->content, "#img1 caption='a funny caption'#");
    }

}
