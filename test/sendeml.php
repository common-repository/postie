<?php

function sendEmlFile($filename, $to) {
    //define a clear line
    define("CRLF", "\r\n");

    //eml content to array.
    $file = file($filename, FILE_IGNORE_NEW_LINES);
    echo "read $filename\n";

    //var to store the headers
    $headers = "";
    $subject = "";

    //loop trough each line
    //the first part are the headers, until you reach a white line
    while (true) {
        //get the first line and remove it from the file
        $line = array_shift($file);
        //echo "line: $line\n";
        if (strlen(trim($line)) == 0) {
            echo "headers are complete\n";
            break;
        }

        //is it the To header
        if (substr(strtolower($line), 0, 3) == "to:") {
            echo "found To: $line\n";
            continue;
        }

        //Is it the subject header
        if (substr(strtolower($line), 0, 8) == "subject:") {
            echo "found Subject: $line\n";
            $subject = trim(substr($line, 8));
            continue;
        }

        //Is it the from header
        if (substr(strtolower($line), 0, 5) == "from:") {
            echo "found From: $line\n";
            continue;
        }

        if (substr(strtolower($line), 0, 13) == "mime-version:") {
            echo "found mime-version: $line\n";
            $headers .= $line . CRLF;
            continue;
        }

        if (substr(strtolower($line), 0, 13) == "content-type:") {
            echo "found Content-Type: $line\n";
            $headers .= $line . CRLF;
            continue;
        }

        //$headers .= $line . CRLF;
    }

    //implode the remaining content into the body and trim it, incase the headers where seperated with multiple white lines
    $body = trim(implode(CRLF, $file));

    //echo content for debugging

    echo "\n\nTo: $to\n";
    echo "Subject: $subject\n";
    echo "-----------------\n";
    echo "$headers\n";
    echo "-----------------\n";

    //echo $body;
    //send the email
    mail($to, $subject, $body, $headers . 'From: wayne@devzing.com');
    //mail($to, 'test sub', "test body", "From: wayne@email.devzing.com");
    //phpinfo();
}

//print_r($argv);
sendEmlFile($argv[1], 'test@postieplugin.com');
