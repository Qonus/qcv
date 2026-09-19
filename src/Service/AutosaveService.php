<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AutosaveService {
    public function __construct(
        private EntityManagerInterface $em,
        private PropertyAccessorInterface $propertyAccessor) {

    }

    public function patchEntityFromRequest(object $entity, array $editableFields, Request $request): JsonResponse {
        $payload = $request->toArray();
        $field = $payload['field'] ?? null;
        if (!$field || !in_array($field, $editableFields, true)) {
            return new JsonResponse(['error'=>"Unknown or non-editable field: {$field}"]);
        }
        $clientVersion = $payload['version'] ?? null;
        if ($this->versionMismatch($clientVersion, $entity)) return $this->conflictResponse($entity, $field);
        $value = $payload['value'] ?? null;
        $this->updateValue($entity, $field, $value);
        return new JsonResponse(['version' => $this->getCurrentRowVersion($entity)]);
    }

    private function updateValue(object $entity, string $field, $value) {
        try {
            $this->propertyAccessor->setValue($entity, $field, $value);
            try {
                $this->em->flush();
            } catch (OptimisticLockException) {
                return $this->conflictResponse($entity, $field);
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }

    private function versionMismatch(int|null $version, object $entity) {
        $currentVersion = $this->getCurrentRowVersion($entity);
        return (!$currentVersion || $currentVersion != (int) $version);
    }

    private function getCurrentRowVersion(object $entity): ?int {
        return method_exists($entity, 'getVersion') ? $entity->getVersion() : null;
    }

    private function conflictResponse(object $entity, string $field): JsonResponse
    {
        $currentValue = $this->propertyAccessor->getValue($entity, $field);
        return new JsonResponse([
            'error' => 'version_conflict',
            'currentValue' => $currentValue,
            'currentVersion' => $this->getCurrentRowVersion($entity),
        ], Response::HTTP_CONFLICT);
    }

}