<?php

namespace Test\Config;

use Iguan\Common\Data\DataCoderProvider;
use Iguan\Common\Util\DotArrayAccessor;
use Iguan\Event\Builder\Builder;
use Iguan\Event\Builder\Config;
use Iguan\Event\Common\CommonAuth;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @throws \Iguan\Event\Common\CommunicateException
     */
    public function testCreatingValid() {
        $config = Config::fromFile(__DIR__ . DIRECTORY_SEPARATOR . 'valid_config.yml');
        $builder = new Builder($config);
        $emitter = $builder->buildEmitter();
        $subscriber = $builder->buildSubscriber();

        $this->assertEquals(MyEventEmitter::class, get_class($emitter));
        $this->assertEquals(MyEventSubscriber::class, get_class($subscriber));

        $this->assertEquals($config->getValue('common.tag'), $subscriber->getSourceTag());

        /** @var MyRemoteStrategy $strategy */
        $strategy = $subscriber->getStrategy();
        $this->assertEquals($config->getValue('common.remote.wait_for_answer'), $strategy->isWaitForAnswer());

        $validDecoder = DataCoderProvider::getDecoderForFormat($config->getValue('common.remote.payload_format'));
        $validEncoder = DataCoderProvider::getEncoderForFormat($config->getValue('common.remote.payload_format'));

        $this->assertEquals(get_class($validDecoder), get_class($strategy->getDecoder()));
        $this->assertEquals(get_class($validEncoder), get_class($strategy->getEncoder()));

        $auth = $strategy->getAuth();
        $exceptedAuth = new CommonAuth($config->getValue('common.auth.token'), $config->getValue('common.auth.token_name'));
        $this->assertTrue($exceptedAuth->equals($auth));

        /** @var MyRemoteClient $remoteClient */
        $remoteClient = $strategy->getRemoteClient();

        $this->assertEquals(MyRemoteClient::class, get_class($remoteClient));

        /** @var MySocketClient $socketClient */
        $socketClient = $remoteClient->getClient();
        $this->assertEquals(MySocketClient::class, get_class($socketClient));

        $socket = $config->getValue('common.remote.client.socket');
        $this->assertEquals($socket['protocol'] . '://' . $socket['host'] . ':' . $socket['port'], $socketClient->getRemoteSocket());
        $this->assertEquals($socket['persist'], $socketClient->isPersist());
    }


}
