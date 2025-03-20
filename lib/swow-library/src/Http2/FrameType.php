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
 * HTTP/2 frame types
 */
class FrameType
{
    /**
     * DATA frame
     */
    public const DATA = 0x0;
    
    /**
     * HEADERS frame
     */
    public const HEADERS = 0x1;
    
    /**
     * PRIORITY frame
     */
    public const PRIORITY = 0x2;
    
    /**
     * RST_STREAM frame
     */
    public const RST_STREAM = 0x3;
    
    /**
     * SETTINGS frame
     */
    public const SETTINGS = 0x4;
    
    /**
     * PUSH_PROMISE frame
     */
    public const PUSH_PROMISE = 0x5;
    
    /**
     * PING frame
     */
    public const PING = 0x6;
    
    /**
     * GOAWAY frame
     */
    public const GOAWAY = 0x7;
    
    /**
     * WINDOW_UPDATE frame
     */
    public const WINDOW_UPDATE = 0x8;
    
    /**
     * CONTINUATION frame
     */
    public const CONTINUATION = 0x9;
    
    /**
     * Get frame type name
     * 
     * @param int $frameType Frame type
     * @return string Frame type name
     */
    public static function getName(int $frameType): string
    {
        switch ($frameType) {
            case self::DATA:
                return 'DATA';
            case self::HEADERS:
                return 'HEADERS';
            case self::PRIORITY:
                return 'PRIORITY';
            case self::RST_STREAM:
                return 'RST_STREAM';
            case self::SETTINGS:
                return 'SETTINGS';
            case self::PUSH_PROMISE:
                return 'PUSH_PROMISE';
            case self::PING:
                return 'PING';
            case self::GOAWAY:
                return 'GOAWAY';
            case self::WINDOW_UPDATE:
                return 'WINDOW_UPDATE';
            case self::CONTINUATION:
                return 'CONTINUATION';
            default:
                return 'UNKNOWN';
        }
    }
}