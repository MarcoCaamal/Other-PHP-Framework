<?php

namespace LightWeight\Server;

use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Storage\File;

/**
 * PHP native server that uses `$_SERVER` global.
 */
class PHPNativeServer implements ServerContract
{
    /**
     * Get files from `$_FILES` global.
     *
     * @return array<string, \LightWeight\Storage\File>
     */
    protected function uploadedFiles(): array
    {
        $files = [];
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                // Handle multiple file uploads
                $fileCount = count($file['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($file['error'][$i] === UPLOAD_ERR_OK) {
                        $files["{$key}_{$i}"] = new File(
                            file_get_contents($file['tmp_name'][$i]),
                            $file['type'][$i],
                            $file['name'][$i]
                        );
                    }
                }
            } elseif (isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                $files[$key] = new File(
                    file_get_contents($file['tmp_name']),
                    $file['type'],
                    $file['name']
                );
            }
        }
        return $files;
    }
    /**
     * Get request data from either POST or request body
     *
     * @return array
     */
    protected function requestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // For standard POST requests, use $_POST
        if ($method === 'POST' && !$isJson) {
            return $_POST;
        }

        // For other methods or JSON content, parse the request body
        $inputData = [];
        $input = file_get_contents('php://input');
        
        if (!empty($input)) {
            if ($isJson) {
                $decoded = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $inputData = $decoded;
                }
            } else {
                parse_str($input, $inputData);
            }
        }

        return $inputData;
    }
    /**
     * @inheritDoc
     */
    public function sendResponse(ResponseContract $response)
    {
        // PHP sends Content-Type header by default, but it has to be removed if
        // the response has not content. Content-Type header can't be removed
        // unless it is set to some value before.
        header('Content-Type: None');
        header_remove('Content-Type');

        $response->prepare();
        http_response_code($response->getStatus());
        foreach ($response->headers() as $header => $value) {
            header("$header: $value");
        }
        print($response->getContent());
    }
    /**
     * @inheritDoc
     */
    public function getRequest(): RequestContract
    {
        $request = new Request();
        
        // Set basic request properties
        $request->setUri(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH))
                ->setMethod(HttpMethod::from($_SERVER['REQUEST_METHOD'] ?? 'GET'))
                ->setQueryParameters($_GET)
                ->setPostData($this->requestData())
                ->setHost($_SERVER['HTTP_HOST'] ?? 'localhost');
        
        // Set URL scheme
        $https = $_SERVER['HTTPS'] ?? '';
        $request->setScheme(!empty($https) && $https !== 'off' ? 'https' : 'http');
        
        // Set port
        $port = (int)($_SERVER['SERVER_PORT'] ?? 80);
        $request->setPort($port);
        
        // Set IP address
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if (!$ip && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        $request->setIp($ip);
        
        // Set User Agent
        $request->setUserAgent($_SERVER['HTTP_USER_AGENT'] ?? null);
        
        // Set Referer
        $request->setReferer($_SERVER['HTTP_REFERER'] ?? null);
        
        // Check if AJAX request
        $request->setAjax(
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
        
        // Set content type
        $request->setContentType($_SERVER['CONTENT_TYPE'] ?? null);
        
        // Set headers
        $request->setHeaders(getallheaders());
        
        // Set uploaded files
        $request->setFiles($this->uploadedFiles());
        
        // Get raw content for non-form requests
        if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', [HttpMethod::GET->value, HttpMethod::POST->value]) || 
            ($request->contentType() && strpos($request->contentType(), 'application/json') !== false)) {
            $content = file_get_contents('php://input');
            $request->setContent($content);
            
            // Process JSON content
            if ($request->isContentType('application/json') && !empty($content)) {
                $jsonData = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                    $request->setPostData($jsonData);
                }
            }
        }
        
        return $request;
    }
    
    /**
     * Check if the current request needs to be redirected for HTTPS or WWW enforcement
     * 
     * @param RequestContract $request The current request
     * @return ResponseContract|null A redirect response if needed, null otherwise
     */
    public function checkRedirects(RequestContract $request): ?ResponseContract
    {
        $forceHttps = filter_var(config('server.force_https', false), FILTER_VALIDATE_BOOLEAN);
        $forceWww = filter_var(config('server.force_www', false), FILTER_VALIDATE_BOOLEAN);
        
        if (!$forceHttps && !$forceWww) {
            return null;
        }
        
        $scheme = $request->scheme();
        $host = $request->host();
        $needsRedirect = false;
        
        // New scheme and host that may be required
        $newScheme = $scheme;
        $newHost = $host;
        
        // Check if HTTPS enforcement is needed
        if ($forceHttps && $scheme !== 'https') {
            $newScheme = 'https';
            $needsRedirect = true;
        }
        
        // Check if WWW enforcement is needed
        if ($forceWww && !$this->hasWwwPrefix($host)) {
            $newHost = 'www.' . $this->stripWwwPrefix($host);
            $needsRedirect = true;
        }
          if ($needsRedirect) {
            // Build the redirect URL
            $redirectUrl = $newScheme . '://' . $newHost;
              // Add port only if it's not the standard port for the scheme
            $port = $request->port();
            // Don't include standard ports (80 for HTTP, 443 for HTTPS)
            // Don't include port 80 when redirecting from HTTP to HTTPS
            if ($port && 
                !(($newScheme === 'http' && $port === 80) || 
                  ($newScheme === 'https' && $port === 443) ||
                  ($newScheme === 'https' && $scheme === 'http' && $port === 80))) {
                $redirectUrl .= ':' . $port;
            }
            
            // Add path and query string
            $redirectUrl .= $request->path();
            $query = $request->query();
            if (!empty($query)) {
                $redirectUrl .= '?' . http_build_query($query);
            }
            
            // Create the redirect response
            $response = new \LightWeight\Http\Response();
            $response->setStatus(301); // Permanent redirect
            $response->setHeader('Location', $redirectUrl);
            return $response;
        }
        
        return null;
    }
    
    /**
     * Check if a hostname has the www prefix
     *
     * @param string $host The hostname to check
     * @return bool True if the hostname starts with www.
     */
    private function hasWwwPrefix(string $host): bool
    {
        return strpos($host, 'www.') === 0;
    }
    
    /**
     * Remove www prefix from a hostname if it exists
     *
     * @param string $host The hostname to process
     * @return string The hostname without www prefix
     */
    private function stripWwwPrefix(string $host): string
    {
        if ($this->hasWwwPrefix($host)) {
            return substr($host, 4);
        }
        return $host;
    }
}
