<?php
namespace Zaek\Response;

class Json extends Web
{
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
            echo json_encode($this->result);
        }
    }
}