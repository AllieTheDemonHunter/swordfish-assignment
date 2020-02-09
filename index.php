<?php
namespace GitAllie;

use gitHubController;

session_start();
include_once 'gitHubController.php';
include_once 'gitHubView.php';

$gitHub = new gitHubController();
$base = new Base($gitHub->response);

//Make a form
$labelsUrl = API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME . '/issues';
$newIssue = [
    'title' => 'test-'];
$labels = $gitHub->apiRequest($labelsUrl, $newIssue);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swordfish Application Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php

print_r($labels);
print $base;
?>
</body>
</html>
