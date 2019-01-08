<?php
namespace Zaek\Framy;

class User
{
    /**
     * @var Controller
     */
    private $controller;

    private $id    = 0;
    private $login = '';
    private $email = '';

    /**
     * User constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;

        try {
            $useDefault = $controller->getConf('useDefault');
        } catch (InvalidConfiguration $e) {
            $useDefault = true;
        }

        if($useDefault) {
            $this->id = 10012019;
            $this->login = 'framy';
            $this->email = 'framy@za-ek.ru';
        }
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        $start = rand(0, 54);
        $end = rand(6,10);
        $salt = substr(hash('sha1', time()), $start, $end);

        return $this->controller->db()->table('users')->insert([
            'login' => $data['login'],
            'email' => $data['email'],
            'salt' => $salt,
            'password' => hash('sha2', $data['password'] . $salt),
        ]);
    }
}