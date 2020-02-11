<?php
/*define('OAUTH2_CLIENT_ID', '53af175ce5d46b80f33a');
define('OAUTH2_CLIENT_SECRET', '9f906210cbce17ccc66cb97050e3c7d22bdfa4ac');
define('APP_NAME', 'GitIntegration');
define('GITHUB_ACCOUNT', 'SwordfishCode');*/


define('OAUTH2_CLIENT_ID', '2434d612549dff0bb4e0');
define('OAUTH2_CLIENT_SECRET', 'b815281ba8cd9cc295b4b6bc1ed375da8d50ad61');
define('APP_NAME', 'swordfish-assignment');
define('GITHUB_ACCOUNT', 'AllieTheDemonHunter');


define('APP_NAME_LOCAL', 'swordhunter');
define('DOMAIN', 'allie.co.za');
define('PROTOCOL', 'https'); //Enforcing this, sorry, not sorry.
define('AUTH_URL', 'https://github.com/login/oauth/authorize');
define('TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('API_URL', 'https://api.github.com');

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
     * @var mixed
     */
    public $curl_info;
    /**
     * @var string|true
     */
    public $debug;

    /**
     * gitHub constructor.
     */
    function __construct()
    {
        //Making life easier.
        $this->base_url = PROTOCOL . '://' . DOMAIN . '/' . APP_NAME_LOCAL;
        if ($this->session('access_token')) {
            return $this;
        }
        if ($this->get('code')) {
            // When Github redirects the user back here.
            // Verify the state matches our stored state
            if (!$this->get('state') || $_SESSION['state'] != $this->get('state')) {
                header('Location: ' . $this->base_url);
                exit();
            }
            // Exchange the auth code for a token
            $token = $this->apiRequest(TOKEN_URL, array(
                'client_id' => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
                'redirect_uri' => $this->base_url,
                'state' => $this->get('state'),
                'code' => $this->get('code'),
                'scope' => 'repo',
                'User-Agent' => APP_NAME //Need this for v.3.
            ));

            $_SESSION['access_token'] = $token;

            header('Location: ' . $this->base_url);
            exit();
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
                'redirect_uri' => 'https://allie.co.za/swordhunter/',
                'state' => $this->session('state'),
                'scope' => 'repo'
            );
            // Redirect the user to Github's authorization page
            header('Location: ' . AUTH_URL . '?' . http_build_query($params));
            exit();
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
        print_r($_SESSION);die();
        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        }
        $headers[] = 'Accept: application/json';
        if ($this->session('access_token')) {
            $headers[] = 'Authorization: token ' . $this->session('access_token');
        }
        $headers[] = 'User-Agent:' . APP_NAME;
        $headers[] = 'application/vnd.github.machine-man-preview+json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $this->response = curl_exec($ch);
        $this->curl_info = curl_getinfo($ch);

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