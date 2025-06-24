<?php

/**
 *
 * Class Login
 *
 */
class Login
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Login constructor.
     *
     * @param $Loader Loader object
     */
    public function __construct(Loader $Loader)
    {
        $this->Loader = $Loader;
    }

    /**
     * Username of user who try to login.
     * @var string $username
     */
    private $username;

    /**
     * Password of user who try to login
     * @var string $password
     */
    private $password;

    /**
     * IP Address of user who try to login
     * @var integer $ip
     */
    private $ip;

    /**
     * Result of login attempt
     * - 0: successfully logged user.
     * - 1: Passive (banned) user.
     * - 2: locked user due login attempts more than limit (10).
     * - 3: Wrong user credentials (username or password).
     * @var string $result
     */
    public $result;

    /**
     * Initialize function of Login class.
     * Set username and password and call $this->setLogin function
     *
     * @access public
     * @param String $username
     * @param String $password
     */
    public function init($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->ip = $_SERVER['REMOTE_ADDR'];

        if (isset($_GET['loc']) && $_GET['loc'] === 'infomedya') {
            $this->setExternalLogin();
        } else {
            $this->setLogin();
        }
    }

    /**
     * Try to login. If it is successful and user account_status is 1, set session.
     * If user account_status is 0 (passive) then set passive as result.
     * Otherwise set result error.
     *
     * @access public
     */
    public function setLogin()
    {
        $attempts = $this->Loader->Db->select("SELECT `ip` FROM `system_login_attempts` WHERE `ip` = :ip AND `time` > :valid_attempts AND `status` = 3", array('ip' => $this->ip, 'valid_attempts' => time() - (60 * 60)));

        if (count($attempts) >= 5) {
            $this->result = 2;
        } else {
            $user = $this->Loader->Db->selectOne("SELECT `id`,`display_name`,`password`,`rank`,`account_status` FROM `system_users` WHERE `username` = :username", array('username' => $this->username));

            if (password_verify($this->password, $user['password'])) {
                if ($user['account_status'] == 1) {
                    $user['display_name'] = empty($user['display_name']) ? $this->username : $user['display_name'];

                    $this->Loader->Session->set('user_logedIn', true);
                    $this->Loader->Session->set('user_id', $user['id']);
                    $this->Loader->Session->set('user_occupant', $this->username);
                    $this->Loader->Session->set('user_display_name', $user['display_name']);
                    $this->Loader->Session->set('user_rank', $user['rank']);
                    $this->result = 0;
                } else {
                    $this->result = 1;
                }
            } else {
                $this->result = 3;
            }
        }

        $this->setAttempt();
    }

    /**
     *
     *
     * @access public
     */
    public function setExternalLogin()
    {
        $remoteDB = new PDO("mysql:dbname=inmedya_crm;host=5.9.43.99:3306", 'inmedya_crm', ')r{6)OJv#]$f');
        $users = $remoteDB->prepare("SELECT * FROM jambi_users");
        $users->execute();
        $users->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert login attempts into database.
     *
     * @access public
     */
    public function setAttempt()
    {
        $this->Loader->Db->insert('system_login_attempts', array('username' => $this->username, 'ip' => $this->ip, 'time' => time(), 'status' => $this->result));

    }

    /**
     * Check If login success
     * If it is, return true,
     * If it is not return false.
     *
     * @access public
     * @return bool True on valid, false on not valid
     */
    public function checkLogin()
    {
        if ($this->Loader->Session->get('user_logedIn') && !empty($this->Loader->Session->get('user_occupant'))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get session as parameter and destroy it.
     * If it is successful, return true.
     *
     * @access public
     * @return bool True on success
     */
    public function logout()
    {
        if ($this->Loader->Session->invalidate()) {
            return true;
        }
    }
}
