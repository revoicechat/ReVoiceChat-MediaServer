<?php
require_once 'src/files.php';

const CONTENT_TYPE_APPLICATION_JSON = "Content-Type: application/json";

$body = json_decode(file_get_contents('php://input'), true, 512, JSON_OBJECT_AS_ARRAY);

switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
        // Emojis Any
        if (isset($_GET['any']) && isset($_GET['emoji']) && !empty($_GET['emoji'])) {
            get_emoji_any($_GET['emoji']);
            break;
        }

        // Emojis global
        if (isset($_GET['global'])) {
            if (isset($_GET['all'])) {
                get_emojis_all('global');
                break;
            }

            if (isset($_GET['emoji']) && !empty($_GET['emoji'])) {
                rvc_read_file('emojis/global', $_GET['emoji']);
                break;
            }
        }

        // Emojis server
        if (isset($_GET['server']) && !empty($_GET['server'])) {
            if (isset($_GET['all'])) {
                get_emojis_all($_GET['server']);
                break;
            }

            if (isset($_GET['emoji']) && !empty($_GET['emoji'])) {
                rvc_read_file('emojis/server', $_GET['emoji']);
                break;
            }
        }

        http_response_code(400);
        break;

    case 'POST':
        if (isset($_GET['emoji']) && !empty($_GET['emoji'])) {
            rvc_file_exists('emojis', $_GET['emojis']);
            break;
        }

        if (isset($_GET['upload'])) {
            post_emoji_upload('emojis', $body);
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

function get_emoji_any($name)
{
    $rootDir = __DIR__ . "/data/emojis";

    $dirContent = scandir($rootDir);
    foreach ($dirContent as $element) {
        if(!is_dir("$rootDir/$element")){
            continue;
        }

        if($element == "." || $element == ".."){
            continue;
        }

        if(is_file("$rootDir/$element/$name")){
            rvc_read_file("emojis/$element", $name);
            exit;
        }
    }

    http_response_code(404);
    exit;
}

function get_emojis_all($where)
{
    $workingDirectory = __DIR__ . "/data/emojis/$where/";
    if (!is_dir($workingDirectory)) {
        http_response_code(404);
        exit;
    }

    $list = scandir($workingDirectory);
    $list = array_values(array_diff($list, [".", "..", ".keep"]));

    header(CONTENT_TYPE_APPLICATION_JSON);
    echo json_encode($list);

    exit;
}

function post_emoji_upload()
{
    require_once('src/file_upload.php');

    // Ask Core for attachement id
    $id = "";

    // Define storage path
    $uploadDir = __DIR__ . "/data/emojis/$serverId/";
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
