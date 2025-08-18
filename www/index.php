<?php

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

        if (isset($_GET['emojis']) && !empty($_GET['emojis'])) {
            get_file('emojis', $_GET['emojis']);
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
