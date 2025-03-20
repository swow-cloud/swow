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
 * HTTP/2 settings parameters
 */
class Settings
{
    /**
     * Header table size
     */
    public const HEADER_TABLE_SIZE = 0x1;
    
    /**
     * Enable push
     */
    public const ENABLE_PUSH = 0x2;
    
    /**
     * Maximum concurrent streams
     */
    public const MAX_CONCURRENT_STREAMS = 0x3;
    
    /**
     * Initial window size
     */
    public const INITIAL_WINDOW_SIZE = 0x4;
    
    /**
     * Maximum frame size
     */
    public const MAX_FRAME_SIZE = 0x5;
    
    /**
     * Maximum header list size
     */
    public const MAX_HEADER_LIST_SIZE = 0x6;
    
    /**
     * Enable connect protocol
     */
    public const ENABLE_CONNECT_PROTOCOL = 0x8;
    
    /**
     * Get setting name
     * 
     * @param int $setting Setting ID
     * @return string Setting name
     */
    public static function getName(int $setting): string
    {
        switch ($setting) {
            case self::HEADER_TABLE_SIZE:
                return 'HEADER_TABLE_SIZE';
            case self::ENABLE_PUSH:
                return 'ENABLE_PUSH';
            case self::MAX_CONCURRENT_STREAMS:
                return 'MAX_CONCURRENT_STREAMS';
            case self::INITIAL_WINDOW_SIZE:
                return 'INITIAL_WINDOW_SIZE';
            case self::MAX_FRAME_SIZE:
                return 'MAX_FRAME_SIZE';
            case self::MAX_HEADER_LIST_SIZE:
                return 'MAX_HEADER_LIST_SIZE';
            case self::ENABLE_CONNECT_PROTOCOL:
                return 'ENABLE_CONNECT_PROTOCOL';
            default:
                return 'UNKNOWN';
        }
    }
}