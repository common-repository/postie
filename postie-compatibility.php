<?php

/* this function is necessary for wildcard matching on non-posix systems */
if (!function_exists('fnmatch')) {

    function fnmatch($pattern, $string) {
        $pattern = strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.', '\[' => '[', '\]' => ']'));
        return preg_match('/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
    }

}

if (!function_exists('mb_str_replace')) {
    if (function_exists('mb_split')) {

        function mb_str_replace($search, $replace, $subject, &$count = 0) {
            if (!is_array($subject)) {
                // Normalize $search and $replace so they are both arrays of the same length
                $searches = is_array($search) ? array_values($search) : array($search);
                $replacements = array_pad(is_array($replace) ? array_values($replace) : array($replace), count($searches), '');

                foreach ($searches as $key => $search) {
                    $parts = mb_split(preg_quote($search), $subject);
                    $count += count($parts) - 1;
                    $subject = implode($replacements[$key], $parts);
                }
            } else {
                // Call mb_str_replace for each subject in array, recursively
                foreach ($subject as $key => $value) {
                    $subject[$key] = mb_str_replace($search, $replace, $value, $count);
                }
            }

            return $subject;
        }

    } else {

        function mb_str_replace($search, $replace, $subject, &$count = null) {
            return str_replace($search, $replace, $subject, $count);
        }

    }
}

if (!function_exists('boolval')) {

    function boolval($val) {
        return (bool) $val;
    }

}