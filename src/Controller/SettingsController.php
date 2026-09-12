<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\LocaleSwitcher;

class SettingsController extends AbstractController
{

    #[Route('/settings/theme/{theme}', name: 'app_switch_theme')]
    public function switchTheme(string $theme, Request $request,
        EntityManagerInterface $entityManager): Response
    {
        if (!in_array($theme, ['light', 'dark'], true)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUser();

        $request->cookies->set('theme', $theme);
        if ($user instanceof User) {
            $themeEnum = Theme::tryFrom($theme);
            if ($themeEnum === null) {
                throw $this->createNotFoundException('Invalid theme');
            }
            $user->setTheme($themeEnum);
            $entityManager->flush();
        }

        $targetUrl = $request->headers->get('referer') ?: '/';
        $response = new RedirectResponse($targetUrl);
        $cookie = Cookie::create('theme')
            ->withValue($theme)
            ->withExpires(new \DateTime('+1 year'))
            ->withPath('/')
            ->withHttpOnly(false);

        $response->headers->setCookie($cookie);

        return $response;
    }

    #[Route('/settings/locale/{locale}', name: 'app_switch_locale')]
    public function switchLocale(
        string $locale,
        Request $request,
        EntityManagerInterface $entityManager,
        ): Response
    {
        if (!in_array($locale, ['en', 'ru'])) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUser();

        $request->getSession()->set('_locale', $locale);
        if ($user instanceof User) {
            $user->setLocale($locale);
            $entityManager->flush();
        }
        
        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_home'));
    }
}