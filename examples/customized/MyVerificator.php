<?php

use Iguan\Event\Subscriber\SubjectNotifyWay;
use Iguan\Event\Subscriber\Verificator\Verificator;

class MyVerificator extends Verificator
{

    /**
     * @param \Iguan\Event\Subscriber\SubjectNotifyWay $way to validate
     * @return boolean true - if data verified, false otherwise
     */
    public function isVerified(SubjectNotifyWay $way)
    {
        //we not recommend for using shared token between multiple
        //subscribers because one of them can stole it
        //and invoke others with invalid or broken data
        return $_SERVER['My-Token'] === 'token-token';
    }
}