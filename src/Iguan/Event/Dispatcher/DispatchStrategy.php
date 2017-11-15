<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 12.11.2017
 * Time: 14:58
 */

namespace Iguan\Event\Dispatcher;
use Internal\Util\Encoder\DataEncoder;

/**
 * Class DispatchStrategy
 * @author Vishnevskiy Kirill
 */
abstract class DispatchStrategy
{
    /**
     * @var DataEncoder
     */
    private $encoder;

    public function __construct(DataEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    protected function encode(EventDescriptor $descriptor) {
        return $this->encoder->encode($descriptor);
    }

    public abstract function emitEvent(EventDescriptor $descriptor);
}