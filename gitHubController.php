<?php

namespace GitAllie;

use stdClass;

define('AUTH_URL', 'https://github.com/login/oauth/authorize');
define('TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('API_URL', 'https://api.github.com');

//Repo on github.com
define('REPO_NAME', 'swordfish-assignment');
define('GITHUB_ACCOUNT', 'AllieTheDemonHunter');
define('HOME', trim(`echo ~`)); // *nix

// Get secrets
if ($_SERVER['HTTP_HOST'] === 'localhost:8080') {
    $file_name = HOME . '/safe-fish/.env.local.json'; //At home
} else {
    $file_name = HOME . '/safe-fish/.env.allie.co.za.json';
}

if (file_exists($file_name)) {
    $secrets = file_get_contents($file_name);

    if (is_string($secrets) && strlen($secrets) > 0) {
        $secrets_object = json_decode($secrets);
        foreach ($secrets_object as $constant_name => $value) {
            define($constant_name, $value);
        }
    }
}

// VERB or actions.
define('ENDPOINT', API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . REPO_NAME);

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Class gitHub
 */
class gitHubController
{
    use gitHubTrait;

    /**
     * @var string
     */

    public $response = [];
    /**
     * @var array
     */
    public $debug;

    /**
     * gitHub constructor.
     */
    function __construct()
    {
        if ($this->get('login')) {
            // Send the user to Github's authorization page

            // Generate a random hash and store in the session for security
            $_state = $_SESSION['state'] = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']);

            // Freshen up
            unset($_SESSION['access_token']);

            // Sending this to get logged in.
            $params = array(
                'client_id' => OAUTH2_CLIENT_ID,
                'login' => GITHUB_ACCOUNT, //personal convenience
                'state' => $_state,
                'scope' => 'repo',
                'redirect_uri' => REDIRECT_URI,
            );

            // Redirect the user to Github's authorization page
            header('Location: ' . AUTH_URL . '?' . http_build_query($params));
            exit();
        }

        if (is_string($this->session('access_token'))) {
            echo '<h3>Logged In</h3>';
        } elseif ($this->get('code') && isset($_SESSION['state'])) {
            // When Github redirects the user back here.
            // Verify the state matches our stored state
            if (!$this->get('state') || $_SESSION['state'] != $this->get('state')) {
                unset($_SESSION['state']);
                exit('Verify the state matches our stored state === FALSE');
            }

            // Exchange the auth code for a token
            $post_for_auth = array(
                'client_id' => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
                'code' => $this->get('code'),
                'state' => $this->get('state'),
            );

            $token = $this->apiRequest(TOKEN_URL, $post_for_auth);

            if (!empty($token)) {
                $_SESSION['access_token'] = $token->access_token;
            }
            //header('Location: ' . REDIRECT_URI);
        } else {
            //All clauses have exit().
            echo '<h3>Not logged in</h3>';
            echo '<p><a href="?login=1">Log In</a></p>';
            exit();
        }
    }

    /**
     * @param $url
     * @param bool $post
     * @param array $headers
     * @return mixed
     */
    function apiRequest($url, $post = FALSE, $headers = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        $_token = $this->session('access_token');
        if (isset($_token) && is_string($this->session('access_token'))) {
            $headers[] = 'Authorization: token ' . $this->session('access_token');
        }

        $headers[] = 'User-Agent: ' . OAUTH_APP_NAME;
        $headers[] = 'Accept: application/json, application/vnd.github.v3+json, application/vnd.github.machine-man-preview, text/html';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($post));
            $this->response = $this->gitHubResponseHandler(curl_exec($ch));
        } else {
            $this->response = $this->gitHubResponseHandler(curl_exec($ch));
        }

        $this->debug[] = curl_getinfo($ch);
        return json_decode($this->response);
    }
}

/**
 * Trait gitHubTrait
 */
trait gitHubTrait
{
    /**
     * @param $key
     * @param null $default
     * @return bool|mixed|null
     */
    function get($key, $default = NULL)
    {
        if (isset($_GET)) {
            return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
        }

        return false;
    }

    function gitHubResponseHandler($dataFromGitHub)
    {
        return $this->response = $dataFromGitHub;
    }

    /**
     * @param $key
     * @param null $default
     * @return bool|mixed|null
     */
    function session($key, $default = NULL)
    {
        if (isset($_SESSION)) {
            return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
        }

        return false;
    }
}

class gitHubCommander extends gitHubController
{
    public $labels;
    /**
     * @var mixed
     */
    public $issues;

    public function __construct()
    {
        parent::__construct();
        $this->issues();
        $this->labels();
    }

    public function issues($which = 'open')
    {
        return $this->issues = $this->apiRequest(ENDPOINT . '/issues?state=' . $which);
    }

    public function labels()
    {
        return $this->labels = $this->apiRequest(ENDPOINT . '/labels');
    }

    public function set_issue()
    {
        if (!$_POST) {
            exit;
        }

        //Create a new issue
        $new = new stdClass();
        $new->title = $_POST;
        $new->description = $_POST;
        $new->labels = $_POST;
        $this->apiRequest(ENDPOINT . '/issues', json_encode($new));
    }
}