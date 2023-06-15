<?php

namespace App\Service;

/**
 * Interface TokenServiceInterface.
 */
interface TokenServiceInterface
{
    /**
     * Generates a token.
     *
     * @param array<string, mixed> $header   contains the type of the token and the hashing algorithm
     * @param array<string, mixed> $payload  contains the claims, which are statements about the user
     * @param string               $secret   the secret key used to sign the token
     * @param int                  $validity The validity period of the token in seconds. Defaults to 3600 (1 hours).
     *
     * @return string the generated token
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 3600): string;

    /**
     * Validates the structure of the token.
     *
     * @param string $token  the token
     * @param string $secret the secret key used to sign the token
     *
     * @return bool true if the token is valid, false otherwise
     */
    public function isValid(string $token, string $secret): bool;

    /**
     * Retrieves the payload of the token.
     *
     * @param string $token the token
     *
     * @return array<string, mixed> the payload of the token
     */
    public function getPayload(string $token): array;

    /**
     * Retrieves the header of the token.
     *
     * @param string $token the token
     *
     * @return array<string, mixed> the header of the token
     */
    public function getHeader(string $token): array;

    /**
     * Checks if the token is expired.
     *
     * @param string $token the token
     *
     * @return bool true if the token is expired, false otherwise
     */
    public function isExpired(string $token): bool;

    /**
     * Checks if the token is valid.
     * This method checks if the token is valid, not if it is expired.
     * Use the isExpired method for that.
     *
     * @param string $token  the token
     * @param string $secret the secret key used to sign the token
     */
    public function check(string $token, string $secret): bool;
}
