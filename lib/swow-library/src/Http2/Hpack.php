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
 * HTTP/2 HPACK header compression implementation
 */
class Hpack
{
    /**
     * Static table entries
     * 
     * @var array<array<string>>
     */
    protected static array $staticTable = [
        [':authority', ''],
        [':method', 'GET'],
        [':method', 'POST'],
        [':path', '/'],
        [':path', '/index.html'],
        [':scheme', 'http'],
        [':scheme', 'https'],
        [':status', '200'],
        [':status', '204'],
        [':status', '206'],
        [':status', '304'],
        [':status', '400'],
        [':status', '404'],
        [':status', '500'],
        ['accept-charset', ''],
        ['accept-encoding', 'gzip, deflate'],
        ['accept-language', ''],
        ['accept-ranges', ''],
        ['accept', ''],
        ['access-control-allow-origin', ''],
        ['age', ''],
        ['allow', ''],
        ['authorization', ''],
        ['cache-control', ''],
        ['content-disposition', ''],
        ['content-encoding', ''],
        ['content-language', ''],
        ['content-length', ''],
        ['content-location', ''],
        ['content-range', ''],
        ['content-type', ''],
        ['cookie', ''],
        ['date', ''],
        ['etag', ''],
        ['expect', ''],
        ['expires', ''],
        ['from', ''],
        ['host', ''],
        ['if-match', ''],
        ['if-modified-since', ''],
        ['if-none-match', ''],
        ['if-range', ''],
        ['if-unmodified-since', ''],
        ['last-modified', ''],
        ['link', ''],
        ['location', ''],
        ['max-forwards', ''],
        ['proxy-authenticate', ''],
        ['proxy-authorization', ''],
        ['range', ''],
        ['referer', ''],
        ['refresh', ''],
        ['retry-after', ''],
        ['server', ''],
        ['set-cookie', ''],
        ['strict-transport-security', ''],
        ['transfer-encoding', ''],
        ['user-agent', ''],
        ['vary', ''],
        ['via', ''],
        ['www-authenticate', ''],
    ];
    
    /**
     * Dynamic table entries
     * 
     * @var array<array<string>>
     */
    protected array $dynamicTable = [];
    
    /**
     * Dynamic table size
     * 
     * @var int
     */
    protected int $dynamicTableSize = 0;
    
    /**
     * Maximum dynamic table size
     * 
     * @var int
     */
    protected int $maxDynamicTableSize = 4096;
    
    /**
     * Create a new HPACK encoder/decoder
     * 
     * @param int $maxDynamicTableSize Maximum dynamic table size
     */
    public function __construct(int $maxDynamicTableSize = 4096)
    {
        $this->maxDynamicTableSize = $maxDynamicTableSize;
    }
    
    /**
     * Set maximum dynamic table size
     * 
     * @param int $size Maximum dynamic table size
     * @return self
     */
    public function setMaxDynamicTableSize(int $size): self
    {
        $this->maxDynamicTableSize = $size;
        $this->evictEntries();
        return $this;
    }
    
    /**
     * Get maximum dynamic table size
     * 
     * @return int Maximum dynamic table size
     */
    public function getMaxDynamicTableSize(): int
    {
        return $this->maxDynamicTableSize;
    }
    
    /**
     * Get current dynamic table size
     * 
     * @return int Current dynamic table size
     */
    public function getDynamicTableSize(): int
    {
        return $this->dynamicTableSize;
    }
    
    /**
     * Evict entries from the dynamic table if necessary
     */
    protected function evictEntries(): void
    {
        while ($this->dynamicTableSize > $this->maxDynamicTableSize && !empty($this->dynamicTable)) {
            $entry = array_pop($this->dynamicTable);
            $this->dynamicTableSize -= 32 + strlen($entry[0]) + strlen($entry[1]);
        }
    }
    
