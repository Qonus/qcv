<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute; // 1. ADD THIS IMPORT
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private EntityManagerInterface $entityManager
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email'),
            ChoiceField::new('roles')
                ->setChoices([
                    'Candidate' => 'ROLE_CANDIDATE',
                    'Recruiter' => 'ROLE_RECRUITER',
                    'Admin' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
            ChoiceField::new('status', 'Status')
                ->setChoices([
                    'Blocked' => 'blocked',
                    'Verified' => 'verified',
                    'Unverified' => 'unverified',
                ])
                ->renderAsBadges([
                    'blocked' => 'danger',     // Red badge
                    'verified' => 'success',   // Green badge
                    'unverified' => 'warning', // Yellow badge
                ]),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $blockBatch = Action::new('batchBlock', 'Block', 'fa fa-ban')
            ->linkToCrudAction('batchBlock')
            ->addCssClass('btn btn-warning');

        $unblockBatch = Action::new('batchUnblock', 'Unblock', 'fa fa-unlock')
            ->linkToCrudAction('batchUnblock')
            ->addCssClass('btn btn-success');

        $cleanBatch = Action::new('batchClean', 'Clean Unverified', 'fa fa-trash')
            ->linkToCrudAction('batchCleanUnverified')
            ->addCssClass('btn btn-danger');

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->addBatchAction($blockBatch)
            ->addBatchAction($unblockBatch)
            ->addBatchAction($cleanBatch);
    }

    // 2. ADD #[AdminRoute] ATTRIBUTE TO ALL CUSTOM ACTIONS
    #[AdminRoute(path: '/batch-block', name: 'batchBlock')]
    public function batchBlock(BatchActionDto $batchActionDto): Response
    {
        $className = $batchActionDto->getEntityFqcn();
        
        foreach ($batchActionDto->getEntityIds() as $id) {
            $user = $this->entityManager->find($className, $id);
            if ($user) {
                $user->setIsBlocked(true);
            }
        }
        
        $this->entityManager->flush();

        return $this->getRedirectResponse();
    }

    #[AdminRoute(path: '/batch-unblock', name: 'batchUnblock')]
    public function batchUnblock(BatchActionDto $batchActionDto): Response
    {
        $className = $batchActionDto->getEntityFqcn();
        
        foreach ($batchActionDto->getEntityIds() as $id) {
            $user = $this->entityManager->find($className, $id);
            if ($user) {
                $user->setIsBlocked(false);
            }
        }
        
        $this->entityManager->flush();

        return $this->getRedirectResponse();
    }

    #[AdminRoute(path: '/batch-clean-unverified', name: 'batchCleanUnverified')]
    public function batchCleanUnverified(BatchActionDto $batchActionDto): Response
    {
        $className = $batchActionDto->getEntityFqcn();
        
        foreach ($batchActionDto->getEntityIds() as $id) {
            $user = $this->entityManager->find($className, $id);
            
            if ($user && !$user->isVerified()) {
                $this->entityManager->remove($user);
            }
        }
        
        $this->entityManager->flush();

        return $this->getRedirectResponse();
    }

    private function getRedirectResponse(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}