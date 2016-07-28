<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-7-13 下午12:13
 */
namespace Runner\GatlingMail\Drivers;

use Runner\GatlingMail\Email;

abstract class DriversAbstract
{

    /**
     * @var array
     */
    protected $config;


    /**
     * DriversAbstract constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }


    /**
     * @param Email $email
     * @return bool
     */
    abstract public function send(Email $email);


    /**
     * @return array
     */
    abstract public function sendReturn();

}
