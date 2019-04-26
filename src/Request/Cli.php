<?php
namespace Zaek\Framy\Request;

class Cli extends Request
{
    protected $_arguments = [];

    public function __construct($url = null)
    {
        $urlParsed = parse_url($url ?? $_SERVER['argv'][1] ?? '');
        parse_str($urlParsed['query'] ?? '', $this->_get);
        if(!empty($urlParsed['path'])) {
            $this->_uri = $urlParsed['path'];
        }

        $this->_method = 'CLI';

        if($_SERVER['argc'] > 2) {
            for($i = 2; $i < $_SERVER['argc']; $i++) {
                $this->parseArgument($_SERVER['argv'][$i]);
            }
        }
    }

    protected function parseArgument($arg)
    {
        $arg = explode('=', $arg);
        switch ($arg[0]) {
            case '--post':
                parse_str($arg[1], $this->_post);
                break;
            case '--useMethod':
                $this->_method = $arg[1];
                break;
        }
        $this->_arguments[substr($arg[0], 2)] = $arg[1];
    }

    public function getArgument($arg)
    {
        return $this->_arguments[$arg] ?? null;
    }
}