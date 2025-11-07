<?php
declare(strict_types=1);

namespace App\Storage;

final class Minio
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function presignPut(string $bucket, string $key, string $contentType, int $ttlSeconds = 900): string
    {
        return $this->presign('PUT', $bucket, $key, $ttlSeconds, ['Content-Type' => $contentType]);
    }

    public function presignGet(string $bucket, string $key, int $ttlSeconds = 900): string
    {
        return $this->presign('GET', $bucket, $key, $ttlSeconds, []);
    }

    private function presign(string $method, string $bucket, string $key, int $ttlSeconds, array $headers): string
    {
        $endpoint = rtrim($this->config['endpoint'], '/');
        $host = parse_url($endpoint, PHP_URL_HOST);
        $isHttps = str_starts_with($endpoint, 'https://');
        $scheme = $isHttps ? 'https' : 'http';
        $path = '/' . $bucket . '/' . ltrim($key, '/');
        $service = 's3';
        $region = $this->config['region'];
        $now = gmdate('Ymd\THis\Z');
        $date = substr($now, 0, 8);
        $credentialScope = sprintf('%s/%s/%s/aws4_request', $date, $region, $service);
        $query = [
            'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential' => $this->config['access_key'] . '/' . $credentialScope,
            'X-Amz-Date' => $now,
            'X-Amz-Expires' => (string)$ttlSeconds,
            'X-Amz-SignedHeaders' => 'host',
        ];
        $canonicalRequest = $method . "\n" . $path . "\n" . http_build_query($query, '', '&', PHP_QUERY_RFC3986) . "\nhost:" . $host . "\n\n" . 'host' . "\nUNSIGNED-PAYLOAD";
        $stringToSign = 'AWS4-HMAC-SHA256' . "\n" . $now . "\n" . $credentialScope . "\n" . hash('sha256', $canonicalRequest);
        $signature = hash_hmac('sha256', $stringToSign, $this->signingKey($date, $region, $service));
        $query['X-Amz-Signature'] = $signature;
        $url = sprintf('%s://%s%s?%s', $scheme, $host, $path, http_build_query($query, '', '&', PHP_QUERY_RFC3986));
        return $url;
    }

    private function signingKey(string $date, string $region, string $service): string
    {
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $this->config['secret_key'], true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
