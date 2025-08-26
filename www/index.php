<?php

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

        if (isset($_GET['emojis'])) {
            // Direct file access
            if (!empty($_GET['emojis'])) {
                get_file('emojis', $_GET['emojis']);
            }

            // List of available emojis
            if (isset($_GET['list'])) {
                get_emojis_list();
                break;
            }

            break;
        }

        http_response_code(400);
        break;

    case 'OPTIONS':
        if (isset($_GET['attachements']) && !empty($_GET['attachements'])) {
            options_file('attachements', $_GET['attachements']);
            break;
        }

        if (isset($_GET['profiles']) && !empty($_GET['profiles'])) {
            if ($_GET['profiles'] == "bulk") {
                options_file_bulk('profiles', $body);
            } else {
                options_file('profiles', $_GET['profiles']);
            }
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

    case 'POST':

        http_response_code(400);
        break;

    case 'DELETE':

        http_response_code(400);
        break;

    default:
        http_response_code(405);
        break;
}

exit();

function get_file($where, $name)
{
    $file = dirname(__FILE__) . "/data/$where/$name";

    if (!file_exists($file)) {
        http_response_code(404);
        exit();
    }

    $type = mime_content_type($file);

    header("Content-Disposition: inline");
    header("Content-Type: $type");
    readfile($file);

    exit();
}

function get_emojis_list()
{
    $list = scandir(dirname(__FILE__) . "/data/emojis/");
    $result = array_values(array_diff($list, [".", "..", "placeholder"]));

    header("Content-Type: application/json");
    echo json_encode($result);

    exit();
}

function options_file($where, $name)
{
    $file = dirname(__FILE__) . "/data/$where/$name";

    if (file_exists($file)) {
        http_response_code(200);
        exit();
    } else {
        http_response_code(204);
        exit();
    }
}

function options_file_bulk($where, $names)
{
    $result = [];

    foreach ($names as $name) {
        $file = dirname(__FILE__) . "/data/$where/$name";
        $array[$name] = file_exists($file);
        //array_push($result, ["id" => $name, "fileExist" => file_exists($file)]);
    }

    http_response_code(200);
    echo json_encode($result);

    exit();
}
