<?php
define('OAUTH2_CLIENT_ID', '53af175ce5d46b80f33a');
define('OAUTH2_CLIENT_SECRET', '9f906210cbce17ccc66cb97050e3c7d22bdfa4ac');
define('APP_NAME', 'swordfish-assignment');
define('OAUTH_APP_NAME', 'swordhunter');
define('GITHUB_ACCOUNT', 'AllieTheDemonHunter');
define('DOMAIN', 'allie.co.za');
define('PROTOCOL', 'https'); //Enforcing this, sorry, not sorry.
define('AUTH_URL', 'https://github.com/login/oauth/authorize');
define('TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('API_URL', 'https://api.github.com');
error_reporting(E_ALL);
ini_set('display_errors', 'yes');


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
    public $access_token;

    /**
     * gitHub constructor.
     */
    function __construct()
    {
        //Making life easier.
        $this->base_url = PROTOCOL . '://' . DOMAIN . '/' . OAUTH_APP_NAME;
        $this->access_token = $this->session('access_token');
        if ($this->access_token) {

            $open = $this->apiRequest(API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME
                . '/issues?state=open'
            );

            $closed = $this->apiRequest(API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME
                . '/issues?state=closed'
            );

            $this->response = array_reverse(array_merge($open, $closed));
            echo '<h3>Logged In</h3>';
            return $this->response;
        }

        if ($this->get('code')) {
            // When Github redirects the user back here.
            // Verify the state matches our stored state
            if (!$this->get('state') || $_SESSION['state'] != $this->get('state')) {
                header('Location: ' . $this->base_url);
                exit('Verify the state matches our stored state === FALSE');
            }
            // Exchange the auth code for a token
            $post_for_auth = array(
                'client_id' => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
                'redirect_uri' => $this->base_url,
                'state' => $_SESSION['state'],
                'code' => $this->get('code'),
            );

            $token = $this->apiRequest(TOKEN_URL, $post_for_auth);
            $this->debug($token);
            if(!empty($token)) {
                $_SESSION['access_token'] = $token;
                header('Location: ' . $this->base_url);
            }
        }

        if ($this->get('login')) {
            // Send the user to Github's authorization page

            // Generate a random hash and store in the session for security
            $_SESSION['state'] = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']);

            // Freshen up
            unset($_SESSION['access_token']);

            // Sending this to get logged in.
            $params = array(
                'client_id' => OAUTH2_CLIENT_ID,
                'redirect_uri' => $this->base_url,
                'login' => GITHUB_ACCOUNT,
                'scope' => 'repo',
                'state' => $_SESSION['state']
            );
            // Redirect the user to Github's authorization page
            header('Location: ' . AUTH_URL . '?' . http_build_query($params));
        }

        //All clauses have exit().
        echo '<h3>Not logged in</h3>';
        echo '<p><a href="?login=1">Log In</a></p>';
        return true;
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        }

        $headers[] = 'Accept: application/vnd.github.v3+json';
        $headers[] = 'User-Agent:' . OAUTH_APP_NAME;
#        $headers[] = 'application/vnd.github.machine-man-preview+json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $this->response = curl_exec($ch);

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
        print '<pre>';
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