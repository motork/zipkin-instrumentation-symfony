<?php

namespace Unit\Components\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Zipkin\Kind;
use Zipkin\Reporters\InMemory;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Tags;
use Zipkin\TracingBuilder;
use Zipkin\DefaultTracing;
use Zipkin\Tracing;
use ZipkinBundle\Components\Messenger\SendMessageSubscriber;
use PHPUnit\Framework\TestCase;

final class SendMessageSubscriberTest extends TestCase
{
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
        $sut = new SendMessageSubscriber($this->tracing);
        $event = new SendMessageToTransportsEvent(new Envelope(new \stdClass()));

        $sut->handle($event);

        $this->tracing->getTracer()->flush();
        $spans = $this->reporter->flush();
        $this->assertCount(1, $spans);
        $this->assertArraySubset([
                'tags' => [
                    Tags\LOCAL_COMPONENT => 'symfony',
                ],
                'kind' => Kind\PRODUCER
            ],
            $spans[0]->toArray()
        );
    }

}
