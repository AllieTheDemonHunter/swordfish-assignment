<?php
define('OAUTH2_CLIENT_ID', '2434d612549dff0bb4e0');
define('OAUTH2_CLIENT_SECRET', file_get_contents(getcwd() . '/../.no-secret-for-you'));
define('APP_NAME', 'swordfish-assignment');
define('APP_NAME_LOCAL', 'swordhunter');
define('GITHUB_ACCOUNT', 'AllieTheDemonHunter');
define('DOMAIN', 'allie.co.za');
define('PROTOCOL', 'https'); //Enforcing this, sorry, not sorry.
define('AUTH_URL', 'https://github.com/login/oauth/authorize');
define('TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('API_URL', 'https://api.github.com');

session_start();

class gitHub
{
    use gitHubTrait;
    public $base_url;

    function __construct()
    {
        //Making life easier.
        $this->base_url = 'Location: ' . PROTOCOL . '://' . DOMAIN . '/' . APP_NAME_LOCAL;
        if ($this->session('access_token')) {
            /**
             * Verbs
             * These should all be accessible via an API
             *  Which we're not using.
             *
             * GET /repos/:owner/:repo/issues/:issue_number
             * POST /repos/:owner/:repo/issues
             *
             */
            $response = $this->apiRequest(API_URL . '/issues');
            echo '<h3>Logged In</h3>';
            echo '<pre>';
            print_r($response);
            echo '</pre>';
        } else {
            echo '<h3>Not logged in</h3>';
            echo '<p><a href="?action=login">Log In</a></p>';
        }
        // When Github redirects the user back here, there will be a "code" and "state" parameter in the query string
        if ($this->get('code')) {
            // Verify the state matches our stored state
            if (!$this->get('state') || $_SESSION['state'] != $this->get('state')) {
                header('Location: ' . $this->base_url);
                exit(); // I don't like die().
            }
            // Exchange the auth code for a token
            $token = $this->apiRequest(TOKEN_URL, array(
                'client_id' => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
                'redirect_uri' => $this->base_url,
                'state' => $_SESSION['state'],
                'code' => $this->get('code'),
                'User-Agent' => APP_NAME //Need this for v.3.
            ));
            $_SESSION['access_token'] = $token->access_token;
            header('Location: ' . $this->base_url);
            exit(); // I don't like die().
        }
    }

    function apiRequest($url, $post = FALSE, $headers = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept: application/vnd.github.machine-man-preview'; //Nice to have
        if ($this->session('access_token'))
            $headers[] = 'Authorization: Bearer ' . $this->session('access_token');
        $headers[] = 'User-Agent:' . APP_NAME;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        return json_decode($response);
    }
}

trait gitHubTrait
{
    function get($key, $default = NULL)
    {
        if(isset($_GET)) {
            return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
        }
        return false;
    }

    function session($key, $default = NULL)
    {
        if(isset($_SESSION)) {
            return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
        }

        return false;
    }
}

// Printing the class' output for now
new Github();