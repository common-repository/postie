<?php

class pPop3MailServer extends pMailServer {

    private $connection = null;

    public function __construct($connection) {
        $this->connection = $connection;
        $this->connection->open();
    }

    public function countMessages() {
        $total_messages = 0;

        $response = $this->connection->write('STAT', '#^\+OK\s+(\d+)\s+#');
        DebugEcho("pop count: " . $response[count($response) - 1]);
        preg_match('#^\+OK\s+(\d+)\s+#', $response[count($response) - 1], $match);
        $total_messages = $match[1];

        return (int) $total_messages;
    }

    public function deleteMessages($uid) {
        settype($uid, 'array');
        foreach ($uid as $id) {
            $this->connection->write('DELE ' . $id, 1);
        }
    }

    public function fetchMessageSource($uid) {
        $response = $this->connection->write('RETR ' . $uid);
        array_shift($response);
        $response = join("\r\n", $response);

        return $response;
    }

    public function listMessages($limit = NULL, $page = NULL) {

        if (!$limit) {
            $start = 1;
            $end = NULL;
        } else {
            if (!$page) {
                $page = 1;
            }
            $start = ($limit * ($page - 1)) + 1;
            $end = $start + $limit - 1;
        }

        $total_messages = $this->countMessages();

        if ($start > $total_messages) {
            return array();
        }

        if ($end === NULL || $end > $total_messages) {
            $end = $total_messages;
        }

        $sizes = array();
        $response = $this->connection->write('LIST');
        array_shift($response);
        foreach ($response as $line) {
            preg_match('#^(\d+)\s+(\d+)$#', $line, $match);
            $sizes[$match[1]] = $match[2];
        }

        $output = array();
        for ($i = $start; $i <= $end; $i++) {
            $response = $this->connection->write('TOP ' . $i . ' 1');
            array_shift($response);
            $value = array_pop($response);
            // Some servers add an extra blank line after the 1 requested line
            if (trim($value) == '') {
                array_pop($response);
            }

            $response = trim(join("\r\n", $response));
            $headers = fMailbox::parseHeaders($response);

            $received = '';
            if (array_key_exists('received', $headers) && count($headers['received']) > 0) {
                $received = $headers['received'][0];
            } else {
                DebugEcho('pPop3MailServer::listMessages missing "received" header');
            }

            $date = '';
            if (array_key_exists('date', $headers)) {
                $date = $headers['date'];
            } else {
                DebugEcho('pPop3MailServer::listMessages missing "date" header');
            }

            $subject = '';
            if (array_key_exists('subject', $headers)) {
                $subject = $headers['subject'];
            } else {
                DebugEcho('pPop3MailServer::listMessages missing "subject" header');
            }

            $output[$i] = array(
                'uid' => $i,
                'received' => self::cleanDate(preg_replace('#^.*;\s*([^;]+)$#', '\1', $received)),
                'size' => $sizes[$i],
                'date' => $date,
                'from' => self::joinEmails(array($headers['from'])),
                'subject' => $subject
            );
            if (isset($headers['message-id'])) {
                $output[$i]['message_id'] = $headers['message-id'];
            } else {
                DebugEcho('pPop3MailServer::listMessages missing "message-id" header');
            }
            
            if (isset($headers['to'])) {
                $output[$i]['to'] = self::joinEmails($headers['to']);
            } else {
                DebugEcho('pPop3MailServer::listMessages missing "to" header');
            }
            
            if (isset($headers['in-reply-to'])) {
                $output[$i]['in_reply_to'] = $headers['in-reply-to'];
            } else {
                DebugEcho('pPop3MailServer::listMessages missing "in-reply-to" header');
            }
        }
        return $output;
    }

    public function close() {
        if ($this->connection->isPersistant()) {
            $this->connection->write('QUIT', 1);
        }
        $this->connection->close();
    }

}
