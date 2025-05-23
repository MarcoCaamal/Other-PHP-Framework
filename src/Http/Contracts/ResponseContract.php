<?php

namespace LightWeight\Http\Contracts;

use LightWeight\Http\Response;

/**
 * Contract for HTTP Response handling.
 */
interface ResponseContract
{
    /**
     * Get Response HTTP status code.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set HTTP status code for this response.
     *
     * @param int $status
     * @return self
     */
    public function setStatus(int $status): self;

    /**
     * Get Response HTTP headers.
     *
     * @return array|string|null
     */
    public function headers(?string $key = null): array|string|null;

    /**
     * Set HTTP headers by array for this response.
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self;

    /**
     * Set HTTP header for this response.
     *
     * @param string $header
     * @param string $value
     * @return self
     */
    public function setHeader(string $header, string $value): self;

    /**
     * Remove HTTP header for this response.
     * @param string $header
     * @return void
     */
    public function removeHeader(string $header): void;

    /**
     * Get content from the current response.
     *
     * @return string|null
     */
    public function getContent(): ?string;

    /**
     * Set content for current response.
     *
     * @param string|null $content
     * @return self
     */
    public function setContent(?string $content): self;

    /**
     * Set the `Content-Type` for current response.
     *
     * @param string $contentType
     * @return self
     */
    public function setContentType(string $contentType): self;

    /**
     * Prepare the current response to be sent to the client.
     *
     * @return void
     */
    public function prepare();

    /**
     * Add validation errors to the response.
     *
     * @param array $errors
     * @param int $status
     * @return self
     */
    public function withErrors(array $errors, int $status = 400): self;
}
