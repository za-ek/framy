<?php
namespace Zaek\Framy;

class User
{
    /**
     * @var App
     */
    private $app;

    private $id    = 0;
    private $login = '';
    private $email = '';

    /**
     * User constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->controller = $app;

        try {
            $useDefault = $app->conf('useDefault');
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