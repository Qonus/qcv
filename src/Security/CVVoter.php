<?php

namespace App\Security;

use App\Entity\CV;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class CVVoter extends Voter {
    const VIEW = 'view';
    const EDIT = 'edit';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool {
        return $subject instanceof CV;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }
        if ($this->accessDecisionManager->decide($token, ['ROLE_RECRUITER']) && $attribute == self::VIEW) {
            return true;
        }
        return $subject->getCandidate() === $token->getUser();
    }
}