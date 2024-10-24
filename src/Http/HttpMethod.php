<?php

namespace SMFramework\Http;

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
}
