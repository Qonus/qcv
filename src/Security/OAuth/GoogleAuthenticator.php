<?php
namespace App\Security\OAuth;

use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;

class GoogleAuthenticator extends AbstractOAuthAuthenticator
{
    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'oauth_connect_check'
            && $request->attributes->get('provider') === 'google';
    }
    protected function getEmail(ResourceOwnerInterface $resourceOwner): string {
        /** @var GoogleUser $resourceOwner */
        return $resourceOwner->getEmail();
    }
    protected function getClientKey(): string {
        return 'google_main';
    }
}