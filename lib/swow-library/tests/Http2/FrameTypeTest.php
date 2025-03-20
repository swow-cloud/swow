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
use Swow\Http2\FrameType;

/**
 * @internal
 */
#[CoversClass(FrameType::class)]
final class FrameTypeTest extends TestCase
{
    public function testFrameTypeConstants(): void
    {
        // Test that all frame type constants are defined and have the correct values
        $this->assertSame(0x0, FrameType::DATA);
        $this->assertSame(0x1, FrameType::HEADERS);
        $this->assertSame(0x2, FrameType::PRIORITY);
        $this->assertSame(0x3, FrameType::RST_STREAM);
        $this->assertSame(0x4, FrameType::SETTINGS);
        $this->assertSame(0x5, FrameType::PUSH_PROMISE);
        $this->assertSame(0x6, FrameType::PING);
        $this->assertSame(0x7, FrameType::GOAWAY);
        $this->assertSame(0x8, FrameType::WINDOW_UPDATE);
        $this->assertSame(0x9, FrameType::CONTINUATION);
    }

    public function testGetName(): void
    {
        // Test the getName method returns correct frame type names
        $this->assertSame('DATA', FrameType::getName(FrameType::DATA));
        $this->assertSame('HEADERS', FrameType::getName(FrameType::HEADERS));
        $this->assertSame('PRIORITY', FrameType::getName(FrameType::PRIORITY));
        $this->assertSame('RST_STREAM', FrameType::getName(FrameType::RST_STREAM));
        $this->assertSame('SETTINGS', FrameType::getName(FrameType::SETTINGS));
        $this->assertSame('PUSH_PROMISE', FrameType::getName(FrameType::PUSH_PROMISE));
        $this->assertSame('PING', FrameType::getName(FrameType::PING));
        $this->assertSame('GOAWAY', FrameType::getName(FrameType::GOAWAY));
        $this->assertSame('WINDOW_UPDATE', FrameType::getName(FrameType::WINDOW_UPDATE));
        $this->assertSame('CONTINUATION', FrameType::getName(FrameType::CONTINUATION));
        $this->assertSame('UNKNOWN', FrameType::getName(99)); // Unknown frame type
    }
}