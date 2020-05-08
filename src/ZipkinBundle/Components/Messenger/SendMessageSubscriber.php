<?php


namespace ZipkinBundle\Components\Messenger;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Zipkin\Kind;
use Zipkin\Propagation\B3;
use Zipkin\Tags;
use Zipkin\Tracing;

class SendMessageSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Zipkin\Tracer
     */
    private $tracer;
    /**
     * @var array
     */
    private $tags;

    public function __construct(Tracing $tracing, array $tags = [])
    {
        $this->tracer = $tracing->getTracer();
        $this->tags = $tags;
    }

    public static function getSubscribedEvents()
    {
        return [
            SendMessageToTransportsEvent::class => 'handle'
        ];
    }

    public function handle(SendMessageToTransportsEvent $event)
    {
        $currentSpan = $this->tracer->getCurrentSpan();
        $context = null;
        if(null !== $currentSpan) {
            $context = $currentSpan->getContext();
        }

        $span = $this->tracer->nextSpan($context);

        $span->setKind(Kind\PRODUCER);
        $span->tag(Tags\LOCAL_COMPONENT, 'symfony');
        foreach ($this->tags as $key => $value) {
            $span->tag($key, $value);
        }

        $stamp = new ZipkinStamp;
        $stamp->add(B3::SPAN_ID_NAME, $span->getContext()->getSpanId());
        $stamp->add(B3::PARENT_SPAN_ID_NAME, $span->getContext()->getParentId());
        $stamp->add(B3::TRACE_ID_NAME, $span->getContext()->getTraceId());

        $envelope = $event->getEnvelope();
        $envelope = $envelope->with($stamp);
        $event->setEnvelope($envelope);
    }
}