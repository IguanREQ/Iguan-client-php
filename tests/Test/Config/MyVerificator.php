<?php
/**
 * Created by PhpStorm.
 * User: 119
 * Date: 28.05.2018
 * Time: 15:25
 */

namespace Test\Config;


use Iguan\Event\Subscriber\SubjectNotifyWay;
use Iguan\Event\Subscriber\Verificator\SignVerificator;
use Iguan\Event\Subscriber\Verificator\Verificator;

class MyVerificator extends SignVerificator
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    public function isVerified(SubjectNotifyWay $way)
    {
        return false;
    }
}