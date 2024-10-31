<?php

abstract class pMailServer {

    abstract function deleteMessages($uids);

    abstract function countMessages();

    abstract function close();

    abstract function fetchMessageSource($uid);

    abstract function listMessages($limit = NULL, $page = NULL);

    protected function cleanDate($date) {
        $date = preg_replace('#\([^)]+\)#', ' ', trim($date));
        $date = preg_replace('#\s+#', ' ', $date);
        $date = preg_replace('#(\d+)-([a-z]+)-(\d{4})#i', '\1 \2 \3', $date);
        $date = preg_replace('#^[a-z]+\s*,\s*#i', '', trim($date));
        return trim($date);
    }

    /**
     * Joins parsed emails into a comma-delimited string
     *
     * @param array $emails  An array of emails split into personal, mailbox and host parts
     * @return string  An comma-delimited list of emails
     */
    protected function joinEmails($emails) {
        $output = '';
        foreach ($emails as $email) {
            if ($output) {
                $output .= ', ';
            }

            if (!isset($email[0])) {
                $email[0] = !empty($email['personal']) ? $email['personal'] : '';
                $email[2] = $email['mailbox'];
                $email[3] = !empty($email['host']) ? $email['host'] : '';
            }

            $address = $email[2];
            if (!empty($email[3])) {
                $address .= '@' . $email[3];
            }
            $output .= fEmail::combineNameEmail($email[0], $address);
        }
        return $output;
    }

    protected function decodeHeader($text) {
        $parts = preg_split('#(=\?[^\?]+\?[QB]\?[^\?]+\?=)#i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $part_with_encoding = array();
        $output = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (preg_match_all('#=\?([^\?]+)\?([QB])\?([^\?]+)\?=#i', $part, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    if (strtoupper($match[2]) == 'Q') {
                        $part_string = rawurldecode(strtr(
                                        $match[3], array(
                            '=' => '%',
                            '_' => ' '
                                        )
                        ));
                    } else {
                        $part_string = base64_decode($match[3]);
                    }
                    $lower_encoding = strtolower($match[1]);
                    $last_key = count($part_with_encoding) - 1;
                    if (isset($part_with_encoding[$last_key]) && $part_with_encoding[$last_key]['encoding'] == $lower_encoding) {
                        $part_with_encoding[$last_key]['string'] .= $part_string;
                    } else {
                        $part_with_encoding[] = array('encoding' => $lower_encoding, 'string' => $part_string);
                    }
                }
            } else {
                $last_key = count($part_with_encoding) - 1;
                if (isset($part_with_encoding[$last_key]) && $part_with_encoding[$last_key]['encoding'] == 'iso-8859-1') {
                    $part_with_encoding[$last_key]['string'] .= $part;
                } else {
                    $part_with_encoding[] = array('encoding' => 'iso-8859-1', 'string' => $part);
                }
            }
        }

        foreach ($part_with_encoding as $part) {
            $output .= $this->iconv($part['encoding'], 'UTF-8', $part['string']);
        }

        return $output;
    }

    /**
     * This works around a bug in MAMP 1.9.4+ and PHP 5.3 where iconv()
     * does not seem to properly assign the return value to a variable, but
     * does work when returning the value.
     *
     * @param string $in_charset   The incoming character encoding
     * @param string $out_charset  The outgoing character encoding
     * @param string $string       The string to convert
     * @return string  The converted string
     */
    protected function iconv($in_charset, $out_charset, $string) {
        return iconv($in_charset, "$out_charset//IGNORE", $string);
    }

}
