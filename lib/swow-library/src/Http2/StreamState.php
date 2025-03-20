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
 * HTTP/2 stream states
 */
class StreamState
{
    /**
     * Stream state: idle
     * 
     * All streams start in the "idle" state. In this state, no frames have
     * been exchanged.
     */
    public const IDLE = 0;
    
    /**
     * Stream state: reserved (local)
     * 
     * A stream in the "reserved (local)" state is one that has been promised
     * by sending a PUSH_PROMISE frame.
     */
    public const RESERVED_LOCAL = 1;
    
    /**
     * Stream state: reserved (remote)
     * 
     * A stream in the "reserved (remote)" state is one that has been promised
     * by receiving a PUSH_PROMISE frame.
     */
    public const RESERVED_REMOTE = 2;
    
    /**
     * Stream state: open
     * 
     * A stream in the "open" state may be used by both peers to send frames of
     * any type. In this state, sending peers observe advertised stream-level
     * flow-control limits.
     */
    public const OPEN = 3;
    
    /**
     * Stream state: half closed (local)
     * 
     * A stream in the "half-closed (local)" state cannot be used for sending
     * frames other than WINDOW_UPDATE, PRIORITY, and RST_STREAM.
     */
    public const HALF_CLOSED_LOCAL = 4;
    
    /**
     * Stream state: half closed (remote)
     * 
     * A stream in the "half-closed (remote)" state is no longer being used by
     * the peer to send frames. In this state, an endpoint is no longer
     * obligated to maintain a receiver flow-control window.
     */
    public const HALF_CLOSED_REMOTE = 5;
    
    /**
     * Stream state: closed
     * 
     * The "closed" state is the terminal state.
     */
    public const CLOSED = 6;
    
    /**
     * Get stream state name
     * 
     * @param int $state Stream state
     * @return string Stream state name
     */
    public static function getName(int $state): string
    {
        switch ($state) {
            case self::IDLE:
                return 'IDLE';
            case self::RESERVED_LOCAL:
                return 'RESERVED_LOCAL';
            case self::RESERVED_REMOTE:
                return 'RESERVED_REMOTE';
            case self::OPEN:
                return 'OPEN';
            case self::HALF_CLOSED_LOCAL:
                return 'HALF_CLOSED_LOCAL';
            case self::HALF_CLOSED_REMOTE:
                return 'HALF_CLOSED_REMOTE';
            case self::CLOSED:
                return 'CLOSED';
            default:
                return 'UNKNOWN';
        }
    }
}