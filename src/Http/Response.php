<?php

namespace Junk\Http;

class Response {
    protected int $statusCode = 200;
    protected array $headers = [];
    protected ?string $content = null;

    public function getStatus(): int {
        return $this->statusCode;
    }
    public function setStatus(int $status): self {
        $this->statusCode = $status;
        return $this;
    }
    public function getHeaders(): array { 
        return $this->headers;
    }

    public function setHeaders(array $headers): self {
        foreach ($headers as $header => $value) {
            $this->headers[strtolower($header)] = $value;
        }
        return $this;
    }

    public function setHeader(string $header, string $value): self {
        $this->headers[strtolower($header)] = $value;
        return $this;
    }

    public function removeHeader(string $header): void {
        unset($this->headers[strtolower($header)]);
    }

    public function getContent(): ?string {
        return $this->content;
    }

	public function setContent(?string $content): self {
		$this->content = $content;
        return $this;
	}

    public function setContentType(string $contentType): self {
        $this->setHeader("Content-Type", $contentType);
        return $this;
    }

    public function prepare() {
        if(is_null($this->content)) {
            $this->removeHeader("Content-Type");
            $this->removeHeader("Content-Length");
            return;
        }
        $this->setHeader("Content-Length", strlen($this->content));
    }

    public static function json(array $data): self {
        return (new self())
            ->setContentType('application/json')
            ->setContent(json_encode($data));
    }

    public static function text(string $text): self {
        return (new self())
            ->setContentType('text/plain')
            ->setContent($text);
    }

    public static function redirect(string $uri): self {
        return (new self())
            ->setStatus(302)
            ->setHeader('Location', $uri);
    }
}