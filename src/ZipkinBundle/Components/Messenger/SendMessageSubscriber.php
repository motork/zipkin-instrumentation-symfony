<?php


namespace ZipkinBundle\Components\Messenger;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Zipkin\Kind;
use Zipkin\Tags;
use Zipkin\Tracing;

class SendMessageSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Zipkin\Tracer
     */
    private $tracer;

    public function __construct(Tracing $tracing, array $tags = [])
    {
        $this->tracer = $tracing->getTracer();
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
    }
}