<?php
namespace GitAllie;

use gitHubController;

session_start();
include_once 'gitHubController.php';
include_once 'gitHubView.php';

/*//Make a form
$labelsUrl = API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME . '/issues';
$newIssue = [
    'title' => 'test-'];
$issue = new \stdClass();
$issue->title = 'testpp';
$labels = $gitHub->apiRequest($labelsUrl, $issue);*/
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swordfish Application Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<pre>
<?php

$gitHub = new gitHubController();
$base = new Base($gitHub->response);
print $base;
?></pre>
</body>
</html>
