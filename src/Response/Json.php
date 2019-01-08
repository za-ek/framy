<?php
namespace Zaek\Framy\Response;

class Json extends Web
{
    private $cfg;
    private $status_ok = true;

    public function __construct($cfg = [])
    {
        $this->cfg = $cfg;
        if(!isset($cfg['useDefault']) || $cfg['useDefault']) {
            $this->status_ok = true;
        }
    }

    public function showError($errorCode)
    {
        $this->error = $errorCode;

        $errorDescription = (!empty(self::$codes[$errorCode])) ? self::$codes[$errorCode] : 'Unknown error';
        echo json_encode([
            'status' => 'error',
            'error_code' => $errorCode,
            'error_description' => $errorDescription,
        ]);
    }
    public function flush()
    {
        if(!$this->error) {
            if($this->status_ok && empty($this->result['status'])) {
                $this->result['status'] = 'ok';
            }
            echo json_encode($this->result);
        }
    }
}