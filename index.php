<?php
require_once('MistAPI.php');

// Require your api here - MistAPI uses reflection to instantiate your class.
require_once("TemplateAPI.php");

// Handle origin
if (isset($_REQUEST["_url"])) $url = $_REQUEST["_url"]; else $url = "/";
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

// Get class that extends our api class
$apiClass = null;
foreach (get_declared_classes() as $class)
{
    if (is_subclass_of($class, 'MistAPI')) $apiClass = $class;
}
if ($apiClass == null) {
    echo json_encode(['error' => 'no_api_defined']);
    die();
}

try {
    $API = new $apiClass($url);
    echo $API->ProcessAPI();
} catch (Exception $e) {
    switch ($e->getMessage())
    {
        default:
            http_response_code(400);
            break;

        case "bad-key":
            http_response_code(401);
            break;
    }

    echo json_encode(Array("error" => $e->getMessage()));
}