<?php


namespace ZipkinBundle\Messenger\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Zipkin\Tracer;


class Zipkin implements MiddlewareInterface
{

    /**
     * @var Tracer
     */
    private $tracer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Tracer $tracer, LoggerInterface $logger)
    {
        $this->tracer = $tracer;
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        //check if message is incoming
        if($envelope->all(ReceivedStamp::class)) {
            $this->handleIncomingMessage($envelope, $stack);
        }
        else {
            $this->handleSendingMessage($envelope, $stack);
        }

        return $stack->next()->handle($envelope, $stack);
    }

    private function handleIncomingMessage(Envelope $envelope, StackInterface $stack)
    {
        //TODO manage incoming message
    }

    private function handleSendingMessage(Envelope $envelope, StackInterface $stack)
    {
        //TODO manage sending message
    }
}