<?php
namespace Test\Subscriber;
use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Dispatcher\EventDispatchException;
use Iguan\Event\Subscriber\Subject;

/**
 * Class RegisterStubCommunicateStrategy
 *
 * @package Test\Subscriber
 */
class RegisterStubCommunicateStrategy extends CommunicateStrategy
{
    private $registerCount = 0;

    /**
     * Emit event according to current strategy.
     *
     * @param EventDescriptor $descriptor event describer structure that must
     *                        be passed to recipient.
     *
     * @throws EventDispatchException in case of any dispatch error
     */
    public function emitEvent(EventDescriptor $descriptor)
    {
        // TODO: Implement emitEvent() method.
    }

    /**
     * Register new subject as an event handler.
     * It does not mean, that subject is ready to receive an
     * events. For receiving events need to subscribe in EventSubscriber.
     *
     * @param Subject $subject to register
     */
    public function register(Subject $subject, $sourceTag)
    {
        $this->registerCount++;
    }

    /**
     * Cancel registration for passed subject.
     * This subject will never receive any invokes.
     *
     * @param Subject $subject to unsubscribe
     * @param $sourceTag
     * @return
     */
    public function unRegister(Subject $subject, $sourceTag)
    {
        // TODO: Implement unRegister() method.
    }

    public function unRegisterAll($sourceTag)
    {
        // TODO: Implement unRegisterAll() method.
    }

    public function subscribe(Subject $subject)
    {
        // TODO: Implement subscribe() method.
    }

    /**
     * @return int
     */
    public function getRegisterCount()
    {
        return $this->registerCount;
    }
}