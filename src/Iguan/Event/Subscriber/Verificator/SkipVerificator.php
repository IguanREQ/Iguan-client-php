<?php

namespace Iguan\Event\Subscriber\Verificator;

use Iguan\Event\Subscriber\SubjectNotifyWay;

/**
 * Class SkipVerificator
 * Without any verifications.
 *
 * BE CAREFUL!!!
 *
 * @author Vishnevskiy Kirill
 */
class SkipVerificator extends Verificator
{
    /**
     * ANY PAYLOAD IS ALWAYS TRUSTED!!!
     * BE CAREFUL!!!
     * DO NOT USE IN WILD-/NON-TRUSTED-NETWORKS!
     *
     * @param SubjectNotifyWay $way
     * @return bool
     */
    public function isVerified(SubjectNotifyWay $way)
    {
        return true;
    }
}