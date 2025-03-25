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

/**
 * HTTP/2 frame implementation
 */
class Frame
{
    /**
     * Frame flag: end stream
     */
    public const FLAG_END_STREAM = 0x1;
    
    /**
     * Frame flag: end headers
     */
    public const FLAG_END_HEADERS = 0x4;
    
    /**
     * Frame flag: padded
     */
    public const FLAG_PADDED = 0x8;
    
    /**
     * Frame flag: priority
     */
    public const FLAG_PRIORITY = 0x20;
    
    /**
     * Frame flag: ACK
     */
    public const FLAG_ACK = 0x1;
    
    /**
     * @var int Frame type
     */
    protected int $type;
    
    /**
     * @var int Frame flags
     */
    protected int $flags = 0;
    
    /**
     * @var int Stream identifier
     */
    protected int $streamId = 0;
    
    /**
     * @var Buffer Frame payload
     */
    protected Buffer $payload;
    
    /**
     * Create a new HTTP/2 frame
     * 
     * @param int $type Frame type
     * @param int $flags Frame flags
     * @param int $streamId Stream identifier
     * @param Buffer|null $payload Frame payload
     */
    public function __construct(int $type, int $flags = 0, int $streamId = 0, ?Buffer $payload = null)
    {
        $this->type = $type;
        $this->flags = $flags;
        $this->streamId = $streamId;
        $this->payload = $payload ?? new Buffer(0);
    }
    
    /**
     * Get frame type
     * 
     * @return int Frame type
     */
    public function getType(): int
    {
        return $this->type;
    }
    
    /**
     * Get frame flags
     * 
     * @return int Frame flags
     */
    public function getFlags(): int
    {
        return $this->flags;
    }
    
    /**
     * Check if a flag is set
     * 
     * @param int $flag Flag to check
     * @return bool Whether the flag is set
     */
    public function hasFlag(int $flag): bool
    {
        return ($this->flags & $flag) === $flag;
    }
    
    /**
     * Set a flag
     * 
     * @param int $flag Flag to set
     * @return self
     */
    public function setFlag(int $flag): self
    {
        $this->flags |= $flag;
        return $this;
    }
    
