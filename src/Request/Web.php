<?php
namespace Zaek\Framy\Request;

class Web extends Request
{
    public function __construct($method = null, $uri = null)
    {
        if(is_null($method)) {
            $this->_method = $_SERVER['REQUEST_METHOD'] ?? '';
        } else {
            $this->_method = $method;
        }

        if($this->_method == 'CLI') {
            throw new InvalidRequest('Unsupported method');
        }

        if(is_null($uri)) {
            $this->_uri = $_SERVER['REQUEST_URI'] ?? '';
        } else {
            $urlParsed = parse_url($uri ?? '');
            parse_str($urlParsed['query'] ?? '', $this->_get);
            if(!empty($urlParsed['path'])) {
                $this->_uri = $urlParsed['path'];
            }
        }

        $this->_files = $_FILES;
    }

    /**
     * @return mixed
     */
    public function files()
    {
        return $_FILES;
    }
}