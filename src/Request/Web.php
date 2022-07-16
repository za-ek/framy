<?php
namespace Zaek\Framy\Request;

class Web extends Request
{
    public function __construct($method, $uri)
    {
        $this->_method = $method;

        if($this->_method == 'CLI') {
            throw new InvalidRequest('Unsupported method');
        }

        if(is_null($uri)) {
            throw new InvalidRequest('No URI provided');
        } else {
            $urlParsed = parse_url($uri);
            parse_str($urlParsed['query'] ?? '', $queries);
            foreach($queries as $query => $value) {
                $this->addQuery($query, $value);
            }
            if(!empty($urlParsed['path'])) {
                $this->_uri = $urlParsed['path'];
            }
        }

        $this->_files = $_FILES;
    }
}