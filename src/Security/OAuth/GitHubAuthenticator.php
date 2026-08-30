<?php
namespace App\Security\OAuth;

use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;

class GitHubAuthenticator extends AbstractOAuthAuthenticator
{
    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'oauth_connect_check'
            && $request->attributes->get('provider') === 'github';
    }
    protected function getEmail(ResourceOwnerInterface $resourceOwner): string {
        /** @var GithubResourceOwner $resourceOwner */
        /** @var GithubResourceOwner $resourceOwner */
        $email = $resourceOwner->getEmail();
        if (!$email || str_ends_with($email, '@users.noreply.github.com')) {
            $userArray = $resourceOwner->toArray();
            
            if (!empty($userArray['email'])) {
                return $userArray['email'];
            }
        } 
        return $email;
    }
    protected function getClientKey(): string {
        return 'github_main';
    }
}