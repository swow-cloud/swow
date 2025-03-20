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

namespace Swow\Tests\Http2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swow\Http2\StreamState;

/**
 * @internal
 */
#[CoversClass(StreamState::class)]
final class StreamStateTest extends TestCase
{
    public function testStreamStateConstants(): void
    {
        // Test that all stream state constants are defined and have the correct values
        $this->assertSame(0, StreamState::IDLE);
        $this->assertSame(1, StreamState::RESERVED_LOCAL);
        $this->assertSame(2, StreamState::RESERVED_REMOTE);
        $this->assertSame(3, StreamState::OPEN);
        $this->assertSame(4, StreamState::HALF_CLOSED_LOCAL);
        $this->assertSame(5, StreamState::HALF_CLOSED_REMOTE);
        $this->assertSame(6, StreamState::CLOSED);
    }

    public function testGetName(): void
    {
        // Test the getName method returns correct stream state names
        $this->assertSame('IDLE', StreamState::getName(StreamState::IDLE));
        $this->assertSame('RESERVED_LOCAL', StreamState::getName(StreamState::RESERVED_LOCAL));
        $this->assertSame('RESERVED_REMOTE', StreamState::getName(StreamState::RESERVED_REMOTE));
        $this->assertSame('OPEN', StreamState::getName(StreamState::OPEN));
        $this->assertSame('HALF_CLOSED_LOCAL', StreamState::getName(StreamState::HALF_CLOSED_LOCAL));
        $this->assertSame('HALF_CLOSED_REMOTE', StreamState::getName(StreamState::HALF_CLOSED_REMOTE));
        $this->assertSame('CLOSED', StreamState::getName(StreamState::CLOSED));
        $this->assertSame('UNKNOWN', StreamState::getName(99)); // Unknown state
    }
}