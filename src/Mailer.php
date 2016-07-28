<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-7-13 下午12:06
 */
namespace Runner\GatlingMail;

use Runner\GatlingMail\Drivers\DriversAbstract;

class Mailer
{

    /**
     * @var DriversAbstract
     */
    protected $driver;

    /**
     * @var Email
     */
    protected $email;


    public function __construct(DriversAbstract $driversAbstract)
    {
        $this->driver = $driversAbstract;
    }


    public function setEmail(Email $email)
    {
        $this->email = $email;

        return $this;
    }


    public function getEmail()
    {
        return $this->email;
    }


    public function send()
    {
        return $this->driver->send($this->email);
    }


    public function sendReturn()
    {
        return $this->driver->sendReturn();
    }


}
