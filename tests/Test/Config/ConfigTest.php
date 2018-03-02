<?php
namespace Test\Config;

use Iguan\Common\Util\DotArrayAccessor;
use Iguan\Event\Builder\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testSettingValue() {
        $config = new Config(new DotArrayAccessor());

        $config->setValue('leaf', 'leaf');
        $this->assertEquals('leaf', $config->getValue('leaf'));
        $config->setValue('node.false', false);
        $this->assertEquals(false, $config->getValue('node.false'));
        $config->setValue('node.leaf.0', 0);
        $this->assertEquals(0, $config->getValue('node.leaf.0'));
        $config->setValue('node.leaf.null', null);
        $this->assertEquals(null, $config->getValue('node.leaf.null'));
        $config->setValue('node.leaf.leaf', ['leaf']);
        $this->assertSame(['leaf'], $config->getValue('node.leaf.leaf'));
    }

    public function testRewritingSettingValue() {
        $config = new Config(new DotArrayAccessor(['node' => ['leaf' => 'value']]));

        $config->setValue('node.leaf', 'another');
        $this->assertEquals('another', $config->getValue('node.leaf'));
    }

    public function testGettingConfigFromFile() {
        $yamlConfig = Config::fromFile(__DIR__ . DIRECTORY_SEPARATOR . 'config.yml');
        $this->runTestDefaultConfig($yamlConfig);
        $jsonConfig = Config::fromFile(__DIR__ . DIRECTORY_SEPARATOR . 'config.json');
        $this->runTestDefaultConfig($jsonConfig);
    }

    private function runTestDefaultConfig(Config $config) {
        $this->assertEquals('tag', $config->getValue('common.tag'));
        $this->assertEquals('json', $config->getValue('common.remote.payload_format'));
        $this->assertEquals('invalid', $config->getValue('invalid.key', 'invalid'));
    }

}
