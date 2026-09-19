<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UploadService {
    public function __construct(private Cloudinary $cloudinary) {}
    public function uploadImage($imageFile): ?string {
        if (!$imageFile) throw new NotFoundHttpException('No file uploaded');
        $result = $this->cloudinary->uploadApi()->upload(
            $imageFile->getRealPath(), [
            'folder' => 'profile_photos',
            'transformation' => [
                'width' => 300,
                'height' => 300,
                'crop' => 'fill',
                'gravity' => 'face',
            ],
        ]);
        $imageUrl = $result['secure_url'];
        return $imageUrl;
    }
}