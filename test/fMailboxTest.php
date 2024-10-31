<?php

declare(strict_types=1);

require_once 'bootstrap.php';

use PHPUnit\Framework\TestCase;

class fMailboxTest extends TestCase {

    public function testdecodeHeader() {
        $this->assertEquals('', fMailbox::decodeHeader(''));
        $this->assertEquals('test', fMailbox::decodeHeader('test'));

        //examples from https://www.ietf.org/rfc/rfc2047.txt
        $this->assertEquals('(a)', fMailbox::decodeHeader('(=?ISO-8859-1?Q?a?=)'));
        $this->assertEquals('(a b)', fMailbox::decodeHeader('(=?ISO-8859-1?Q?a?= b)'));
        $this->assertEquals('(ab)', fMailbox::decodeHeader('(=?ISO-8859-1?Q?a?= =?ISO-8859-1?Q?b?=)'));
        $this->assertEquals('(ab)', fMailbox::decodeHeader('(=?ISO-8859-1?Q?a?=  =?ISO-8859-1?Q?b?=)'));
        $this->assertEquals('(ab)', fMailbox::decodeHeader('(=?ISO-8859-1?Q?a?= ' . PHP_EOL . '=?ISO-8859-1?Q?b?=)'));
        $this->assertEquals('(a b)', fMailbox::decodeHeader('(=?ISO-8859-1?Q?a_b?=)'));
        $this->assertEquals('(a b)', fMailbox::decodeHeader('(=?ISO-8859-1?Q?a?= =?ISO-8859-2?Q?_b?=)'));

        $this->assertEquals('Sports 2017 NASCAR', fMailbox::decodeHeader('Sports =?utf-8?B?77u/MjAxNw==?= NASCAR'));
        $this->assertEquals('-News- -FB-Jörg Schüßler zu Gast bei Kreisdirektor Dr. Ansgar Hörster', fMailbox::decodeHeader('=?utf-8?Q?-News-_-FB-J=C3=B6rg_Sch=C3=BC=C3=9Fler_zu_Gast_bei_Kre?==?utf-8?Q?isdirektor_Dr=2E_Ansgar_H=C3=B6rster?='));
    }

    public function testparseHeaders() {
        $this->assertEquals(array(), fMailbox::parseHeaders(''));
        $h = fMailbox::parseHeaders("Subject: Google Alert - postie\n");
        $this->assertEquals(1, count($h));
        $h = fMailbox::parseHeaders("Subject: =?utf-8?Q?-News-_-FB-J=C3=B6rg_Sch=C3=BC=C3=9Fler_zu_Gast_bei_Kre?=\n =?utf-8?Q?isdirektor_Dr=2E_Ansgar_H=C3=B6rster?=");
        $this->assertEquals(1, count($h));
        $this->assertEquals('-News- -FB-Jörg Schüßler zu Gast bei Kreisdirektor Dr. Ansgar Hörster', $h['subject']);
    }

    public function testparseFromHeaders() {
        $h = fMailbox::parseHeaders('From: harupong <harupong@gmail.com>');
        $this->assertEquals(1, count($h));
        $this->assertEquals('gmail.com', $h['from']['host']);
        $this->assertEquals('harupong', $h['from']['mailbox']);
        $this->assertEquals('harupong', $h['from']['personal']);

        $h = fMailbox::parseHeaders('From: <hoepping@lg-fulda.de>');
        $this->assertEquals(1, count($h));
        $this->assertEquals('lg-fulda.de', $h['from']['host']);
        $this->assertEquals('hoepping', $h['from']['mailbox']);

        $h = fMailbox::parseHeaders('From: "Ingo Hoepping (LG)" <hoepping@lg-fulda.de>');
        $this->assertEquals(1, count($h));
        $this->assertEquals('lg-fulda.de', $h['from']['host']);
        $this->assertEquals('hoepping', $h['from']['mailbox']);
        $this->assertEquals('Ingo Hoepping (LG)', $h['from']['personal']);

        $h = fMailbox::parseHeaders('From: "Ingo Hoepping \(LG\)" <hoepping@lg-fulda.de>');
        $this->assertEquals(1, count($h));
        $this->assertEquals('lg-fulda.de', $h['from']['host']);
        $this->assertEquals('hoepping', $h['from']['mailbox']);
        $this->assertEquals('Ingo Hoepping \(LG\)', $h['from']['personal']);
    }

}
