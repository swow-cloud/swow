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
use Swow\Http2\Http2;

/**
 * @internal
 */
#[CoversClass(Http2::class)]
final class Http2Test extends TestCase
{
    public function testPrefaceConstant(): void
    {
        // Test that the HTTP/2 connection preface is correctly defined
        $this->assertSame("PRI * HTTP/2.0\r\n\r\nSM\r\n\r\n", Http2::PREFACE);
    }

    public function testDefaultSettingsConstants(): void
    {
        // Test that all default settings constants are defined and have the correct values
        $this->assertSame(4096, Http2::DEFAULT_HEADER_TABLE_SIZE);
        $this->assertSame(1, Http2::DEFAULT_ENABLE_PUSH);
        $this->assertSame(100, Http2::DEFAULT_MAX_CONCURRENT_STREAMS);
        $this->assertSame(65535, Http2::DEFAULT_INITIAL_WINDOW_SIZE);
        $this->assertSame(16384, Http2::DEFAULT_MAX_FRAME_SIZE);
        $this->assertSame(65536, Http2::DEFAULT_MAX_HEADER_LIST_SIZE);
    }

    public function testOtherConstants(): void
    {
        // Test other HTTP/2 constants
        $this->assertSame(0x7FFFFFFF, Http2::MAX_STREAM_ID);
        $this->assertSame(16, Http2::DEFAULT_PRIORITY);
        $this->assertSame(16, Http2::DEFAULT_WEIGHT);
        $this->assertSame('2.0', Http2::VERSION);
        $this->assertSame('h2', Http2::ALPN_PROTOCOL_ID);
        $this->assertSame('h2c', Http2::ALPN_PROTOCOL_ID_CLEARTEXT);
    }

    public function testIsPseudoHeader(): void
    {
        // Test the isPseudoHeader method
        $this->assertTrue(Http2::isPseudoHeader(':method'));
        $this->assertTrue(Http2::isPseudoHeader(':path'));
        $this->assertTrue(Http2::isPseudoHeader(':scheme'));
        $this->assertTrue(Http2::isPseudoHeader(':authority'));
        $this->assertFalse(Http2::isPseudoHeader('content-type'));
        $this->assertFalse(Http2::isPseudoHeader('user-agent'));
    }
}