<?php
namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GoogleController extends AbstractController
{
    #[Route(path:"/connect/google", name:"connect_google_start")]
    public function connectAction(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('google_main')
            ->redirect([
                'profile', 'email'
            ], []);
    }

    #[Route(path:"/connect/google/check", name:"connect_google_check")]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
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
    }
}