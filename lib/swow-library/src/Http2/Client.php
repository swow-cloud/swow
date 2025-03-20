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

use Swow\Buffer;
use Swow\Socket;
use Swow\Http\Message\Http2StreamEntity;
use Swow\Http\Message\ResponseEntity;

class Client
{
    /**
     * @var Session The HTTP/2 session
     */
    protected Session $session;
    
    /**
     * @var Socket The socket used for communication
     */
    protected Socket $socket;
    
    /**
     * @var array<int, Http2StreamEntity> Active streams
     */
    protected array $streams = [];
    
    /**
     * @var int Next stream ID
     */
    protected int $nextStreamId = 1;
    
    /**
     * Create a new HTTP/2 client
     * 
     * @param Socket $socket The socket to use
     * @param array<string, mixed> $settings HTTP/2 settings
     */
    public function __construct(Socket $socket, array $settings = [])
    {
        $this->socket = $socket;
        $this->session = new Session($socket, true, $settings);
    }
    
    /**
     * Connect to the server and perform HTTP/2 handshake
     * 
     * @return bool Whether the connection was successful
     */
    public function connect(): bool
    {
        // Send connection preface and initial settings
        return $this->session->sendPreface();
    }
    
    /**
     * Send a request
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @param array<string, string|array<string>> $headers Request headers
     * @param string|Buffer|null $body Request body
     * @return int Stream ID of the request
     */
    public function request(string $method, string $path, array $headers = [], $body = null): int
    {
        // Create pseudo-headers
        $pseudoHeaders = [
            ':method' => $method,
            ':path' => $path,
            ':scheme' => 'https',
            ':authority' => $headers['Host'] ?? $headers['host'] ?? '',
        ];
        
        // Remove headers that are converted to pseudo-headers
        unset($headers['Host'], $headers['host']);
        
        // Merge pseudo-headers with regular headers
        $allHeaders = array_merge($pseudoHeaders, $headers);
        
        // Create a new stream
        $stream = $this->session->createStream($allHeaders);
        $streamId = $stream->streamId;
        
        // Store the stream
        $this->streams[$streamId] = $stream;
        
        // Send request body if provided
        if ($body !== null) {
            $this->session->sendData($streamId, $body, FrameType::DATA);
        }
        
        return $streamId;
    }
    
    /**
     * Receive a response
     * 
     * @param int $streamId Stream ID to receive from
     * @param int $timeout Timeout in milliseconds
     * @return ResponseEntity|null Response or null on timeout
     */
    public function receiveResponse(int $streamId, int $timeout = -1): ?ResponseEntity
    {
        // Implementation will be added later
        return null;
    }
    
    /**
     * Close the client connection
     * 
     * @param int $errorCode Error code to send
     * @return bool Whether the connection was closed successfully
     */
    public function close(int $errorCode = Error::NO_ERROR): bool
    {
        return $this->session->close($errorCode);
    }
}