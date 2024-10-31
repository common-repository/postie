<?php

class pImapMailServer extends pMailServer {

    private $connection = null;

    public function __construct($connection) {
        $this->connection = $connection;
        $this->connection->open();
    }

    /**
     * Takes a response from an IMAP command and parses it into a
     * multi-dimensional array
     *
     * @param string  $text       The IMAP command response
     * @param boolean $top_level  If we are parsing the top level
     * @return array  The parsed representation of the response text
     */
    private function parseResponse($text, $top_level = FALSE) {
        $regex = '[\\\\\w.\[\]]+|"([^"\\\\]+|\\\\"|\\\\\\\\)*"|\((?:(?1)[ \t]*)*\)';

        if (preg_match('#\{(\d+)\}#', $text, $match)) {
            $regex = '\{' . $match[1] . '\}\r\n.{' . ($match[1]) . '}|' . $regex;
        }

        preg_match_all('#(' . $regex . ')#s', $text, $matches, PREG_SET_ORDER);
        $output = array();
        foreach ($matches as $match) {
            if (substr($match[0], 0, 1) == '"') {
                $output[] = str_replace('\\"', '"', substr($match[0], 1, -1));
            } elseif (substr($match[0], 0, 1) == '(') {
                $output[] = self::parseResponse(substr($match[0], 1, -1));
            } elseif (substr($match[0], 0, 1) == '{') {
                $output[] = preg_replace('#^[^\r]+\r\n#', '', $match[0]);
            } else {
                $output[] = $match[0];
            }
        }

        if ($top_level) {
            $new_output = array();
            $total_size = count($output);
            for ($i = 0; $i < $total_size; $i = $i + 2) {
                $new_output[strtolower($output[$i])] = $output[$i + 1];
            }
            $output = $new_output;
        }

        return $output;
    }

    function deleteMessages($uid) {
        settype($uid, 'array');
        $this->connection->write('UID STORE ' . join(',', $uid) . ' +FLAGS (\\Deleted)');
        $this->connection->write('EXPUNGE');
    }

    function countMessages() {
        $total_messages = 0;

        $response = $this->connection->write('STATUS "' . $this->connection->getMailbox() . '" (MESSAGES)');
        foreach ($response as $line) {
            if (preg_match('#^\s*\*\s+STATUS\s+"?[^"]+"?\s+\((.*)\)\s*$#', $line, $match)) {
                $details = $this->parseResponse($match[1], TRUE);
                $total_messages = $details['messages'];
            }
        }

        return (int) $total_messages;
    }

    function fetchMessageSource($uid) {

        $response = $this->connection->write('UID FETCH ' . $uid . ' (BODY[])');

        $j = 0;
        foreach ($response as $line) {
            //DebugEcho("fetchMessageSource: [$j] $line");
            if (1 === preg_match('#\{(\d+)\}$#', $response[$j], $match)) {
                //DebugEcho("fetchMessageSource: found " . $match[1]);
                break;
            }
            $j++;
        }
        //preg_match('#\{(\d+)\}$#', $response[0], $match);
        //DebugEcho("fetchMessageSource: reading {$match[1]} charaters");

        $message = '';
        foreach ($response as $i => $line) {
            if ($i <= $j) {
                continue;
            }
            if (trim($line) == ')') {
                //break;
            }
            if (strlen($message) + strlen($line) + 2 > $match[1]) {
                //DebugEcho("fetchMessageSource: last ($i) $line");
                $message .= substr($line . "\r\n", 0, $match[1] - strlen($message));
                break;
            } else {
                $message .= $line . "\r\n";
                //DebugEcho("fetchMessageSource: ($i-" . strlen($message) . ") $line");
            }
        }

        // Removes the extra trailing \r\n added above to the last line
        return substr($message, 0, -2);
    }

    function listMessages($limit = NULL, $page = NULL) {

        if (!$limit) {
            $start = 1;
            $end = '*';
        } else {
            if (!$page) {
                $page = 1;
            }
            $start = ($limit * ($page - 1)) + 1;
            $end = $start + $limit - 1;
        }

        $total_messages = 0;
        $response = $this->connection->write('STATUS "' . $this->connection->getMailbox() . '" (MESSAGES)');
        foreach ($response as $line) {
            if (preg_match('#^\s*\*\s+STATUS\s+"?[^"]+"?\s+\((.*)\)\s*$#', $line, $match)) {
                $details = self::parseResponse($match[1], TRUE);
                $total_messages = $details['messages'];
            }
        }

        if ($start > $total_messages) {
            return array();
        }

        if ($end > $total_messages) {
            $end = $total_messages;
        }

        $output = array();
        $mline = '';
        $response = $this->connection->write('FETCH ' . $start . ':' . $end . ' (UID INTERNALDATE RFC822.SIZE)');
        foreach ($response as $line) {
            //DebugEcho("listMessages: line: '$line'");
            $mline = $line;
            if (preg_match('#^\s*\*\s+(\d+)\s+FETCH\s+\((.*)\)\s*$#', $mline, $match)) {
                //DebugEcho("listMessages: found: $match[2]");
                $details = self::parseResponse($match[2], TRUE);
                $info = array();

                $info['uid'] = $details['uid'];
                $info['received'] = $this->cleanDate($details['internaldate']);
                $info['size'] = $details['rfc822.size'];

                $output[$info['uid']] = $info;
                $mline = '';
            }
        }
        return $output;
    }

    public function close() {

        if ($this->connection->isPersistant()) {
            $this->connection->write('LOGOUT');
        }

        $this->connection->close();
    }

}
