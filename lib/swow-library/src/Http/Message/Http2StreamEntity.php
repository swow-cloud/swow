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

namespace Swow\Http\Message;

use Swow\Buffer;

class Http2StreamEntity extends MessageEntity
{
    /**
     * HTTP/2 stream identifier
     */
    public int $streamId = 0;
    
    /**
     * HTTP/2 stream priority
     */
    public int $priority = 0;
    
    /**
     * HTTP/2 stream weight (1-256)
     */
    public int $weight = 16;
    
    /**
     * HTTP/2 stream dependency
     */
    public int $dependsOn = 0;
    
    /**
     * HTTP/2 stream exclusive flag
     */
    public bool $exclusive = false;
    
    /**
     * HTTP/2 stream state
     */
    public int $state = 0;
    
    /**
     * HTTP/2 stream error code
     */
    public int $errorCode = 0;
    
    /**
     * HTTP/2 stream headers
     * 
     * @var array<string, array<string>>
     */
    public array $trailers = [];
    
    /**
     * HTTP/2 stream pseudo-headers
     * 
     * @var array<string, string>
     */
    public array $pseudoHeaders = [];
    
    /**
     * Set protocol version to HTTP/2
     */
    public function __construct()
    {
        $this->protocolVersion = '2.0';
    }
}