    /**
     * Clear a flag
     * 
     * @param int $flag Flag to clear
     * @return self
     */
    public function clearFlag(int $flag): self
    {
        $this->flags &= ~$flag;
        return $this;
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
     * Get frame payload
     * 
     * @return Buffer Frame payload
     */
    public function getPayload(): Buffer
    {
        return $this->payload;
    }
    
    /**
     * Set frame payload
     * 
     * @param Buffer $payload Frame payload
     * @return self
     */
    public function setPayload(Buffer $payload): self
    {
        $this->payload = $payload;
        return $this;
    }
    
    /**
     * Get frame length
     * 
     * @return int Frame length
     */
    public function getLength(): int
    {
        return $this->payload->getLength();
    }
    
    /**
     * Create a DATA frame
     * 
     * @param int $streamId Stream identifier
     * @param Buffer $data Data payload
     * @param int $flags Frame flags
     * @return self
     */
    public static function createDataFrame(int $streamId, Buffer $data, int $flags = 0): self
    {
        return new self(FrameType::DATA, $flags, $streamId, $data);
    }
    
    /**
     * Create a HEADERS frame
     * 
     * @param int $streamId Stream identifier
     * @param Buffer $headers Headers payload
     * @param int $flags Frame flags
     * @return self
     */
    public static function createHeadersFrame(int $streamId, Buffer $headers, int $flags = 0): self
    {
        return new self(FrameType::HEADERS, $flags, $streamId, $headers);
    }
    
    /**
     * Create a SETTINGS frame
     * 
     * @param array<int, int> $settings Settings parameters
     * @param bool $ack Whether this is an ACK frame
     * @return self
     */
    public static function createSettingsFrame(array $settings = [], bool $ack = false): self
    {
        $payload = new Buffer(count($settings) * 6);
        
        foreach ($settings as $id => $value) {
            $payload->write(0, pack('nN', $id, $value));
        }
        
        return new self(FrameType::SETTINGS, $ack ? self::FLAG_ACK : 0, 0, $payload);
    }
    
    /**
     * Create a WINDOW_UPDATE frame
     * 
     * @param int $streamId Stream identifier
     * @param int $increment Window size increment
     * @return self
     */
    public static function createWindowUpdateFrame(int $streamId, int $increment): self
    {
        $payload = new Buffer(4);
        $payload->write(0, pack('N', $increment));
        
        return new self(FrameType::WINDOW_UPDATE, 0, $streamId, $payload);
    }
    
    /**
     * Create a RST_STREAM frame
     * 
     * @param int $streamId Stream identifier
     * @param int $errorCode Error code
     * @return self
     */
    public static function createRstStreamFrame(int $streamId, int $errorCode): self
    {
        $payload = new Buffer(4);
        $payload->write(0, pack('N', $errorCode));
        
        return new self(FrameType::RST_STREAM, 0, $streamId, $payload);
    }
    
    /**
     * Create a GOAWAY frame
     * 
     * @param int $lastStreamId Last stream identifier
     * @param int $errorCode Error code
     * @param string $debugData Debug data
     * @return self
     */
    public static function createGoAwayFrame(int $lastStreamId, int $errorCode, string $debugData = ''): self
    {
        $payload = new Buffer(8 + strlen($debugData));
        $payload->write(0, pack('NN', $lastStreamId, $errorCode));
        
        if ($debugData !== '') {
            $payload->write(0, $debugData);
        }
        
        return new self(FrameType::GOAWAY, 0, 0, $payload);
    }
    
    /**
     * Create a PING frame
     * 
     * @param string $data Opaque data
     * @param bool $ack Whether this is an ACK frame
     * @return self
     */
    public static function createPingFrame(string $data, bool $ack = false): self
    {
        $payload = new Buffer(8);
        $payload->write(0, $data);
        
        return new self(FrameType::PING, $ack ? self::FLAG_ACK : 0, 0, $payload);
    }
    
    /**
     * Create a PRIORITY frame
     * 
     * @param int $streamId Stream identifier
     * @param int $dependsOn Stream dependency
     * @param int $weight Stream weight
     * @param bool $exclusive Stream exclusive flag
     * @return self
     */
    public static function createPriorityFrame(int $streamId, int $dependsOn, int $weight, bool $exclusive = false): self
    {
        $payload = new Buffer(5);
        $payload->write(0, pack('NC', ($exclusive ? 0x80000000 : 0) | $dependsOn, $weight));
        
        return new self(FrameType::PRIORITY, 0, $streamId, $payload);
    }
    
    /**
     * Create a PUSH_PROMISE frame
     * 
     * @param int $streamId Stream identifier
     * @param int $promisedStreamId Promised stream identifier
     * @param Buffer $headers Headers payload
     * @param int $flags Frame flags
     * @return self
     */
    public static function createPushPromiseFrame(int $streamId, int $promisedStreamId, Buffer $headers, int $flags = 0): self
    {
        $payload = new Buffer(4 + $headers->getLength());
        $payload->write(0, pack('N', $promisedStreamId));
        $payload->write(4, $headers->toString());
        
        return new self(FrameType::PUSH_PROMISE, $flags, $streamId, $payload);
    }
    
    /**
     * Create a CONTINUATION frame
     * 
     * @param int $streamId Stream identifier
     * @param Buffer $headers Headers payload
     * @param int $flags Frame flags
     * @return self
     */
    public static function createContinuationFrame(int $streamId, Buffer $headers, int $flags = 0): self
    {
        return new self(FrameType::CONTINUATION, $flags, $streamId, $headers);
    }
    
    /**
     * Encode frame to binary data
     * 
     * @return Buffer Encoded frame
     */
    public function encode(): Buffer
    {
        $length = $this->getLength();
        $header = new Buffer(9);
        
        // Length (24 bits), Type (8 bits), Flags (8 bits)
        $header->write(0, pack('CCC', ($length >> 16) & 0xFF, ($length >> 8) & 0xFF, $length & 0xFF));
        $header->write(3, pack('CC', $this->type, $this->flags));
        
        // Stream Identifier (31 bits, reserved bit = 0)
        $header->write(5, pack('N', $this->streamId & 0x7FFFFFFF));
        
        // Combine header and payload
        $result = new Buffer($header->getLength() + $length);
        $result->write(0, $header->toString());
        $result->write(9, $this->payload->toString());
        
        return $result;
    }
    
    /**
     * Decode binary data to frame
     * 
     * @param Buffer $buffer Buffer containing frame data
     * @return self|null Decoded frame or null if not enough data
     */
    public static function decode(Buffer $buffer): ?self
    {
        if ($buffer->getLength() < 9) {
            return null;
        }
        
        // Read frame header
        $header = $buffer->read(9);
        $length = (ord($header[0]) << 16) | (ord($header[1]) << 8) | ord($header[2]);
        $type = ord($header[3]);
        $flags = ord($header[4]);
        $streamId = unpack('N', substr($header, 5, 4))[1] & 0x7FFFFFFF;
        
        // Check if we have enough data for the payload
        if ($buffer->getLength() < $length) {
            return null;
        }
        
        // Read frame payload
        $payload = new Buffer($length);
        if ($length > 0) {
            $payload->write(0, $buffer->read($length));
        }
        
        return new self($type, $flags, $streamId, $payload);
    }
}