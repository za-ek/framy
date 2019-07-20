<?php
namespace Zaek\Framy\Request;

/**
 * Contain URL components and request method
 * URL structure: https://tools.ietf.org/html/rfc3986#page-16
 * Authority section is divided into user, pass, host and port parts.
 *
 * Interface RequestInterface
 * @package Zaek\Framy\Request
 */
interface RequestInterface
{
    /**
     * Default available methods are:
     *
     * GET
     * HEAD
     *
     * POST
     * PUT
     * DELETE
     * PATCH
     * CONNECT
     * OPTIONS
     * TRACE
     *
     * CLI
     *
     * @return mixed
     */
    public function getMethod();

    /**
     * Array of values
     * Return queries of request uri string
     *
     * example of use:
     * $arg1 = $request->getQueries('arg1')['arg1'];
     * $args = $request->getQueries('arg1', 'arg2');
     *
     * @param $key
     * @return mixed
     */
    public function getQueries(...$key);

    public function getPath();
    public function getScheme();
    public function getHost();
    public function getPort();
    public function getUser();
    public function getPass();
    public function getFragment();
}