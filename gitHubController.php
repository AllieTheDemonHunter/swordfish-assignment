<?php
define('OAUTH2_CLIENT_ID', '53af175ce5d46b80f33a');
define('OAUTH2_CLIENT_SECRET', '9f906210cbce17ccc66cb97050e3c7d22bdfa4ac');

define('APP_NAME', 'GitIntegration');
define('OAUTH_APP_NAME', 'swordhunter');
define('GITHUB_ACCOUNT', 'AllieTheDemonHunter');
define('REPO_NAME', 'GitIntegration');
define('DOMAIN', 'allie.co.za');
define('PROTOCOL', 'https'); //Enforcing this, sorry, not sorry.
define('AUTH_URL', 'https://github.com/login/oauth/authorize');
define('TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('API_URL', 'https://api.github.com');
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
    public $base_url;
    public $response;

    /**
     * gitHub constructor.
     */
    function __construct()
    {
        //Making life easier.
        $this->base_url = PROTOCOL . '://' . DOMAIN . '/' . OAUTH_APP_NAME;

        if ($this->get('login')) {
            // Send the user to Github's authorization page

            // Generate a random hash and store in the session for security
            $_state = $_SESSION['state'] = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']);

            // Freshen up
            unset($_SESSION['access_token']);

            // Sending this to get logged in.
            $params = array(
                'client_id' => OAUTH2_CLIENT_ID,
                'redirect_uri' => $this->base_url,
                'login' => GITHUB_ACCOUNT,
                'state' => $_state,
                'scope' => 'repo'
            );

            // Redirect the user to Github's authorization page
            header('Location: ' . AUTH_URL . '?' . http_build_query($params));
            exit();
        }

        if ($this->get('code')) {
            // When Github redirects the user back here.
            // Verify the state matches our stored state
            if (!$this->get('state') || $_SESSION['state'] != $this->get('state')) {
                unset($_SESSION['state']);
                //header('Location: ' . $this->base_url);
                exit('Verify the state matches our stored state === FALSE');
            }

            // Exchange the auth code for a token
            $post_for_auth = array(
                'client_id' => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
                'redirect_uri' => 'http://localhost:8080/receiving/',
                'state' => $this->get('state'),
                'code' => $this->get('code')
            );

            $token = $this->apiRequest(TOKEN_URL, $post_for_auth);

            if(!empty($token)) {
                $_SESSION['access_token'] = $token;
                return 1;
            } else {
                $this->debug($post_for_auth);
                header('Location: ' . $this->base_url.'#redirected-form-not-an-empty-token');
            }
        }

        if ($this->session('access_token')) {

            $open = $this->apiRequest(API_URL . '/repos/' . REPO_NAME . '/' . APP_NAME
                . '/issues?state=open'
            );

            $closed = $this->apiRequest(API_URL . '/repos/' . REPO_NAME . '/' . APP_NAME
                . '/issues?state=closed'
            );

            $this->response = array_reverse(array_merge($open, $closed));
            echo '<h3>Logged In</h3>';
            return $this->response;
        }

        //All clauses have exit().
        echo '<h3>Not logged in</h3>';
        echo '<p><a href="?login=1">Log In</a></p>';
        $this->debug($_REQUEST);
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
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        if ($this->session('access_token')) {
            $headers[] = 'Authorization: token ' . $this->session('access_token');
        }

        $headers[] = 'User-Agent:' . OAUTH_APP_NAME;
        $headers[] = 'Accept: application/vnd.github.v3+json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $this->response = curl_exec($ch);
        /**
         * Some error handling
         */
        $curl_info = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        if(curl_getinfo($ch,CURLINFO_HTTP_CODE) === '404') {
            print '<h2>Oh 404</h2>';
        }
        $_SESSION['debug'] = curl_getinfo($ch);

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

    function debug($any = ['nothing']) {
        if(empty($any)) {
            $any = 'blank';
        }
        print '<pre><<<';
        debug_print_backtrace();
        die('Variable:'.print_r($any,1).'Session:'.print_r($_SESSION,1).'</pre>');
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