<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swordfish Application Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
include_once 'Github.php';
$base = new \GitAllie\Base($response);
print $base;

//Make a form
$labelsUrl = API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME . '/issues';
print '<pre>' . $labelsUrl . '</pre>';
$newIssue = ['title' => 'test-', 'body' => 'more'];
$labels = $gitHub->apiRequest($labelsUrl, $newIssue);
print_r($labels);
?>
</body>
</html>