    /**
     * Add an entry to the dynamic table
     * 
     * @param string $name Header name
     * @param string $value Header value
     */
    protected function addDynamicTableEntry(string $name, string $value): void
    {
        $size = 32 + strlen($name) + strlen($value);
        
        // Check if the entry is too large for the table
        if ($size > $this->maxDynamicTableSize) {
            // Clear the dynamic table if the entry is too large
            $this->dynamicTable = [];
            $this->dynamicTableSize = 0;
            return;
        }
        
        // Evict entries if necessary
        while ($this->dynamicTableSize + $size > $this->maxDynamicTableSize && !empty($this->dynamicTable)) {
            $entry = array_pop($this->dynamicTable);
            $this->dynamicTableSize -= 32 + strlen($entry[0]) + strlen($entry[1]);
        }
        
        // Add the new entry
        array_unshift($this->dynamicTable, [$name, $value]);
        $this->dynamicTableSize += $size;
    }
    
    /**
     * Get an entry from the static or dynamic table
     * 
     * @param int $index Table index
     * @return array<string>|null Header entry or null if not found
     */
    protected function getTableEntry(int $index): ?array
    {
        if ($index <= 0) {
            return null;
        }
        
        if ($index <= count(self::$staticTable)) {
            return self::$staticTable[$index - 1];
        }
        
        $dynamicIndex = $index - count(self::$staticTable) - 1;
        if ($dynamicIndex < count($this->dynamicTable)) {
            return $this->dynamicTable[$dynamicIndex];
        }
        
        return null;
    }
    
    /**
     * Find an entry in the static or dynamic table
     * 
     * @param string $name Header name
     * @param string $value Header value
     * @return int|null Table index or null if not found
     */
    protected function findTableEntry(string $name, string $value): ?int
    {
        // Search in static table
        foreach (self::$staticTable as $i => $entry) {
            if ($entry[0] === $name && $entry[1] === $value) {
                return $i + 1;
            }
        }
        
        // Search in dynamic table
        foreach ($this->dynamicTable as $i => $entry) {
            if ($entry[0] === $name && $entry[1] === $value) {
                return count(self::$staticTable) + $i + 1;
            }
        }
        
        return null;
    }
    
    /**
     * Find a name in the static or dynamic table
     * 
     * @param string $name Header name
     * @return int|null Table index or null if not found
     */
    protected function findTableName(string $name): ?int
    {
        // Search in static table
        foreach (self::$staticTable as $i => $entry) {
            if ($entry[0] === $name) {
                return $i + 1;
            }
        }
        
        // Search in dynamic table
        foreach ($this->dynamicTable as $i => $entry) {
            if ($entry[0] === $name) {
                return count(self::$staticTable) + $i + 1;
            }
        }
        
        return null;
    }
    
    /**
     * Encode an integer
     * 
     * @param Buffer $buffer Buffer to write to
     * @param int $value Integer to encode
     * @param int $prefixBits Number of prefix bits
     * @param int $prefix Prefix value
     */
    protected function encodeInteger(Buffer $buffer, int $value, int $prefixBits, int $prefix = 0): void
    {
        $mask = (1 << $prefixBits) - 1;
        
        if ($value < $mask) {
            $buffer->write(chr($prefix | $value));
            return;
        }
        
        $buffer->write(chr($prefix | $mask));
        $value -= $mask;
        
        while ($value >= 128) {
            $buffer->write(chr(($value & 0x7F) | 0x80));
            $value >>= 7;
        }
        
        $buffer->write(chr($value));
    }
    
    /**
     * Decode an integer
     * 
     * @param Buffer $buffer Buffer to read from
     * @param int $prefixBits Number of prefix bits
     * @return int Decoded integer
     */
    protected function decodeInteger(Buffer $buffer, int $prefixBits): int
    {
        if ($buffer->getLength() < 1) {
            throw new \RuntimeException('Buffer too small');
        }
        
        $mask = (1 << $prefixBits) - 1;
        $value = ord($buffer->read(1)) & $mask;
        
        if ($value < $mask) {
            return $value;
        }
        
        $shift = 0;
        do {
            if ($buffer->getLength() < 1) {
                throw new \RuntimeException('Buffer too small');
            }
            
            $byte = ord($buffer->read(1));
            $value += ($byte & 0x7F) << $shift;
            $shift += 7;
        } while ($byte & 0x80);
        
        return $value;
    }
    
    /**
     * Encode a string
     * 
     * @param Buffer $buffer Buffer to write to
     * @param string $value String to encode
     * @param bool $huffman Whether to use Huffman encoding
     */
    protected function encodeString(Buffer $buffer, string $value, bool $huffman = false): void
    {
        if ($huffman) {
            // Huffman encoding not implemented yet
            // For now, just use plain encoding
            $this->encodeInteger($buffer, strlen($value), 7, 0);
        } else {
            $this->encodeInteger($buffer, strlen($value), 7, 0);
        }
        
        $buffer->write($value);
    }
    
