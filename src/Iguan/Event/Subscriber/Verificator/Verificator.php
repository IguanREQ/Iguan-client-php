<?php

namespace Iguan\Event\Subscriber\Verificator;

use Iguan\Event\Subscriber\SubjectNotifyWay;

/**
 * Class Verificator
 * Check incoming data was sent by trusted source.
 *
 * @author Vishnevskiy Kirill
 */
abstract class Verificator
{
    /**
     * @param SubjectNotifyWay $way to validate
     * @return boolean true - if data verified, false otherwise
     */
    public abstract function isVerified(SubjectNotifyWay $way);
}