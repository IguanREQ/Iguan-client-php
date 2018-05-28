<?php

namespace Test\Integrated;


use Iguan\Common\Data\JsonDataEncoder;
use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Emitter\RpcCallException;
use Iguan\Event\Subscriber\Subject;

class CliTestCommunicateStrategy extends CommunicateStrategy
{

    private $lastRunOutput = '';

    /**
     * Emit event according to current strategy.
     *
     * @param EventDescriptor $descriptor event describer structure that must
     *                        be passed to recipient.
     *
     * @throws RpcCallException in case of any dispatch error
     * @throws \Iguan\Common\Data\JsonException
     */
    public function emitEvent(EventDescriptor $descriptor)
    {
        $encoder = new JsonDataEncoder();
        $this->lastRunOutput = shell_exec(
            'php "' . __DIR__ . '/src/index.php" '
            . base64_encode($encoder->encode(['events' => [$encoder->encode($descriptor)]]))
            . ' ' . addslashes($this->getAuth()->getLogin())
            . ' ' . addslashes($this->getAuth()->getPassword())
            . ' 2>&1 '
        );
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
        // TODO: Implement register() method.
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
     * @return string
     */
    public function getLastRunOutput()
    {
        return $this->lastRunOutput;
    }
}