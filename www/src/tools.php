<?php

function url_with_id($type, &$matches) {
  return preg_match('#^.*/' . $type . '/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})$#', $_SERVER['REQUEST_URI'], $matches);
}


function get_current_user_from_auth()
{
    $settings = parse_ini_file(__DIR__ . '/settings.ini', true);
    $authHeader = get_authorization_header();
    $url = $settings['api']['user_me_url'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $authHeader"
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        http_response_code(500);
        echo json_encode([
            'error' => 'cURL request failed',
            'curl_error' => $error,
            'curl_errno' => $errno
        ]);
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        http_response_code(401);
        echo json_encode(
            [
                'error' => $response,
                'code' => $httpCode
            ]
        );
        exit;
    }

    return json_decode($response, true); // return parsed user JSON
}

function get_authorization_header()
{
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            return $headers['Authorization'];
        }
    }
    http_response_code(401);
    echo json_encode(['error' => 'Missing Authorization header']);
    exit;
}
