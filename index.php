<?php
namespace GitAllie;

use gitHubController;

session_start();
include_once 'gitHubController.php';
include_once 'gitHubView.php';
$gitHub = new gitHubController();
$base = new Base($gitHub->response);
print $base;