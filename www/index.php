<?php
require_once 'src/files.php';
require_once 'src/tools.php';

const CONTENT_TYPE_APPLICATION_JSON = "Content-Type: application/json";

$body = json_decode(file_get_contents('php://input'), true, 512, JSON_OBJECT_AS_ARRAY);

switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':

        if (isset($_GET['attachements']) && !empty($_GET['attachements'])) {
            rvc_read_file('attachements', $_GET['attachements']);
            break;
        }

        if (isset($_GET['profiles']) && !empty($_GET['profiles'])) {
            rvc_read_file('profiles', $_GET['profiles']);
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


function post_profile_upload($id)
{
    if (url_with_id($_SERVER['REQUEST_URI'], $matches)) {
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
