<?php
namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
class LoginSuccessListener
{
    public function __invoke(LoginSuccessEvent $event): void
    {
        // $user = $event->getUser();
        
        // if (!$user instanceof User) {
        //     return;
        // }

        // $request = $event->getRequest();
        
        // // Populate the session locale with user's preferred locale
        // if ($user->getLocale()) {
        //     $request->getSession()->set('_locale', $user->getLocale());
        // }
    }
}