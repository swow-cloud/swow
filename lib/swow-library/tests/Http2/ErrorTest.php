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
use Swow\Http2\Error;

/**
 * @internal
 */
#[CoversClass(Error::class)]
final class ErrorTest extends TestCase
{
    public function testErrorConstants(): void
    {
        // Test that all error constants are defined and have the correct values
        $this->assertSame(0x0, Error::NO_ERROR);
        $this->assertSame(0x1, Error::PROTOCOL_ERROR);
        $this->assertSame(0x2, Error::INTERNAL_ERROR);
        $this->assertSame(0x3, Error::FLOW_CONTROL_ERROR);
        $this->assertSame(0x4, Error::SETTINGS_TIMEOUT);
        $this->assertSame(0x5, Error::STREAM_CLOSED);
        $this->assertSame(0x6, Error::FRAME_SIZE_ERROR);
        $this->assertSame(0x7, Error::REFUSED_STREAM);
        $this->assertSame(0x8, Error::CANCEL);
        $this->assertSame(0x9, Error::COMPRESSION_ERROR);
        $this->assertSame(0xa, Error::CONNECT_ERROR);
        $this->assertSame(0xb, Error::ENHANCE_YOUR_CALM);
        $this->assertSame(0xc, Error::INADEQUATE_SECURITY);
        $this->assertSame(0xd, Error::HTTP_1_1_REQUIRED);
    }
}