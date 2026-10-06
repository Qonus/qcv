<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\PositionRepository;
use App\Repository\UserRepository;
use App\Service\DropboxClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, TextareaType};
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\{Length, NotBlank};
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class SupportTicketController extends AbstractController
{
    private const POSITION_ROUTES = ['app_position_show', 'app_position_edit'];

    public function __construct(
        private TranslatorInterface $translator,
        private PositionRepository $positionRepository,
        private UserRepository $userRepository,
    ) {}

    #[Route('/support/ticket', name: 'app_support_ticket')]
    public function create(
        Request $request,
        #[CurrentUser] User $user,
        DropboxClient $dropbox,
    ): Response {
        $from = $request->query->get('from');
        $request->query->remove('from');
        $referer = $this->generateUrl($from, [...$request->query]);
        $form = $this->createFormBuilder(['priority' => 'Average'])
            ->add('summary', TextareaType::class, [
                'label' => 'support.summary.label',
                'constraints' => [new NotBlank(), new Length(max: 2000)],
                'attr' => ['rows' => 5],
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => ['support.priority.high' => 'High', 'support.priority.average' => 'Average', 'support.priority.low' => 'Low'],
                'label' => 'support.priority.label'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $d = $form->getData();
            $admins = array_map(fn($u) => $u->getEmail(), $this->userRepository->findByRole("ROLE_ADMIN"));
            $payload = [
                'reportedBy' => $this->esc(sprintf('%s %s (%s), %s',
                    $user->getFirstName(),
                    $user->getLastName(),
                    $user->getEmail(),
                    $user->getRole())),
                'position' => $this->getPosition($from, $request->query->get('id')),
                'link' => $request->getSchemeAndHttpHost().$referer,
                'priority' => $d['priority'],
                'adminEmails' => $admins,
                'summary' => $this->esc($d['summary']),
                'createdAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            ];
            if (in_array($from, self::POSITION_ROUTES)) {
                $position = $this->positionRepository->find($request->query->get('id'));
                $payload['position'] = $position ? $this->esc($position->getName()) : null;
            }
            $file = sprintf('/tickets/ticket-%s-%s.json', date('Ymd-His'), bin2hex(random_bytes(3)));
            try {
                $dropbox->upload($file, json_encode($payload,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
                $this->addFlash('success', $this->translator->trans('success.sent'));
                return $this->redirect($referer);
            } catch (\RuntimeException $e) {
                $this->addFlash('danger', 'Could not send the ticket: '.$e->getMessage());
            }
        }
        return $this->render('support/edit.html.twig', ['form' => $form, 'back' => $referer]);
    }

    private function getPosition(string $from, ?string $id): ?string {
        if (!in_array($from, self::POSITION_ROUTES)) return null;
        $position = $this->positionRepository->find($id);
        return $position ? $this->esc($position->getName()) : null;
    }

    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}