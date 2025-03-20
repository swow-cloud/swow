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
use Swow\Http\Message\ServerRequestEntity;

class Server
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
     * @var callable|null Request handler
     */
    protected $requestHandler = null;
    
    /**
     * Create a new HTTP/2 server
     * 
     * @param Socket $socket The socket to use
     * @param array<string, mixed> $settings HTTP/2 settings
     */
    public function __construct(Socket $socket, array $settings = [])
    {
        $this->socket = $socket;
        $this->session = new Session($socket, false, $settings);
    }
    
    /**
     * Set the request handler
     * 
     * @param callable $handler The request handler function
     * @return self
     */
    public function setRequestHandler(callable $handler): self
    {
        $this->requestHandler = $handler;
        return $this;
    }
    
    /**
     * Handle a client connection
     * 
     * @return bool Whether the connection was handled successfully
     */
    public function handle(): bool
    {
        // Wait for client connection preface
        $buffer = new Buffer(24);
        $this->socket->recv($buffer);
        
        if ($buffer->toString() !== Http2::PREFACE) {
            // Not an HTTP/2 connection
            return false;
        }
        
        // Send server settings
        $this->session->sendSettings([]);
        
        // Process incoming frames
        $buffer = new Buffer(16384);
        while (true) {
            $length = $this->socket->recv($buffer);
            if ($length <= 0) {
                break;
            }
            
            // Process the received data
            $this->session->receiveData($buffer);
        }
        
        return true;
    }
    
    /**
     * Send a response
     * 
     * @param int $streamId Stream ID to send to
     * @param int $statusCode HTTP status code
     * @param array<string, string|array<string>> $headers Response headers
     * @param string|Buffer|null $body Response body
     * @return bool Whether the response was sent successfully
     */
    public function respond(int $streamId, int $statusCode, array $headers = [], $body = null): bool
    {
        // Create pseudo-headers
        $pseudoHeaders = [
            ':status' => (string) $statusCode,
        ];
        
        // Merge pseudo-headers with regular headers
        $allHeaders = array_merge($pseudoHeaders, $headers);
        
        // Send headers
        $this->session->sendHeaders($streamId, $allHeaders);
        
        // Send body if provided
        if ($body !== null) {
            $this->session->sendData($streamId, $body, FrameType::DATA);
        }
        
        return true;
    }
    
    /**
     * Close the server connection
     * 
     * @param int $errorCode Error code to send
     * @return bool Whether the connection was closed successfully
     */
    public function close(int $errorCode = Error::NO_ERROR): bool
    {
        return $this->session->close($errorCode);
    }
}