<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

class MbStrPadRightTest extends TestCase
{
    public function testPadsAsciiStringWithSpaces(): void
    {
        $this->assertSame(
            'abc  ',
            mb_str_pad_right('abc', 5)
        );
    }

    public function testPadsMultibyteStringCorrectly(): void
    {
        $this->assertSame(
            'тест  ',
            mb_str_pad_right('тест', 6)
        );
    }

    public function testReturnsOriginalStringIfAlreadyLongEnough(): void
    {
        $this->assertSame(
            'hello',
            mb_str_pad_right('hello', 3)
        );
    }

    public function testPadsWithCustomCharacter(): void
    {
        $this->assertSame(
            'a...',
            mb_str_pad_right('a', 4, '.')
        );
    }

    public function testZeroWidthReturnsOriginalString(): void
    {
        $this->assertSame(
            'abc',
            mb_str_pad_right('abc', 0)
        );
    }
}