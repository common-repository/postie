<?php

abstract class pConnection {

    protected $username;
    protected $host;
    protected $password;
    protected $port;
    protected $secure;
    protected $timeout;
    protected $mailbox;
    protected $type;

    public function __construct($type, $host, $username, $password, $port, $secure = FALSE, $timeout = NULL) {

        $valid_types = array('imap', 'pop3');
        if (!in_array($type, $valid_types)) {
            throw new fProgrammerException('The mailbox type specified, %1$s, in invalid. Must be one of: %2$s.', $type, join(', ', $valid_types));
        }

        if ($secure && !extension_loaded('openssl')) {
            throw new fEnvironmentException('A secure connection was requested, but the %s extension is not installed', 'openssl');
        }

        if ($timeout === NULL) {
            $timeout = ini_get('default_socket_timeout');
        }

        $this->type = $type;
        $this->host = $host;
        $this->username = $username;
        if (($i = strpos($username, '/')) !== false) {
            $this->username = substr($username, 0, $i);
            $this->mailbox = substr($username, $i + 1);
        } else {
            $this->username = $username;
            $this->mailbox = 'INBOX';
        }
        //DebugEcho('USERNAME: ' . $this->username);
        DebugEcho('pConnection: mailbox: ' . $this->mailbox);

        $this->password = $password;
        $this->port = $port;
        $this->secure = $secure;
        $this->timeout = $timeout;
    }

    abstract function read($expect = NULL);

    abstract function write($command, $expected = null);

    abstract function close();

    abstract function open();

    abstract function isPersistant();

    public function getMailbox() {
        return $this->mailbox;
    }

}
