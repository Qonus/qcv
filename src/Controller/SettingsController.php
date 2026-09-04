<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        if ($user instanceof User) {
            $themeEnum = Theme::tryFrom($theme);
            if ($themeEnum === null) {
                throw $this->createNotFoundException('Invalid theme');
            }
            $user->setTheme($themeEnum);
            $entityManager->flush();
        }
        $request->getSession()->set('theme', $theme);

        return $this->redirect($request->headers->get('referer') ?: '/');
    }

    // #[Route('/switch-locale/{locale}', name: 'app_switch_locale')]
    // public function switchLocale(string $locale, Request $request, EntityManagerInterface $em): Response
    // {
    //     if (in_array($locale, ['en', 'ru'])) {
            
    //         $request->getSession()->set('_locale', $locale);
            
    //         if ($user = $this->getUser()) {
    //             // $user->setLocale($locale);
    //             // $em->flush();
    //         }
    //     }
        
    //     return $this->redirect($request->headers->get('referer') ?: '/');
    // }
}