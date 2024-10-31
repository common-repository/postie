<?php
//http_response_code(403);
$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
header("$protocol 403 Forbidden");
$GLOBALS['http_response_code'] = $code;
?>
<html>
    <head>
        <title>Postie - Error</title>
    </head>
    <body>
        This URL is no longer supported for forcing an email check please update your cron job to 
        access http://&lt;mysite&gt;/?postie=get-mail
    </body>
</html>