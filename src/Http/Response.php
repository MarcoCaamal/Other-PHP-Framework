<?php

namespace Junk\Http;

/**
 * This class respresnts a HTTP Response that it will be sending to the client.
 */
class Response
{
    /**
     * Response HTTP status code.
     *
     * @var int
     */
    protected int $statusCode = 200;
    /**
     * Response HTTP headers.
     *
     * @var array
     */
    protected array $headers = [];
    /**
     * Response content.
     *
     * @var ?string
     */
    protected ?string $content = null;
    /**
     * Get Response HTTP status code.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->statusCode;
    }
    /**
     * Set HTTP status code for this response.
     *
     * @param int $status
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->statusCode = $status;
        return $this;
    }
    /**
     * Get Response HTTP headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    /**
     * Set HTTP headers by array for this response.
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $header => $value) {
            $this->headers[strtolower($header)] = $value;
        }
        return $this;
    }
    /**
     * Set HTTP header for this response.
     *
     * @param string $header
     * @param string $value
     * @return self
     */
    public function setHeader(string $header, string $value): self
    {
        $this->headers[strtolower($header)] = $value;
        return $this;
    }
    /**
     * Remove HTTP header for this response.
     * @param string $header
     * @return void
     */
    public function removeHeader(string $header): void
    {
        unset($this->headers[strtolower($header)]);
    }
    /**
     * Get content from the current response.
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }
    /**
     * Set content for current response.
     *
     * @param mixed $content
     * @return self
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }
    /**
     * Set the `Content-Type` for current response.
     *
     * @param string $contentType
     * @return self
     */
    public function setContentType(string $contentType): self
    {
        $this->setHeader("Content-Type", $contentType);
        return $this;
    }
    /**
     * Prepare the current response to be sent to the client.
     *
     * @return void
     */
    public function prepare()
    {
        if (is_null($this->content)) {
            $this->removeHeader("Content-Type");
            $this->removeHeader("Content-Length");
            return;
        }
        $this->setHeader("Content-Length", strlen($this->content));
    }
    /**
     * Create a new JSON response.
     * 
     * @param array $data
     * @return \Junk\Http\Response
     */
    public static function json(array $data): self
    {
        return (new self())
            ->setContentType('application/json')
            ->setContent(json_encode($data));
    }
    /**
     * Create a new plain text response.
     * 
     * @param string $text
     * @return \Junk\Http\Response
     */
    public static function text(string $text): self
    {
        return (new self())
            ->setContentType('text/plain')
            ->setContent($text);
    }
    /**
     * Create a new redirect response.
     * 
     * @param string $uri
     * @return \Junk\Http\Response
     */
    public static function redirect(string $uri): self
    {
        return (new self())
            ->setStatus(302)
            ->setHeader('Location', $uri);
    }
}
