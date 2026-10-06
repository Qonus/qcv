<?php
namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DropboxClient
{
    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache,
        private string $appKey,
        private string $appSecret,
        private string $refreshToken,
    ) {}

    public function upload(string $path, string $contents): void
    {
        try {
            $response = $this->http->request('POST', 'https://content.dropboxapi.com/2/files/upload', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken(),
                    'Dropbox-API-Arg' => json_encode(['path' => $path, 'mode' => 'add', 'autorename' => true]),
                    'Content-Type' => 'application/octet-stream',
                ],
                'body' => $contents,
            ]);
            if ($response->getStatusCode() >= 400) {
                throw new \RuntimeException('Dropbox upload failed: '.$response->getContent(false));
            }
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Dropbox request failed: '.$e->getMessage(), 0, $e);
        }
    }

    private function accessToken(): string
    {
        return $this->cache->get('dropbox_access_token', function (ItemInterface $item): string {
            $data = $this->http->request('POST', 'https://api.dropbox.com/oauth2/token', [
                'auth_basic' => [$this->appKey, $this->appSecret],
                'body' => ['grant_type' => 'refresh_token', 'refresh_token' => $this->refreshToken],
            ])->toArray();
            $item->expiresAfter(max(60, ($data['expires_in'] ?? 14400) - 300));
            return $data['access_token'];
        });
    }
}