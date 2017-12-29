<?php

namespace Test\Subscriber;

use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Common\Data\JsonDataEncoder;
use Iguan\Event\Common\CommonAuth;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Event;
use Iguan\Event\Subscriber\GlobalEventExtractor;
use Iguan\Event\Subscriber\SubjectCliNotifyWay;
use PHPUnit\Framework\TestCase;

/**
 * Class GlobalEventExtractorTest
 *
 * @author Vishnevskiy Kirill
 */
class GlobalEventExtractorTest extends TestCase
{
    /**
     * @throws \Iguan\Common\Data\JsonException
     * @throws \Iguan\Common\Data\EncodeDecodeException
     */
    public function testValidExtraction()
    {
        global $argv;

        $payloads = [
            null,
            'some string',
            420,
            4.20,
            true,
            new \Exception(''),
            ['ar' => 'ray'],
            (object)['ob' => 'ject'],
        ];
        /** @var EventDescriptor[] $sourceDescriptors */
        $sourceDescriptors = [];
        for ($i = 0; $i < 10; $i++) {
            $descriptor = new EventDescriptor();
            $event = new Event();
            $event->setToken("domain.event.$i");
            $event->setPayload(array_key_exists($i, $payloads) ? $payloads[$i] : []);
            $descriptor->event = $event->pack()->asArray();
            $descriptor->sourceTag = "tag_$i";
            $descriptor->firedAt = $i;
            $descriptor->delay = 0;
            $descriptor->dispatcher = 1;
            $sourceDescriptors[] = $descriptor;
        }

        $encoder = new JsonDataEncoder();
        $argv[1] = base64_encode($encoder->encode([
            'events' => array_map(function ($el) use ($encoder) {
                return $encoder->encode($el);
            }, $sourceDescriptors)
        ]));
        $argv[2] = null;
        $argv[3] = null;

        $extractor = new GlobalEventExtractor(new CommonAuth(), new JsonDataDecoder());
        $cliWay = new SubjectCliNotifyWay('does not matter');

        /** @var EventDescriptor[] $extractedDescriptors */
        $extractedDescriptors = $extractor->extract($cliWay);

        for ($i = 0; $i < count($sourceDescriptors); $i++) {
            $extracted = $extractedDescriptors[$i];
            $source = $sourceDescriptors[$i];
            $this->assertEquals($source->sourceTag, $extracted->sourceTag);
            $this->assertEquals($source->firedAt, $extracted->firedAt);
            $this->assertEquals($source->delay, $extracted->delay);
            $this->assertEquals($source->dispatcher, $extracted->dispatcher);
            $this->assertEquals($source->dispatcher, $extracted->dispatcher);
            $this->assertEquals($source->event, $extracted->event);
            $this->assertEquals($source->event['class'], get_class($extracted->raisedEvent));
            $this->assertEquals($source->event['token'], $extracted->raisedEvent->getToken());
            $this->assertEquals($source->event['payload'], $extracted->raisedEvent->getPayload());
        }

        $anotherDescriptors = $extractor->extract($cliWay);
        $this->assertSame($extractedDescriptors, $anotherDescriptors);
    }
}
