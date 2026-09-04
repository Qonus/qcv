<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleRedirectSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 20],
            ],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $path = $request->getPathInfo();

        if (
            str_starts_with($path, '/_') ||
            str_starts_with($path, '/build') ||
            str_starts_with($path, '/assets')
        ) {
            return;
        }

        if (preg_match('#^/(en|ru)(/|$)#', $path)) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        $locale = $user instanceof User
            ? $user->getLocale()
            : 'en';

        $event->setResponse(
            new RedirectResponse(
                '/' . $locale . $path
            )
        );
    }
}