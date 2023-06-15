<?php

namespace App\Service;

/**
 * Class TokenService
 * This class is used to generate and verify tokens.
 *
 * @see TokenServiceInterface
 *
 * @method string generate(array $header, array $payload, string $secret, int $validity = 3600): string
 * @method bool isValid(string $token, string $secret): bool
 * @method array getPayload(string $token): array
 * @method array getHeader(string $token): array
 * @method bool isExpired(string $token): bool
 */
class TokenService implements TokenServiceInterface
{
    public function generate(array $header, array $payload, string $secret, int $validity = 3600): string
    {
        if ($validity > 0) {
            $now = new \DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }
        $base64Header = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $base64Payload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $base64Header.'.'.$base64Payload, base64_encode($secret), true);
        $base64Signature = $this->base64UrlEncode($signature);

        return $base64Header.'.'.$base64Payload.'.'.$base64Signature;
    }

    public function isValid(string $token, string $secret): bool
    {
        return 1 === preg_match('/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/', $token);
    }

    public function getPayload(string $token): array
    {
        $payload = $this->splitToken($token, 1);

        return $this->decodeAndValidateJson($payload, 'payload');
    }

    public function getHeader(string $token): array
    {
        $header = $this->splitToken($token, 0);

        return $this->decodeAndValidateJson($header, 'header');
    }

    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);
        $now = new \DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    public function check(string $token, string $secret): bool
    {
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);
        $checkToken = $this->generate($header, $payload, $secret, 0);

        return hash_equals($token, $checkToken);
    }

    /**
     * Splits the token into its parts.
     *
     * @param string $token the token
     * @param int    $index the index of the part to retrieve
     *
     * @return string the part of the token
     */
    private function splitToken(string $token, int $index): string
    {
        $parts = explode('.', $token);
        if (!isset($parts[$index])) {
            throw new \InvalidArgumentException('Invalid token structure.');
        }

        return $parts[$index];
    }

    /**
     * Encodes a string to base64url.
     *
     * @param string $input the string to encode
     *
     * @return string the encoded string
     */
    private function base64UrlEncode(string $input): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($input));
    }

    /**
     * Decodes a base64url encoded string.
     *
     * @param string $input the string to decode
     * @param string $type  the type of the string to decode
     *
     * @return array<string, mixed>
     */
    private function decodeAndValidateJson(string $input, string $type): array
    {
        $decodedInput = base64_decode($input);
        if (!is_string($decodedInput)) {
            throw new \InvalidArgumentException("Could not decode {$type}");
        }
        $result = json_decode($decodedInput, true);
        if (!is_array($result)) {
            throw new \RuntimeException("Failed to decode JSON to array for {$type}");
        }

        return $result;
    }
}
