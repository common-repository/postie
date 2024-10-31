<?php

class pSocketConnection extends pConnection {

    private $socket = null;
    private $command_num = 1;

    public function __construct($type, $host, $username, $password, $port = NULL, $secure = FALSE, $timeout = NULL) {
        parent::__construct($type, $host, $username, $password, $port, $secure, $timeout);
        DebugEcho("Connecting via Socket");
    }

    public function __destruct() {
        $this->close();
    }

    public function close() {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    public function open() {
        if ($this->socket) {
            return;
        }

        $connstr = ($this->secure ? 'tls://' : '') . "$this->host:$this->port";
        DebugEcho("Socket: $connstr");
        $error_number = 0;
        $error_string = '';
        $context = stream_context_create();
        stream_context_set_option($context, "ssl", "allow_self_signed", true);
        stream_context_set_option($context, "ssl", "verify_peer", false);
        stream_context_set_option($context, "ssl", "verify_peer_name", false);
        $this->socket = stream_socket_client($connstr, $error_number, $error_string, $this->timeout, STREAM_CLIENT_CONNECT, $context);
        DebugEcho("Socket error: $error_number - $error_string ($this->socket)");

        if (!$this->socket) {
            throw new fConnectivityException('There was an error connecting to the server ' . $error_string);
        }

        stream_set_timeout($this->socket, $this->timeout);

        if ($this->type == 'imap') {
            DebugEcho("Socket: IMAP");
            if ($this->secure && extension_loaded('openssl')) {
                $response = $this->write('CAPABILITY');
                if (preg_match('#\bstarttls\b#i', $response[0])) {
                    $this->write('STARTTLS');
                    do {
                        if (isset($res)) {
                            sleep(0.1);
                        }
                        $res = stream_socket_enable_crypto($this->socket, TRUE, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                    } while ($res === 0);
                }
            }
            $response = $this->write('LOGIN ' . $this->username . ' "' . $this->password . '"');
            if (!$response || !preg_match('#^[^ ]+\s+OK#', $response[count($response) - 1])) {
                throw new fValidationException('IMAP - Could not connect to %1$s server %2$s on port %3$s. %4$s', strtoupper($this->type), $this->host, $this->port, print_r($response, true));
            }
            $this->write('SELECT "' . $this->mailbox . '"');
        } elseif ($this->type == 'pop3') {
            DebugEcho("Socket: POP3");
            $response = $this->read(1);
            if (isset($response[0])) {
                if ($response[0][0] == '-') {
                    throw new fConnectivityException('There was an error connecting to the POP3 server %1$s on port %2$s', $this->host, $this->port);
                }
                preg_match('#<[^@]+@[^>]+>#', $response[0], $match);
            }

            if ($this->secure && extension_loaded('openssl')) {
                DebugEcho("Socket: attempting a secure connection");
                $response = $this->write('STLS', 1);
                if ($response[0][0] == '+') {
                    $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;

                    if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                        $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                        $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
                    }
                    do {
                        if (isset($res)) {
                            sleep(0.1);
                        }
                        $res = stream_socket_enable_crypto($this->socket, TRUE, $crypto_method);
                    } while ($res === 0);
                    if ($res === FALSE) {
                        throw new fConnectivityException('Error establishing secure connection');
                    }
                }
            }

            $authenticated = FALSE;
            // commented out since some mail servers disconnect if APOP is not supported
//            if (isset($match[0])) {
//                $response = $this->write('APOP ' . $this->username . ' ' . md5($match[0] . $this->password), 1);
//                if (isset($response[0]) && $response[0][0] == '+') {
//                    $authenticated = TRUE;
//                }
//            }

            if (!$authenticated) {
                $response = $this->write('USER ' . $this->username, 1);
                if ($response[0][0] == '+') {
                    $response = $this->write('PASS ' . $this->password, 1);
                    if (isset($response[0][0]) && $response[0][0] == '+') {
                        $authenticated = TRUE;
                    }
                }
            }

            if (!$authenticated) {
                throw new fValidationException('POP3 - Could not connect to %1$s server %2$s on port %3$s. %4$s', strtoupper($this->type), $this->host, $this->port, print_r($response, true));
            }
        }
    }

    public function write($command, $expected = null) {

        if ($this->type == 'imap') {
            $identifier = 'A' . str_pad($this->command_num++, 4, '0', STR_PAD_LEFT);
            $command = $identifier . ' ' . $command;
        }

        if (substr($command, -2) != "\r\n") {
            $command .= "\r\n";
        }

        if ((strpos($command, ' LOGIN') === false) & (strpos($command, 'APOP ') === false ) & (strpos($command, 'PASS ') === false )) {
            DebugEcho("socket write: " . trim($command));
        } else {
            DebugEcho("socket write: (login) LOGIN/APOP/PASS");
        }

        $res = fwrite($this->socket, $command);
        if ($res == 1) {
            sleep(3);
            $res = fwrite($this->socket, $command);
        }

        if ($res === FALSE || $res === 0) {
            DebugEcho("Socket: error");
            DebugDump(error_get_last());
            throw new fConnectivityException('Unable to write data to %1$s server %2$s on port %3$s', strtoupper($this->type), $this->host, $this->port);
        }

        if ($this->type == 'imap') {
            return $this->read('#^' . $identifier . '#');
        } elseif ($this->type == 'pop3') {
            return $this->read($expected);
        }
    }

    public function read($expect = NULL) {

        $read = array($this->socket);
        $write = NULL;
        $except = NULL;
        $response = array();

        // PHP 5.2.0 to 5.2.5 has a bug on amd64 linux where stream_select()
        // fails, so we have to fake it - http://bugs.php.net/bug.php?id=42682
        static $broken_select = NULL;
        if ($broken_select === NULL) {
            //$broken_select = strpos(php_uname('m'), '64') !== FALSE && fCore::checkVersion('5.2.0') && !fCore::checkVersion('5.2.6');
        }

        // Fixes an issue with stream_select throwing a warning on PHP 5.3 on Windows
        if (fCore::checkOS('windows') && fCore::checkVersion('5.3.0')) {
            $select = @stream_select($read, $write, $except, $this->timeout);
        } elseif ($broken_select) {
            $broken_select_buffer = NULL;
            $start_time = microtime(TRUE);
            $i = 0;
            do {
                if ($i) {
                    usleep(50000);
                }
                $char = fgetc($this->socket);
                if ($char != "\x00" && $char !== FALSE) {
                    $broken_select_buffer = $char;
                }
                $i++;
            } while ($broken_select_buffer === NULL && microtime(TRUE) - $start_time < $this->timeout);
            $select = $broken_select_buffer !== NULL;
        } else {
            $select = stream_select($read, $write, $except, $this->timeout);
        }

        if ($select) {
            while (!feof($this->socket)) {
                $line = fgets($this->socket);
                if ($line === FALSE) {
                    break;
                }
                $line = substr($line, 0, -2);

                // When we fake select, we have to handle what we've retrieved
                if ($broken_select && $broken_select_buffer !== NULL) {
                    $line = $broken_select_buffer . $line;
                    $broken_select_buffer = NULL;
                }

                $response[] = $line;

                // Automatically stop at the termination octet or a bad response
                if ($this->type == 'pop3' && ($line == '.' || (count($response) == 1 && $response[0][0] == '-'))) {
                    break;
                }

                if ($expect !== NULL) {
                    $matched_number = is_int($expect) && sizeof($response) == $expect;
                    $matched_regex = is_string($expect) && preg_match($expect, $line);
                    if ($matched_number || $matched_regex) {
                        break;
                    }
                }
            }
        }
        //fCore::debug("Received:\n" . join("\r\n", $response), true);
        DebugEcho("socket read (" . count($response) . ') :' . $response[0]);
        //DebugDump($response);

        if ($this->type == 'pop3') {
            // Remove the termination octet
            if ($response && $response[sizeof($response) - 1] == '.') {
                $response = array_slice($response, 0, -1);
            }
            // Remove byte-stuffing
            $lines = count($response);
            for ($i = 0; $i < $lines; $i++) {
                if (strlen($response[$i]) && $response[$i][0] == '.') {
                    $response[$i] = substr($response[$i], 1);
                }
            }
        }

        return $response;
    }

    public function isPersistant() {
        return true;
    }

}
