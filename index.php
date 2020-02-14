<?php
namespace GitAllie;
session_start();

use gitHubController;
include_once 'gitHubController.php';
include_once 'gitHubView.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swordfish Application Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
$gitHub = new gitHubController();
$base = new Base($gitHub->response);
print $base;
print_r($gitHub->debug);