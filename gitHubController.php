<?php

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
    public $token;

    /**
     * gitHub constructor.
     */
    function __construct()
    {
        //Making life easier.
        $this->base_url = PROTOCOL . '://' . DOMAIN . '/' . APP_NAME_LOCAL;
        $this->token = $this->session('access_token');
        if ($this->token) {

            $open = $this->apiRequest(API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME
                . '/issues?state=open'
            );

            $closed = $this->apiRequest(API_URL . '/repos/' . GITHUB_ACCOUNT . '/' . APP_NAME
                . '/issues?state=closed'
            );

            $this->response = (array_merge($open, $closed));
            echo '<h3>Logged In</h3>';
            return $this->response;
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
                'state' => $_SESSION['state'],
                'code' => $this->get('code'),
                'User-Agent' => APP_NAME //Need this for v.3.
            ));
            $_SESSION['access_token'] = $token->access_token;
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
                'scope' => 'user',
                'state' => $_SESSION['state']
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
        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept: application/vnd.github.machine-man-preview'; //Nice to have
        if ($this->session('access_token'))
            $headers[] = 'Authorization: token ' . $this->token;
        $headers[] = 'User-Agent:' . APP_NAME;
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