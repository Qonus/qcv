<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted("ROLE_ADMIN")]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private TranslatorInterface $translator
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('user.singular')
            ->setEntityLabelInPlural('user.plural');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email', 'user.email.label'),
            ChoiceField::new('role', 'user.role.label')
                ->setChoices([
                    'user.role.candidate' => 'ROLE_CANDIDATE',
                    'user.role.recruiter' => 'ROLE_RECRUITER',
                    'user.role.admin' => 'ROLE_ADMIN',
                ])
                ->renderExpanded(),
            ChoiceField::new('status', 'user.status.label')
                ->setChoices([
                    'user.status.blocked' => 'blocked',
                    'user.status.verified' => 'verified',
                    'user.status.unverified' => 'unverified',
                ])
                ->renderAsBadges([
                    'blocked' => 'danger',
                    'verified' => 'success',
                    'unverified' => 'warning',
                ]),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $blockBatch = Action::new('batchBlock', 'user.actions.block', 'fa fa-ban')
            ->linkToCrudAction('batchBlock')
            ->addCssClass('btn btn-warning');

        $unblockBatch = Action::new('batchUnblock', 'user.actions.unblock', 'fa fa-unlock')
            ->linkToCrudAction('batchUnblock')
            ->addCssClass('btn btn-success');
        
        $cleanBatch = Action::new('batchCleanUnverified', 'user.actions.clean', 'fa fa-broom')
            ->linkToCrudAction('batchCleanUnverified')
            ->addCssClass('btn btn-danger');

        return $actions
            ->disable(Action::NEW)
            ->addBatchAction($blockBatch)
            ->addBatchAction($unblockBatch)
            ->addBatchAction($cleanBatch);
    }

    #[AdminRoute(path: '/batch-block', name: 'batchBlock')]
    public function batchBlock(BatchActionDto $batchActionDto): Response
    {
        $this->userRepository->updateBlockByIds($batchActionDto->getEntityIds(), true);
        $this->entityManager->flush();
        return $this->getRedirectResponse();
    }

    #[AdminRoute(path: '/batch-unblock', name: 'batchUnblock')]
    public function batchUnblock(BatchActionDto $batchActionDto): Response
    {
        $this->userRepository->updateBlockByIds($batchActionDto->getEntityIds(), false);
        $this->entityManager->flush();
        return $this->getRedirectResponse();
    }

    #[AdminRoute(path: '/batch-clean', name: 'batchCleanUnverified')]
    public function batchCleanUnverified(BatchActionDto $batchActionDto): Response
    {
        $this->userRepository->deleteByIds($batchActionDto->getEntityIds(), true);
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