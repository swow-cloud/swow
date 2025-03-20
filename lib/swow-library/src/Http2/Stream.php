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
use Swow\Http\Message\Http2StreamEntity;

/**
 * HTTP/2 stream implementation
 */
class Stream
{
    /**
     * Stream state: idle
     */
    public const STATE_IDLE = 0;
    
    /**
     * Stream state: reserved (local)
     */
    public const STATE_RESERVED_LOCAL = 1;
    
    /**
     * Stream state: reserved (remote)
     */
    public const STATE_RESERVED_REMOTE = 2;
    
    /**
     * Stream state: open
     */
    public const STATE_OPEN = 3;
    
    /**
     * Stream state: half closed (local)
     */
    public const STATE_HALF_CLOSED_LOCAL = 4;
    
    /**
     * Stream state: half closed (remote)
     */
    public const STATE_HALF_CLOSED_REMOTE = 5;
    
    /**
     * Stream state: closed
     */
    public const STATE_CLOSED = 6;
    
    /**
     * @var int Stream identifier
     */
    protected int $streamId;
    
    /**
     * @var int Stream state
     */
    protected int $state = self::STATE_IDLE;
    
    /**
     * @var int Stream priority
     */
    protected int $priority = 0;
    
    /**
     * @var int Stream weight (1-256)
     */
    protected int $weight = 16;
    
    /**
     * @var int Stream dependency
     */
    protected int $dependsOn = 0;
    
    /**
     * @var bool Stream exclusive flag
     */
    protected bool $exclusive = false;
    
    /**
     * @var int Stream error code
     */
    protected int $errorCode = Error::NO_ERROR;
    
    /**
     * @var Http2StreamEntity Associated stream entity
     */
    protected Http2StreamEntity $entity;
    
    /**
     * @var Session Parent session
     */
    protected Session $session;
    
    /**
     * Create a new HTTP/2 stream
     * 
     * @param int $streamId Stream identifier
     * @param Session $session Parent session
     */
    public function __construct(int $streamId, Session $session)
    {
        $this->streamId = $streamId;
        $this->session = $session;
        $this->entity = new Http2StreamEntity();
        $this->entity->streamId = $streamId;
    }
    
    /**
     * Get stream identifier
     * 
     * @return int Stream identifier
     */
    public function getStreamId(): int
    {
        return $this->streamId;
    }
    
    /**
     * Get stream state
     * 
     * @return int Stream state
     */
    public function getState(): int
    {
        return $this->state;
    }
    
    /**
     * Set stream state
     * 
     * @param int $state New stream state
     * @return self
     */
    public function setState(int $state): self
    {
        $this->state = $state;
        $this->entity->state = $state;
        return $this;
    }
    
    /**
     * Get stream entity
     * 
     * @return Http2StreamEntity Stream entity
     */
    public function getEntity(): Http2StreamEntity
    {
        return $this->entity;
    }
    
    /**
     * Set stream priority
     * 
     * @param int $priority Stream priority
     * @param int $dependsOn Stream dependency
     * @param bool $exclusive Stream exclusive flag
     * @return self
     */
    public function setPriority(int $priority, int $dependsOn = 0, bool $exclusive = false): self
    {
        $this->priority = $priority;
        $this->dependsOn = $dependsOn;
        $this->exclusive = $exclusive;
        
        $this->entity->priority = $priority;
        $this->entity->dependsOn = $dependsOn;
        $this->entity->exclusive = $exclusive;
        
        return $this;
    }
    
    /**
     * Set stream weight
     * 
     * @param int $weight Stream weight (1-256)
     * @return self
     */
    public function setWeight(int $weight): self
    {
        $this->weight = $weight;
        $this->entity->weight = $weight;
        return $this;
    }
    
    /**
     * Close the stream
     * 
     * @param int $errorCode Error code
     * @return bool Whether the stream was closed successfully
     */
    public function close(int $errorCode = Error::NO_ERROR): bool
    {
        $this->errorCode = $errorCode;
        $this->entity->errorCode = $errorCode;
        $this->setState(self::STATE_CLOSED);
        return true;
    }
    
    /**
     * Get stream state name
     * 
     * @param int $state Stream state
     * @return string Stream state name
     */
    public static function getStateName(int $state): string
    {
        switch ($state) {
            case self::STATE_IDLE:
                return 'IDLE';
            case self::STATE_RESERVED_LOCAL:
                return 'RESERVED_LOCAL';
            case self::STATE_RESERVED_REMOTE:
                return 'RESERVED_REMOTE';
            case self::STATE_OPEN:
                return 'OPEN';
            case self::STATE_HALF_CLOSED_LOCAL:
                return 'HALF_CLOSED_LOCAL';
            case self::STATE_HALF_CLOSED_REMOTE:
                return 'HALF_CLOSED_REMOTE';
            case self::STATE_CLOSED:
                return 'CLOSED';
            default:
                return 'UNKNOWN';
        }
    }
}