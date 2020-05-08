<?php

namespace Unit\Components\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Zipkin\Kind;
use Zipkin\Propagation\B3;
use Zipkin\Reporters\InMemory;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Tags;
use Zipkin\TracingBuilder;
use Zipkin\DefaultTracing;
use Zipkin\Tracing;
use ZipkinBundle\Components\Messenger\SendMessageSubscriber;
use PHPUnit\Framework\TestCase;
use ZipkinBundle\Components\Messenger\ZipkinStamp;

final class SendMessageSubscriberTest extends TestCase
{
    const TAG_KEY = 'key';
    const TAG_VALUE = 'value';
    /**
     * @var DefaultTracing|Tracing
     */
    private $tracing;

    private $reporter;

    protected function setUp()
    {
        $this->reporter = new InMemory;
        $this->tracing = TracingBuilder::create()
            ->havingSampler(BinarySampler::createAsAlwaysSample())
            ->havingReporter($this->reporter)
            ->build();
    }

    public function testSpanIsCreated()
    {
        $sut = new SendMessageSubscriber($this->tracing,
            [self::TAG_KEY => self::TAG_VALUE]);
        $event = new SendMessageToTransportsEvent(new Envelope(new \stdClass()));

        $sut->handle($event);

        $this->tracing->getTracer()->flush();
        $spans = $this->reporter->flush();
        $this->assertCount(1, $spans);

        $this->assertArraySubset([
                'tags' => [
                    Tags\LOCAL_COMPONENT => 'symfony',
                    self::TAG_KEY => self::TAG_VALUE
                ],
                'kind' => Kind\PRODUCER
            ],
            $spans[0]->toArray()
        );
    }

    public function testAddSpanToEnvelope()
    {
        $sut = new SendMessageSubscriber($this->tracing,
            [self::TAG_KEY => self::TAG_VALUE]);
        $event = new SendMessageToTransportsEvent(new Envelope(new \stdClass()));

        $sut->handle($event);

        $this->tracing->getTracer()->flush();
        $spans = $this->reporter->flush();
        $this->assertCount(1, $spans);
        $span = $spans[0]->toArray();

        $envelope = $event->getEnvelope();
        $stamps = $envelope->all(ZipkinStamp::class);
        $this->assertCount(1, $stamps);
        $context = $stamps[0]->getContext();
        $this->assertArrayHasKey(B3::TRACE_ID_NAME, $context);
        $this->assertEquals($span['traceId'], $context[B3::TRACE_ID_NAME]);
        $this->assertArrayHasKey(B3::PARENT_SPAN_ID_NAME, $context);
        $this->assertEquals($span['parentId'], $context[B3::PARENT_SPAN_ID_NAME]);
        $this->assertArrayHasKey(B3::SPAN_ID_NAME, $context);
        $this->assertEquals($span['id'], $context[B3::SPAN_ID_NAME]);
    }

}
