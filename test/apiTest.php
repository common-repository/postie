<?php

require_once 'bootstrap.php';

use PHPUnit\Framework\TestCase;

/*
 * This doesn't actually test the function, rather it makes sure the API methods don't break
 */

class apiTest extends TestCase {

    public function testRemoveExtraCharactersInEmailAddress() {
        RemoveExtraCharactersInEmailAddress('help@postieplugin.com');
        $this->assertTrue(true);
    }

    public function testEchoError() {
        EchoError('test');
        $this->assertTrue(true);
    }

    public function testDebugDump() {
        DebugDump(null);
        $this->assertTrue(true);
    }

    public function testDebugEcho() {
        DebugEcho('');
        $this->assertTrue(true);
    }

    public function testpostie_config_read() {
        postie_config_read();
        $this->assertTrue(true);
    }

}
