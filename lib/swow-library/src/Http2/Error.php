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
 * HTTP/2 error codes
 */
class Error
{
    /**
     * No error
     */
    public const NO_ERROR = 0x0;
    
    /**
     * Protocol error
     */
    public const PROTOCOL_ERROR = 0x1;
    
    /**
     * Internal error
     */
    public const INTERNAL_ERROR = 0x2;
    
    /**
     * Flow control error
     */
    public const FLOW_CONTROL_ERROR = 0x3;
    
    /**
     * Settings timeout
     */
    public const SETTINGS_TIMEOUT = 0x4;
    
    /**
     * Stream closed
     */
    public const STREAM_CLOSED = 0x5;
    
    /**
     * Frame size error
     */
    public const FRAME_SIZE_ERROR = 0x6;
    
    /**
     * Refused stream
     */
    public const REFUSED_STREAM = 0x7;
    
    /**
     * Cancel
     */
    public const CANCEL = 0x8;
    
    /**
     * Compression error
     */
    public const COMPRESSION_ERROR = 0x9;
    
    /**
     * Connect error
     */
    public const CONNECT_ERROR = 0xa;
    
    /**
     * Enhance your calm
     */
    public const ENHANCE_YOUR_CALM = 0xb;
    
    /**
     * Inadequate security
     */
    public const INADEQUATE_SECURITY = 0xc;
    
    /**
     * HTTP/1.1 required
     */
    public const HTTP_1_1_REQUIRED = 0xd;
}