<?php

use LDAP\Result;

const CONTENT_TYPE_APPLICATION_JSON = "Content-Type: application/json";

$body = json_decode(file_get_contents('php://input'), true, 512, JSON_OBJECT_AS_ARRAY);

switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':

        if (isset($_GET['attachements']) && !empty($_GET['attachements'])) {
            get_file('attachements', $_GET['attachements']);
            break;
        }

        if (isset($_GET['profiles']) && !empty($_GET['profiles'])) {
            get_file('profiles', $_GET['profiles']);
            break;
        }

        if (isset($_GET['emojis-global'])) {
            if ($_GET['emojis-global'] == "all") {
                get_emojis_global_all();
                break;
            } else {
                get_file('emojis/global', $_GET['emojis-global']);
                break;
            }
        }

        if (isset($_GET['emojis']) && !empty($_GET['emojis'])) {
            // Direct file access
            if ($_GET['emojis'] == 'all') {
                get_emojis_all();
            } else {
                get_file('emojis', $_GET['emojis']);
            }
            break;
        }

        http_response_code(400);
        break;

    case 'POST':
        if (isset($_GET['attachements']) && !empty($_GET['attachements'])) {
            post_attachements_upload($_GET['attachements']);
            break;
        }

        if (isset($_GET['profiles']) && !empty($_GET['profiles'])) {
            post_profile_upload($_GET['profiles']);
            break;
        }

        if (isset($_GET['emojis']) && !empty($_GET['emojis'])) {
            if ($_GET['emojis'] == "bulk") {
                options_file_bulk('emojis', $body);
            } else {
                options_file('emojis', $_GET['emojis']);
            }
            break;
        }

        http_response_code(400);
        break;

    case 'DELETE':

        http_response_code(400);
        break;
    default:
        http_response_code(405);
        break;
}

exit;

function get_file($where, $name)
{
    $file = dirname(__FILE__) . "/data/$where/$name";

    if (!file_exists($file)) {
        if ($where == "profiles" && $name != "default-avatar") {
            get_file("profiles", "default-avatar");
            exit;
        }
        http_response_code(404);
        exit;
    }

    $type = mime_content_type($file);

    header("Content-Disposition: inline");
    header("Content-Type: $type");
    readfile($file);

    exit;
}

function get_emojis_all()
{
    $list = scandir(dirname(__FILE__) . "/data/emojis/");
    $result = array_values(array_diff($list, [".", "..", ".keep"]));

    header(CONTENT_TYPE_APPLICATION_JSON);
    echo json_encode($result);

    exit;
}

function get_emojis_global_all()
{
    $list = scandir(dirname(__FILE__) . "/data/emojis/global/");
    $list = array_values(array_diff($list, [".", "..", ".keep"]));

    header(CONTENT_TYPE_APPLICATION_JSON);
    echo json_encode($list);

    exit;
}

function options_file($where, $name)
{
    $file = dirname(__FILE__) . "/data/$where/$name";

    if (file_exists($file)) {
        http_response_code(200);
        exit;
    } else {
        http_response_code(204);
        exit;
    }
}

function options_file_bulk($where, $names)
{
    $result = [];

    foreach ($names as $name) {
        $file = dirname(__FILE__) . "/data/$where/$name";
        $result[$name] = file_exists($file);
    }

    http_response_code(200);
    header(CONTENT_TYPE_APPLICATION_JSON);
    echo json_encode($result);

    exit;
}

function post_profile_upload($id)
{
    if (preg_match('#^.*/profiles/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})$#', $_SERVER['REQUEST_URI'], $matches)) {
        $id = $matches[1];
        $user = get_current_user_from_auth();
        if ($id != $user['id'] && $user['type'] != 'ADMIN') {
            echo json_encode(['error' => 'You cannot edit this profile', 'user' => $user]);
            http_response_code(401);
            return;
        }

        require_once('src/file_upload.php');

        // Define storage path
        $uploadDir = __DIR__ . '/data/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // create directory if not exists
        }

        try {
            // Use the user ID as filename, no extension
            file_upload('file', $uploadDir . $id);
        } catch (FileUploadException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e]);
            exit;
        }

        // OK
        http_response_code(200);
        echo json_encode(['success' => true, 'path' => 'profiles/' . $id]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Bad request']);
    exit;
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

function post_attachements_upload()
{
    require_once('src/file_upload.php');

    // Ask Core for attachement id
    $id = "";

    // Define storage path
    $uploadDir = __DIR__ . '/data/attachements/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // create directory if not exists
    }

    try {
        file_upload('file', $uploadDir . $id);
    } catch (FileUploadException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e]);
        exit;
    }
}
