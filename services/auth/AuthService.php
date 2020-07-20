<?php


namespace app\services\auth;


use PhpAmqpLib\Connection\AMQPStreamConnection;

class AuthService
{
    /** @var AMQPStreamConnection $connection */
    private $connection;

    public function getConnection()
    {
        if(is_null($this->connection)) {
            $this->connection = new AMQPStreamConnection(
                'localhost',
                5672,
                'guest',
                'guest'
            );
        }
        return $this->connection;
    }

    public function loginEmail($email, $password)
    {
        $connection = $this->getConnection();
        $channel = $this->getConnection()->channel();
        $channel->basic_qos('login', [
            'email' => $email,
            'password' => $password,
        ], null);
        $channel->basic_consume(
            'login',
            '',
            false,
            false,
            false,
            false,

            function($req) {

                $a = 0;

            });

        while ($channel->is_consuming()) {
            $channel->wait();
        }

    }

}
