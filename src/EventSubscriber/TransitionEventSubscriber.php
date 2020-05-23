<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;

class TransitionEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onWorkflowArticleTransition(TransitionEvent $event): void
    {
        $context = $event->getContext();

        $token = $this->tokenStorage->getToken()->getUser();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
            if ($user instanceof UserInterface) {
                $context['user'] = $user->getUsername();
            }
        }

        $event->setContext($context);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TransitionEvent::class => 'onWorkflowArticleTransition',
        ];
    }
}
