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
use Swow\Http2\Settings;

/**
 * @internal
 */
#[CoversClass(Settings::class)]
final class SettingsTest extends TestCase
{
    public function testSettingsConstants(): void
    {
        // Test that all settings constants are defined and have the correct values
        $this->assertSame(0x1, Settings::HEADER_TABLE_SIZE);
        $this->assertSame(0x2, Settings::ENABLE_PUSH);
        $this->assertSame(0x3, Settings::MAX_CONCURRENT_STREAMS);
        $this->assertSame(0x4, Settings::INITIAL_WINDOW_SIZE);
        $this->assertSame(0x5, Settings::MAX_FRAME_SIZE);
        $this->assertSame(0x6, Settings::MAX_HEADER_LIST_SIZE);
        $this->assertSame(0x8, Settings::ENABLE_CONNECT_PROTOCOL);
    }

    public function testGetName(): void
    {
        // Test the getName method returns correct settings names
        $this->assertSame('HEADER_TABLE_SIZE', Settings::getName(Settings::HEADER_TABLE_SIZE));
        $this->assertSame('ENABLE_PUSH', Settings::getName(Settings::ENABLE_PUSH));
        $this->assertSame('MAX_CONCURRENT_STREAMS', Settings::getName(Settings::MAX_CONCURRENT_STREAMS));
        $this->assertSame('INITIAL_WINDOW_SIZE', Settings::getName(Settings::INITIAL_WINDOW_SIZE));
        $this->assertSame('MAX_FRAME_SIZE', Settings::getName(Settings::MAX_FRAME_SIZE));
        $this->assertSame('MAX_HEADER_LIST_SIZE', Settings::getName(Settings::MAX_HEADER_LIST_SIZE));
        $this->assertSame('ENABLE_CONNECT_PROTOCOL', Settings::getName(Settings::ENABLE_CONNECT_PROTOCOL));
        $this->assertSame('UNKNOWN', Settings::getName(99)); // Unknown setting
    }
}