<?php

namespace LightWeight\Http;

/**
 * HTTP Verbs.
 *
 */
enum HttpMethod: string
{
    case GET = "GET";
    case POST = "POST";
    case PUT = "PUT";
    case DELETE = "DELETE";
    case PATCH = "PATCH";
    case OPTIONS = "OPTIONS";
}
