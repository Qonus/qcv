<?php
namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SalesforceException extends \RuntimeException {}

final class SalesforceClient
{
    // TODO: Untested in deployment
    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache,
        private string $domain,
        private string $clientId,
        private string $clientSecret,
        private string $apiVersion,
    ) {}

    /** @return string new record Id */
    public function create(string $sobject, array $fields): string
    {
        return $this->request('POST', "sobjects/$sobject", ['json' => $fields])['id'];
    }

    public function update(string $sobject, string $id, array $fields): void
    {
        $this->request('PATCH', "sobjects/$sobject/$id", ['json' => $fields]);
    }

    public function delete(string $sobject, string $id): void
    {
        $this->request('DELETE', "sobjects/$sobject/$id");
    }

    private function request(string $method, string $path, array $options = [], bool $retry = true): array
    {
        $options['headers']['Authorization'] = 'Bearer '.$this->token();
        // Dev orgs have duplicate rules on; without this, re-testing with the same email fails
        // TODO: Untested without this line
        $options['headers']['Sforce-Duplicate-Rule-Header'] = 'allowSave=true';

        try {
            $response = $this->http->request($method, "{$this->domain}/services/data/{$this->apiVersion}/$path", $options);
            $status = $response->getStatusCode();
            $body = $response->getContent(false);
        } catch (ExceptionInterface $e) {
            throw new SalesforceException('Cannot reach Salesforce: '.$e->getMessage(), 0, $e);
        }

        if ($status === 401 && $retry) {
            $this->cache->delete('salesforce_access_token');
            return $this->request($method, $path, $options, false);
        }

        $data = $body === '' ? [] : (json_decode($body, true) ?? []);
        if ($status >= 400) {
            $msg = array_is_list($data)
                ? implode('; ', array_map(fn ($e) => ($e['errorCode'] ?? '').': '.($e['message'] ?? ''), $data))
                : "HTTP $status";
            throw new SalesforceException($msg ?: "HTTP $status", $status);
        }
        return $data;
    }

    private function token(): string
    {
        return $this->cache->get('salesforce_access_token', function (ItemInterface $item): string {
            $item->expiresAfter(1500);
            try {
                $data = $this->http->request('POST', $this->domain.'/services/oauth2/token', ['body' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]])->toArray(false);
            } catch (ExceptionInterface $e) {
                throw new SalesforceException('Salesforce auth failed: '.$e->getMessage(), 0, $e);
            }
            if (!isset($data['access_token'])) {
                throw new SalesforceException('Salesforce auth failed: '.($data['error_description'] ?? $data['error'] ?? 'unknown'));
            }
            return $data['access_token'];
        });
    }
}