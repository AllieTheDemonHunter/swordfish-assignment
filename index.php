<?php
define('OAUTH2_CLIENT_ID', '2434d612549dff0bb4e0');
define('OAUTH2_CLIENT_SECRET', file_get_contents('.no-secret-for-you'));
// see https://developer.github.com/v3/#user-agent-required
define('APP_NAME', 'Swordhunter');

$authorizeURL = 'https://github.com/login/oauth/authorize';
$tokenURL = 'https://github.com/login/oauth/access_token';
$apiURLBase = 'https://api.github.com';
// or, in the case of https://education.github.com/pack, uncomment this:
//$apiURLBase = 'https://education.github.com/api';

session_start();

// Start the login process by sending the user to Github's authorization page
if (get('action') == 'login') {
    // Generate a random hash and store in the session for security
    $_SESSION['state'] = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']);
    unset($_SESSION['access_token']);
    $params = array(
        'client_id' => OAUTH2_CLIENT_ID,
        'redirect_uri' => 'https://allie.co.za/swordhunter/',
        'scope' => 'user',
        'state' => $_SESSION['state']
    );
    // Redirect the user to Github's authorization page
    header('Location: ' . $authorizeURL . '?' . http_build_query($params));
    die();
}

// When Github redirects the user back here, there will be a "code" and "state" parameter in the query string
if (get('code')) {
    // Verify the state matches our stored state
    if (!get('state') || $_SESSION['state'] != get('state')) {
        header('Location: ' . 'https://allie.co.za/swordhunter/');
        die();
    }
    // Exchange the auth code for a token
    $token = apiRequest($tokenURL, array(
        'client_id' => OAUTH2_CLIENT_ID,
        'client_secret' => OAUTH2_CLIENT_SECRET,
        'redirect_uri' => 'https://allie.co.za/swordhunter/',
        'state' => $_SESSION['state'],
        'User-Agent' => APP_NAME,
        'code' => get('code')
    ));
    $_SESSION['access_token'] = $token->access_token;
    header('Location: ' . 'https://allie.co.za/swordhunter/');
}

if (session('access_token')) {
    $response = apiRequest($apiURLBase . '/issues');
    echo '<h3>Logged In</h3>';
    echo '<pre>';
    print_r($response);
    echo '</pre>';
} else {
    echo '<h3>Not logged in</h3>';
    echo '<p><a href="?action=login">Log In</a></p>';
}

function apiRequest($url, $post = FALSE, $headers = array())
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if ($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    $headers[] = 'Accept: application/json';
    $headers[] = 'Accept: application/vnd.github.machine-man-preview'; //Nice to have
    if (session('access_token'))
        $headers[] = 'Authorization: Bearer ' . session('access_token');
    $headers[] = 'User-Agent:' . APP_NAME;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    return json_decode($response);
}

function get($key, $default = NULL)
{
    return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

function session($key, $default = NULL)
{
    return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}