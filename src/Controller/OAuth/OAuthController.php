<?php

namespace App\Controller\OAuth;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OAuthController extends AbstractController
{
    #[Route('/connect/{provider}', name: 'oauth_connect_start')]
    public function connect(string $provider, ClientRegistry $clientRegistry): Response
    {
        $clientName = $provider . '_main';

        $scopes = match ($provider) {
            'google' => ['profile', 'email'],
            'github' => ['read:user', 'user:email'],
            default => [],
        };

        return $clientRegistry
            ->getClient($clientName)
            ->redirect($scopes, []);
    }

    #[Route('/connect/{provider}/check', name: 'oauth_connect_check')]
    public function check(string $provider)
    {
        // /** @var \KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient $client */
        // $client = $clientRegistry->getClient('google_main');

        // try {
        //     /** @var \League\OAuth2\Client\Provider\GoogleUser $user */
        //     $user = $client->fetchUser();

        //     dd($user);
        // } catch (IdentityProviderException $e) {
        //     dd($e->getMessage());
        // }
        throw new \LogicException('Provider Authenticator Failed to Intercept');
    }
}