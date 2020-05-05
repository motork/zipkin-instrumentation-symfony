<?php


namespace ZipkinBundle\Messenger\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

class ZipkinMessageHandlerSubscriber implements EventSubscriberInterface
{


    public function __construct()
    {

    }

    public function manageHandledMessage(WorkerMessageHandledEvent $event)
    {

    }

    public function manageIncomingMessage(WorkerMessageReceivedEvent $event)
    {

    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageHandledEvent::class => 'manageHandledMessage',
            WorkerMessageReceivedEvent::class => 'manageIncomingMessage',

        ];
    }
}