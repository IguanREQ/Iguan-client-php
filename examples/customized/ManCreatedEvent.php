<?php

use Iguan\Event\Event;
use Iguan\Event\EventBundle;

class ManCreatedEvent extends Event
{
    const NAME = 'man.created';
    private $man;

    public function unpack(EventBundle $bundle)
    {
        $payload = $bundle->getPayload();
        $man = $payload['man'];
        $this->man = new Man($man['id'], $man['name'], $man['age']);
    }

    /**
     * @return Man
     */
    public function getMan()
    {
        return $this->man;
    }

    public static function compose($id, $name, $age)
    {
        return self::create(self::NAME, ['man' => ['id' => $id, 'name' => $name, 'age' => $age]]);
    }
}