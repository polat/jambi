<?php

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * Class Cookies
 *
 */
class Cookies
{
    /**
     * Response class instance
     * @var object $Response
     */
    private $Response;

    /**
     * Request class instance
     * @var object $Request
     */
    private $Request;


    public function __construct()
    {
        $this->Response = new Response();
        $this->Request = Request::createFromGlobals();
    }

    /**
     * Creates a cookie with the specified name, time and value
     *
     * @access public
     * @default Cookie Life Time($cookieTime) 2147483647
     * @return void
     */
    public function set($cookieName, $cookieValue, $cookieTime = 2147483647)
    {
        $this->Response->headers->setCookie(new Cookie($cookieName, $cookieValue, $cookieTime));
        $this->Response->send();
    }

    /**
     * Gives the name entered cookie
     *
     * @access public
     * @return String
     */
    public function get($cookieName)
    {
        return $this->Request->cookies->get($cookieName);
    }

    /**
     *
     * @access public
     */
    public function clear($cookieName)
    {
        $this->Response->headers->clearCookie($cookieName);
        $this->Response->send();
    }
}