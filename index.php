<?php
namespace GitAllie;

use gitHubController;

define('OAUTH2_CLIENT_ID', '2434d612549dff0bb4e0');
define('OAUTH2_CLIENT_SECRET', 'b815281ba8cd9cc295b4b6bc1ed375da8d50ad61');
define('APP_NAME', 'swordfish-assignment');
define('APP_NAME_LOCAL', 'swordhunter');
define('GITHUB_ACCOUNT', 'AllieTheDemonHunter');
define('DOMAIN', 'allie.co.za');
define('PROTOCOL', 'https'); //Enforcing this, sorry, not sorry.
define('AUTH_URL', 'https://github.com/login/oauth/authorize');
define('TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('API_URL', 'https://api.github.com');

session_start();
include_once 'gitHubController.php';
include_once 'gitHubView.php';

$gitHub = new gitHubController();
$base = new Base($gitHub->response);
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

print $base;

//Make a form
$labelsUrl = API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME . '/issues';
$newIssue = ['title' => 'test-', 'body' => 'more'];
$labels = $gitHub->apiRequest($labelsUrl, $newIssue,['Authorization: token '.$gitHub->session('access_token')]);
print_r($gitHub);
?>
</body>
</html>
