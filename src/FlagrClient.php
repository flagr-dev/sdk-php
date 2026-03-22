<?php

declare(strict_types=1);

namespace Flagr;

class AuthenticationException extends \RuntimeException {}
class EvaluationException extends \RuntimeException {}

class FlagrClient
{
    private \CurlHandle $curl;

    public function __construct(
        private readonly string $sdkKey,
        private readonly string $baseUrl = 'https://api.flagr.dev',
    ) {
        $handle = curl_init();
        if ($handle === false) {
            throw new \RuntimeException('Failed to initialise cURL');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->sdkKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT_MS     => 5000,
            CURLOPT_URL            => rtrim($this->baseUrl, '/') . '/evaluate',
        ]);

        $this->curl = $handle;
    }

    public function isEnabled(string $flagKey, string $tenantId, bool $default = false): bool
    {
        $body = json_encode([
            'flagKey' => $flagKey,
            'context' => ['tenant_id' => $tenantId],
        ]);

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);

        $response = curl_exec($this->curl);

        if ($response === false) {
            throw new EvaluationException('cURL error: ' . curl_error($this->curl));
        }

        $status = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($status === 401) {
            throw new AuthenticationException('Invalid SDK key');
        }

        if ($status !== 200) {
            throw new EvaluationException("Unexpected status {$status}");
        }

        $data = json_decode((string) $response, true);

        return $data['value'] ?? $default;
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }
}
