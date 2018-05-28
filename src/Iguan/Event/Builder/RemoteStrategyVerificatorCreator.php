<?php

namespace Iguan\Event\Builder;


use Iguan\Event\Subscriber\Verificator\SignVerificator;
use Iguan\Event\Subscriber\Verificator\SkipVerificator;
use Iguan\Event\Subscriber\Verificator\Verificator;

/**
 * Class RemoteStrategyVerificatorCreator
 * Creator for 'common.remote.verificator' key.
 *
 * @author Vishnevskiy Kirill
 */
class RemoteStrategyVerificatorCreator extends Creator
{
    const EXCEPTED_CONFIG_VALUES = [
        'class' => Verificator::class,
        'sign.public_key_path' => [
            'types' => ['string']
        ]
    ];

    /**
     * Create an instance for config subset.
     *
     * @return mixed
     * @throws \Iguan\Event\Subscriber\Verificator\InvalidPublicKeyException
     */
    public function create()
    {
        $class = $this->getExceptedConfigValue('class');
        $publicKeyPath = $this->getExceptedConfigValue('sign.public_key_path', '');
        if (empty($class) && empty($publicKeyPath)) {
            return new SkipVerificator();
        }

        if (!empty($publicKeyPath) && empty($class)) {
            return new SignVerificator($publicKeyPath);
        }

        if (is_subclass_of($class, SignVerificator::class)) {
            return new $class($publicKeyPath);
        }

        return new $class();
    }
}