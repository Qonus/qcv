<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class SalesforceVoter extends Voter
{
    public const SYNC = 'SALESFORCE_SYNC';

    public function __construct(private AccessDecisionManagerInterface $accessDecisionManager,) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::SYNC && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) return true;
        $current = $token->getUser();
        return $current instanceof User
            && ($current->getId() === $subject->getId());
    }
}