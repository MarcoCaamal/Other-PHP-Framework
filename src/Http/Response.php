<?php

namespace Junk\Http;

class Response {
    protected int $statusCode = 200;
    protected array $headers = [];
    protected ?string $content = null;

    public function getStatus(): int {
        return $this->statusCode;
    }
    public function setStatus(int $status): void {
        $this->statusCode = $status;
    }
    public function getHeaders(): array { 
        return $this->headers;
    }

    public function setHeaders(array $headers): void {
        foreach ($headers as $header => $value) {
            $this->headers[strtolower($header)] = $value;
        }
    }

    public function setHeader(string $header, string $value): void {
        $this->headers[strtolower($header)] = $value;
    }

    public function removeHeader(string $header): void {
        unset($this->headers[strtolower($header)]);
    }

    public function getContent(): ?string {
        return $this->content;
    }

	public function setContent(?string $content): void {
		$this->content = $content;
	}

    public function prepare() {
        if(is_null($this->content)) {
            $this->removeHeader("Content-Type");
            $this->removeHeader("Content-Length");
            return;
        }
        $this->setHeader("Content-Length", strlen($this->content));
    }
}