<?php

namespace App\Security;

use App\Entity\CV;
use App\Entity\Position;
use App\Entity\User;
use App\Service\CandidateService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class PositionVoter extends Voter {
    const VIEW = 'view';
    const EDIT = 'edit';
    const APPLY = 'apply';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
        private CandidateService $candidateService,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool {
        return $subject instanceof Position;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
        /** @var Position $position */
        $position = $subject;
        return match ($attribute) {
            self::VIEW => $this->canView($position, $token, $vote),
            self::APPLY => $this->canApply($position, $token, $vote),
            self::EDIT => $this->canEdit($position, $token, $vote),
        };
    }

    private function canApply(Position $position, TokenInterface $token, ?Vote $vote): bool
    {
        if (!$this->accessDecisionManager->decide($token, ['ROLE_CANDIDATE'])) {
            return false;
        }
        if (!$this->canView($position, $token, $vote)) return false;
        if (!$this->candidateService->builtinValuesExist($token->getUser())){
            $vote?->addReason(sprintf(
                "The logged in user's builtin values are missing",
            ));
            return false;
        }
        return true;
    }

    private function canView(Position $position, TokenInterface $token, ?Vote $vote): bool {
        if ($this->accessDecisionManager->decide($token, ['ROLE_RECRUITER'])) {
            return true;
        }
        // TODO: Loop through Access Rules of $position, and call accessRule function from Candidate Service on each one.
        return true;
    }

    private function canEdit(Position $position, TokenInterface $token, ?Vote $vote): bool {
        if ($this->accessDecisionManager->decide($token, ['ROLE_RECRUITER'])) {
            return true;
        }
        return false;
    }
}