<?php
/**
 * This file is part of Swow
 *
 * @link    https://github.com/swow/swow
 * @contact twosee <twosee@php.net>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 */

declare(strict_types=1);

namespace Swow\Http2;

/**
 * HTTP/2 protocol constants and utilities
 */
class Http2
{
    /**
     * HTTP/2 connection preface
     */
    public const PREFACE = "PRI * HTTP/2.0\r\n\r\nSM\r\n\r\n";
    
    /**
     * Default header table size
     */
    public const DEFAULT_HEADER_TABLE_SIZE = 4096;
    
    /**
     * Default enable push
     */
    public const DEFAULT_ENABLE_PUSH = 1;
    
    /**
     * Default max concurrent streams
     */
    public const DEFAULT_MAX_CONCURRENT_STREAMS = 100;
    
    /**
     * Default initial window size
     */
    public const DEFAULT_INITIAL_WINDOW_SIZE = 65535;
    
    /**
     * Default max frame size
     */
    public const DEFAULT_MAX_FRAME_SIZE = 16384;
    
    /**
     * Default max header list size
     */
    public const DEFAULT_MAX_HEADER_LIST_SIZE = 65536;
    
    /**
     * Maximum stream ID
     */
    public const MAX_STREAM_ID = 0x7FFFFFFF;
    
    /**
     * Default stream priority
     */
    public const DEFAULT_PRIORITY = 16;
    
    /**
     * Default stream weight
     */
    public const DEFAULT_WEIGHT = 16;
    
    /**
     * HTTP/2 version string
     */
    public const VERSION = '2.0';
    
    /**
     * HTTP/2 protocol identifier for ALPN
     */
    public const ALPN_PROTOCOL_ID = 'h2';
    
    /**
     * HTTP/2 over cleartext protocol identifier for ALPN
     */
    public const ALPN_PROTOCOL_ID_CLEARTEXT = 'h2c';
    
    /**
     * Check if a header name is a pseudo-header
     * 
     * @param string $name Header name
     * @return bool Whether the header is a pseudo-header
     */
    public static function isPseudoHeader(string $name): bool
    {
        return $name[0] === ':';
    }
    
    /**
     * Convert HTTP/1.x headers to HTTP/2 headers
     * 
     * @param array<string, string|array<string>> $headers HTTP/1.x headers
     * @return array<array<string>> HTTP/2 headers
     */
    public static function packHeaders(array $headers): array
    {
        $result = [];
        
        foreach ($headers as $name => $value) {
            $name = strtolower($name);
            
            if (is_array($value)) {
                foreach ($value as $v) {
                    $result[] = [$name, (string) $v];
                }
            } else {
                $result[] = [$name, (string) $value];
            }
        }
        
        return $result;
    }
    
    /**
     * Convert HTTP/2 headers to HTTP/1.x headers
     * 
     * @param array<array<string>> $headers HTTP/2 headers
     * @return array<string, array<string>> HTTP/1.x headers
     */
    public static function unpackHeaders(array $headers): array
    {
        $result = [];
        $pseudoHeaders = [];
        
        foreach ($headers as [$name, $value]) {
            if (self::isPseudoHeader($name)) {
                $pseudoHeaders[$name] = $value;
                continue;
            }
            
            if (!isset($result[$name])) {
                $result[$name] = [];
            }
            
            $result[$name][] = $value;
        }
        
        return ['headers' => $result, 'pseudo' => $pseudoHeaders];
    }
    
    /**
     * Create HTTP/2 request pseudo-headers from HTTP/1.x headers
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @param array<string, string|array<string>> $headers HTTP/1.x headers
     * @return array<string, string> HTTP/2 pseudo-headers
     */
    public static function createRequestPseudoHeaders(string $method, string $path, array $headers): array
    {
        $scheme = 'https';
        $authority = '';
        
        // Extract host/authority from headers
        if (isset($headers['Host'])) {
            $authority = $headers['Host'];
        } elseif (isset($headers['host'])) {
            $authority = $headers['host'];
        }
        
        // Create pseudo-headers
        return [
            ':method' => $method,
            ':path' => $path,
            ':scheme' => $scheme,
            ':authority' => is_array($authority) ? (string) ($authority[0] ?? '') : (string) $authority,
        ];
    }
    
    /**
     * Create HTTP/2 response pseudo-headers
     * 
     * @param int $statusCode HTTP status code
     * @return array<string, string> HTTP/2 pseudo-headers
     */
    public static function createResponsePseudoHeaders(int $statusCode): array
    {
        return [
            ':status' => (string) $statusCode,
        ];
    }
    
    /**
     * Merge pseudo-headers with regular headers
     * 
     * @param array<string, string> $pseudoHeaders HTTP/2 pseudo-headers
     * @param array<string, string|array<string>> $headers HTTP/1.x headers
     * @return array<array<string>> HTTP/2 headers
     */
    public static function mergeHeaders(array $pseudoHeaders, array $headers): array
    {
        $result = [];
        
        // Add pseudo-headers first (order matters in HTTP/2)
        foreach ($pseudoHeaders as $name => $value) {
            $result[] = [$name, $value];
        }
        
        // Add regular headers
        return array_merge($result, self::packHeaders($headers));
    }
}