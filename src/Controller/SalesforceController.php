<?php
namespace App\Controller;

use App\Entity\User;
use App\Service\SalesforceClient;
use App\Service\SalesforceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType, EmailType, TelType, TextareaType, TextType, UrlType};
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\{Length, NotBlank};
use Symfony\Contracts\Translation\TranslatorInterface;

class SalesforceController extends AbstractController
{
    public function __construct(private TranslatorInterface $translator) {}

    #[Route('/profile/salesforce/{id}', name: 'app_salesforce_sync')]
    #[IsGranted('SALESFORCE_SYNC', subject: 'user')]
    public function sync(User $user, Request $request, SalesforceClient $sf, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder([
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'role' => $this->roleLabel($user),
                'newsletter' => true,
            ])
            ->add('firstName', TextType::class, ['disabled' => true, 'label' => 'attribute.builtin.firstName'])
            ->add('lastName', TextType::class, ['disabled' => true, 'label' => 'attribute.builtin.lastName'])
            ->add('email', EmailType::class, ['disabled' => true])
            ->add('role', TextType::class, ['disabled' => true, 'label' => 'user.role.label'])

            ->add('company', TextType::class, ['label' => 'user.salesforce.company',
                'constraints' => [new NotBlank(), new Length(max: 255)]])
            ->add('website', UrlType::class, ['required' => false, 'label' => 'user.salesforce.website', 'default_protocol' => 'https',
                'constraints' => [new Length(max: 255)]])
            ->add('phone', TelType::class, ['required' => false, 'label' => 'user.salesforce.phone', 'constraints' => [new Length(max: 40)]])
            ->add('jobTitle', TextType::class, ['required' => false, 'label' => 'user.salesforce.job',
                'constraints' => [new Length(max: 128)]])
            ->add('city', TextType::class, ['required' => false, 'constraints' => [new Length(max: 40)], 'label' => 'user.salesforce.city'])
            ->add('notes', TextareaType::class, ['required' => false, 'constraints' => [new Length(max: 5000)], 'label' => 'user.salesforce.notes'])
            ->add('newsletter', CheckboxType::class, ['required' => false, 'label' => 'user.salesforce.news_checkbox'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $d = $form->getData();
            try {
                if ($user->getSalesforceContactId()) {
                    $sf->update('Account', $user->getSalesforceAccountId(), $this->accountFields($d));
                    $sf->update('Contact', $user->getSalesforceContactId(), $this->contactFields($d));
                    $this->addFlash('success', $this->translator->trans('success.updated'));
                } else {
                    $accountId = $sf->create('Account', $this->accountFields($d));
                    try {
                        $contactId = $sf->create('Contact', $this->contactFields($d) + ['AccountId' => $accountId]);
                    } catch (SalesforceException $e) {
                        $sf->delete('Account', $accountId);
                        throw $e;
                    }
                    $user->setSalesforceAccountId($accountId);
                    $user->setSalesforceContactId($contactId);
                    $this->addFlash('success', $this->translator->trans('success.created'));
                }
                // $user->setSalesforceSyncedAt(new \DateTimeImmutable());
                $em->flush();

                return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
            } catch (SalesforceException $e) {
                $this->addFlash('danger', 'Salesforce error: '.$e->getMessage());
            }
        }

        return $this->render('salesforce/edit.html.twig', ['form' => $form, 'user' => $user]);
    }

    private function accountFields(array $d): array
    {
        return $this->clean([
            'Name' => $d['company'],
            'Website' => $d['website'],
            'Phone' => $d['phone'],
            'BillingCity' => $d['city'],
            'Description' => 'Created from the CV Management site',
        ]);
    }

    private function contactFields(array $d): array
    {
        return $this->clean([
            'FirstName' => $d['firstName'],
            'LastName' => $d['lastName'],
            'Email' => $d['email'],
            'Title' => $d['jobTitle'],
            'Phone' => $d['phone'],
            'MailingCity' => $d['city'],
            'HasOptedOutOfEmail' => !$d['newsletter'],
            'Description' => trim("Site role: {$d['role']}\n".($d['notes'] ?? '')),
        ]);
    }

    private function clean(array $f): array
    {
        return array_filter($f, static fn ($v) => $v !== null && $v !== '');
    }

    private function roleLabel(User $u): string
    {
        $roles = $u->getRoles();
        return $this->translator->trans('user.role.' . match (true) {
            in_array('ROLE_ADMIN', $roles, true) => 'admin',
            in_array('ROLE_RECRUITER', $roles, true) => 'recruiter',
            default => 'candidate',
        });
    }
}