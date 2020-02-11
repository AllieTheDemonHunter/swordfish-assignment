<?php
namespace GitAllie;

use gitHubController;

session_start();

include_once 'gitHubController.php';
include_once 'gitHubView.php';

$gitHub = new gitHubController();


if($gitHub) {

    //Make a form
    $labelsUrl = API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME . '/issues';
    $issue = new \stdClass();
    $issue->title = 'testpp';
    $labels = $gitHub->apiRequest($labelsUrl, $issue);
    print_r($labels);

    $open = $gitHub->apiRequest(API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME
        . '/issues?state=open'
    );

    $closed = $gitHub->apiRequest(API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME
        . '/issues?state=closed'
    );

    if(is_array($open) && !empty($open) && is_array($closed) && !empty($closed)) {
        $response = array_reverse(array_merge($closed, $open));
        $base = new Base($response);
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swordfish Application Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php

if(isset($base)) {
    print $base;
}


?>
</body>
</html>