    /**
     * Decode a string
     * 
     * @param Buffer $buffer Buffer to read from
     * @return string Decoded string
     */
    protected function decodeString(Buffer $buffer): string
    {
        if ($buffer->getLength() < 1) {
            throw new \RuntimeException('Buffer too small');
        }
        
        $firstByte = ord($buffer->peek(1));
        $huffman = ($firstByte & 0x80) !== 0;
        $length = $this->decodeInteger($buffer, 7);
        
        if ($buffer->getLength() < $length) {
            throw new \RuntimeException('Buffer too small');
        }
        
        $value = $buffer->read($length);
        
        if ($huffman) {
            // Huffman decoding not implemented yet
            // For now, just return the raw value
            return $value;
        }
        
        return $value;
    }
    
    /**
     * Encode headers
     * 
     * @param array<array<string>> $headers Headers to encode
     * @return Buffer Encoded headers
     */
    public function encode(array $headers): Buffer
    {
        $buffer = new Buffer(1024);
        
        foreach ($headers as [$name, $value]) {
            $name = strtolower($name);
            
            // Try to find the header in the tables
            $index = $this->findTableEntry($name, $value);
            
            if ($index !== null) {
                // Indexed header field
                $this->encodeInteger($buffer, $index, 7, 0x80);
            } else {
                $nameIndex = $this->findTableName($name);
                
                if ($nameIndex !== null) {
                    // Literal header field with indexed name
                    $this->encodeInteger($buffer, $nameIndex, 6, 0x40);
                    $this->encodeString($buffer, $value);
                } else {
                    // Literal header field with new name
                    $buffer->write(chr(0x40));
                    $this->encodeString($buffer, $name);
                    $this->encodeString($buffer, $value);
                }
                
                // Add to dynamic table
                $this->addDynamicTableEntry($name, $value);
            }
        }
        
        return $buffer;
    }
    
    /**
     * Decode headers
     * 
     * @param Buffer $buffer Buffer containing encoded headers
     * @return array<array<string>> Decoded headers
     */
    public function decode(Buffer $buffer): array
    {
        $headers = [];
        
        while ($buffer->getLength() > 0) {
            $firstByte = ord($buffer->peek(1));
            
            if (($firstByte & 0x80) !== 0) {
                // Indexed header field
                $index = $this->decodeInteger($buffer, 7);
                $entry = $this->getTableEntry($index);
                
                if ($entry === null) {
                    throw new \RuntimeException("Invalid index: {$index}");
                }
                
                $headers[] = $entry;
            } elseif (($firstByte & 0x40) !== 0) {
                // Literal header field with incremental indexing
                $index = $this->decodeInteger($buffer, 6);
                
                if ($index === 0) {
                    // New name
                    $name = $this->decodeString($buffer);
                    $value = $this->decodeString($buffer);
                } else {
                    // Indexed name
                    $entry = $this->getTableEntry($index);
                    
                    if ($entry === null) {
                        throw new \RuntimeException("Invalid index: {$index}");
                    }
                    
                    $name = $entry[0];
                    $value = $this->decodeString($buffer);
                }
                
                $headers[] = [$name, $value];
                $this->addDynamicTableEntry($name, $value);
            } elseif (($firstByte & 0x20) !== 0) {
                // Dynamic table size update
                $size = $this->decodeInteger($buffer, 5);
                $this->setMaxDynamicTableSize($size);
            } else {
                // Literal header field without indexing or never indexed
                $index = $this->decodeInteger($buffer, 4);
                
                if ($index === 0) {
                    // New name
                    $name = $this->decodeString($buffer);
                    $value = $this->decodeString($buffer);
                } else {
                    // Indexed name
                    $entry = $this->getTableEntry($index);
                    
                    if ($entry === null) {
                        throw new \RuntimeException("Invalid index: {$index}");
                    }
                    
                    $name = $entry[0];
                    $value = $this->decodeString($buffer);
                }
                
                $headers[] = [$name, $value];
            }
        }
        
        return $headers;
    }
}