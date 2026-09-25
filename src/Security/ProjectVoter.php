<?php

namespace App\Security;

use App\Entity\Project;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class ProjectVoter extends Voter {
    const VIEW = 'view';
    const EDIT = 'edit';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool {
        return $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
        return true;
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }
        if ($this->accessDecisionManager->decide($token, ['ROLE_RECRUITER']) && $attribute == self::VIEW) {
            return true;
        }
        /**
         * @var Project
         */
        $project = $subject;
        return $project->getCandidate() === $token->getUser();
    }
}