<?php

function get_file($where, $name)
{
    $file = __DIR__ . "/../data/$where/$name";

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