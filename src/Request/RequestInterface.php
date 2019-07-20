<?php
namespace Zaek\Framy\Request;

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
     * Request path
     * @return mixed
     */
    public function getUri();

}