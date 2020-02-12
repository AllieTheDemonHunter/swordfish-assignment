<?php
session_start();
include_once '../gitHubController.php';
include_once '../gitHubView.php';
use GitAllie\Base;

$gitHub = new gitHubController();

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
$base = new Base($gitHub->response);
print $base;
?></pre>
</body>
</html>
