<?php
/**
 * + POST
 * - - login
 * - - email
 * - - password
 * - - confirm_password
 *
 * + RESPONSE
 * - - status = 'ok'
 * - - userId = (int)
 *
 * @var $this \Zaek\Framy\App
 */

$this->setResponse(new \Zaek\Framy\Response\Json());
$data = $this->request()->post('login', 'password', 'email', 'confirm_password');

if($data['password'] !== $data['confirm_password']) {
    throw new Exception('password doesn\'t match password confirmation');
}

if(empty($data['email'])) {
    throw new Exception('email is incorrect');
}

if(!strstr($data['email'], '@')) {
    throw new Exception('email is incorrect');
}

if(empty($data['login'])) {
    throw new Exception('login is empty');
}

if(strlen($data['login']) < 3) {
    throw new Exception('login must contain at least 3 characters');
}

if(preg_match('/^\w\d_/', $data['login'])) {
    throw new Exception('login may contain only latin symbols, digits and _');
}

$userId = $this->user()->create($data);

if($userId) {
    return [
        'userId' => $userId,
    ];
} else {
    throw new Exception;
}