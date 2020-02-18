<?php
namespace GitAllie;
session_start();

include_once 'gitHubController.php';
include_once 'gitHubView.php';
$gitHub = new gitHubCommander();
$gitHub->issues();
$base = new Base($gitHub->response);
$gitHub->set_issue();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swordfish Application Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php

print $base;
print_r($gitHub);