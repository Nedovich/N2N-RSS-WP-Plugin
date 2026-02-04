<?php
/**
 * Auth Header Debugger
 * Call this URL from n8n instead of the API to see what headers arrive.
 */

header('Content-Type: application/json');

$headers = array();
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0 || strpos($key, 'CONTENT_') === 0 || strpos($key, 'REMOTE_') === 0) {
        $headers[$key] = $value;
    }
}

// Special check for Authorization which is often hidden
if (function_exists('apache_request_headers')) {
    $apache_headers = apache_request_headers();
    $headers['APACHE_HEADERS'] = $apache_headers;
}

echo json_encode([
    'status' => 'debug',
    'received_headers' => $headers,
    'php_auth_user' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'NOT_SET',
    'php_auth_pw'   => isset($_SERVER['PHP_AUTH_PW']) ? 'SET' : 'NOT_SET',
    'http_authorization_mapped' => isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : 'NOT_SET',
    'redirect_http_authorization' => isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : 'NOT_SET',
]);
