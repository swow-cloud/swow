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

class Session
{
    /**
     * @var \Swow\Http2\Session Internal session object
     */
    protected $session;
    
    /**
     * @var Socket The socket associated with this session
     */
    protected Socket $socket;
    
    /**
     * @var array<int, Http2StreamEntity> Active streams
     */
    protected array $streams = [];
    
    /**
     * @var array<string, mixed> Session settings
     */
    protected array $settings = [];
    
    /**
     * @var bool Whether this session is for client side
     */
    protected bool $isClient;
    
    /**
     * Create a new HTTP/2 session
     * 
     * @param Socket $socket The socket to use for this session
     * @param bool $isClient Whether this session is for client side
     * @param array<string, mixed> $settings Session settings
     */
    public function __construct(Socket $socket, bool $isClient = true, array $settings = [])
    {
        $this->socket = $socket;
        $this->isClient = $isClient;
        $this->settings = $settings;
        
        // Initialize the internal session object
        $this->initSession();
    }
    
    /**
     * Initialize the internal session object
     */
    protected function initSession(): void
    {
        // This will be implemented in the extension
    }
    
    /**
     * Send connection preface and initial settings
     */
    public function sendPreface(): bool
    {
        if ($this->isClient) {
            // Send client connection preface
            $this->socket->send(Http2::PREFACE);
        }
        
        // Send initial SETTINGS frame
        return $this->sendSettings($this->settings);
    }
    
    /**
     * Send SETTINGS frame
     * 
     * @param array<string, mixed> $settings Settings to send
     */
    public function sendSettings(array $settings): bool
    {
        // This will be implemented in the extension
        return true;
    }
    
    /**
     * Create a new stream
     * 
     * @param array<string, string> $headers Headers to send
     * @param int $flags Stream flags
     * @return Http2StreamEntity The created stream
     */
    public function createStream(array $headers, int $flags = 0): Http2StreamEntity
    {
        $stream = new Http2StreamEntity();
        // This will be implemented in the extension
        return $stream;
    }
    
    /**
     * Send data on a stream
     * 
     * @param int $streamId Stream ID
     * @param string|Buffer $data Data to send
     * @param int $flags Data flags
     */
    public function sendData(int $streamId, $data, int $flags = 0): bool
    {
        // This will be implemented in the extension
        return true;
    }
    
    /**
     * Send headers on a stream
     * 
     * @param int $streamId Stream ID
     * @param array<string, string> $headers Headers to send
     * @param int $flags Header flags
     */
    public function sendHeaders(int $streamId, array $headers, int $flags = 0): bool
    {
        // This will be implemented in the extension
        return true;
    }
    
    /**
     * Process incoming data
     * 
     * @param string|Buffer $data Data to process
     */
    public function receiveData($data): bool
    {
        // This will be implemented in the extension
        return true;
    }
    
    /**
     * Close the session
     * 
     * @param int $errorCode Error code to send
     */
    public function close(int $errorCode = Error::NO_ERROR): bool
    {
        // This will be implemented in the extension
        return true;
    }
}