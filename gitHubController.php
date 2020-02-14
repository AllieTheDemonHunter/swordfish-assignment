<?php
define('OAUTH2_CLIENT_ID', '53af175ce5d46b80f33a');
define('OAUTH2_CLIENT_SECRET', '9f906210cbce17ccc66cb97050e3c7d22bdfa4ac');
define('REDIRECT_URI', 'http://allie.co.za/swordhunter');
define('OAUTH_APP_NAME', 'swordhunter');

define('GITHUB_ACCOUNT', 'SwordfishCode');
define('REPO_NAME', 'GitIntegration');


define('PROTOCOL', 'https'); //Enforcing this, sorry, not sorry.
define('AUTH_URL', 'https://github.com/login/oauth/authorize');
define('TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('API_URL', 'https://api.github.com');
define('ENDPOINT', API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . REPO_NAME . '/issues');

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

    public $response;
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
                'redirect_uri' => 'http://localhost:8080',
            );

            // Redirect the user to Github's authorization page
            header('Location: ' . AUTH_URL . '?' . http_build_query($params));
            exit();
        }

        if (is_string($this->session('access_token'))) {
            echo '<h3>Logged In</h3>';
            $new = new stdClass();
            $new->title = 'test--o' . time();
            $this->debug($this->apiRequest(ENDPOINT, json_encode($new)));
            $open = $this->apiRequest(ENDPOINT . '?state=open');
            $closed = $this->apiRequest(ENDPOINT . '?state=open');
            $this->response = array_reverse(array_merge($open, $closed));
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
            header('Location: http://localhost:8080');
        }
        else {
            //All clauses have exit().
            echo '<h3>Not logged in</h3>';
            echo '<p><a href="?login=1">Log In</a></p>';
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

        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($post));
        }

        $_token = $this->session('access_token');
        if (isset($_token) && is_string($this->session('access_token'))) {
            $headers[] = 'Authorization: token ' . $this->session('access_token');
        }

        $headers[] = 'User-Agent: ' . OAUTH_APP_NAME;
        $headers[] = 'Accept: application/json, application/vnd.github.v3+json, application/vnd.github.machine-man-preview, text/html';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $return_headers = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$return_headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $return_headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );


        $this->response = curl_exec($ch);
        $this->debug[] = curl_getinfo($ch);
        $this->debug[] = $return_headers;

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

    function debug($any = ['nothing'])
    {
        if (empty($any)) {
            $any = 'blank';
        }
//        print '<pre><<<';
//        debug_print_backtrace();
//        print('Variable:' . print_r($any, 1) . 'Session:' . print_r($this, 1) . '</pre>');
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