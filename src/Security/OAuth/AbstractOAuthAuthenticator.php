<?php
namespace App\Security\OAuth;

use App\Entity\OAuthAccount;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

abstract class AbstractOAuthAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private Security $security
    ) {}
    abstract protected function getClientKey(): string;
    abstract protected function getEmail(ResourceOwnerInterface $resourceOwner): string;
    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient($this->getClientKey());
        $accessToken = $this->fetchAccessToken($client);
        
        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                $oauthUser = $client->fetchUserFromToken($accessToken);
                $email = $this->getEmail($oauthUser);
                // Search OAuth User
                $oauthRepo = $this->entityManager->getRepository(OAuthAccount::class);
                $providerName = explode('_', $this->getClientKey())[0];
                $providerUserId = $oauthUser->getId();
                $oauthAccount = $oauthRepo->findOneBy([
                    'provider' => $providerName,
                    'providerUserId' => $providerUserId,
                ]);
                if ($oauthAccount) {
                    return $oauthAccount->getUser();
                }

                // Search for the User
                $userRepo = $this->entityManager->getRepository(User::class);
                $user = $email ? $userRepo->findOneBy(['email' => $email]) : null;
                if (!$user) {
                    // Create User
                    $user = new User();
                    $user->setEmail($email);
                    // $user->setRoles(['ROLE_CANDIDATE']);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }

                // Create OAuth Account
                $newOauth = new OAuthAccount();
                $newOauth->setProvider($providerName);
                $newOauth->setProviderUserId($providerUserId);
                $newOauth->setUser($user);
                $this->entityManager->persist($newOauth);
                $this->entityManager->flush();

                // Login
                $this->security->login($user, 'form_login', 'main');
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }
        return new RedirectResponse($this->router->generate('app_login'));
    }
}