<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

final class ArrangementsNoRepeatTest extends TestCase
{
    private function collect(array $items, int $n): array
    {
        return iterator_to_array(arrangementsNoRepeat($items, $n), false);
    }

    public function testReturnsGenerator(): void
    {
        $result = $this->collect([1, 2, 3], 2);
        $this->assertIsArray($result);
    }

    public function testGeneratesCorrectPermutationsForSimpleCase(): void
    {
        $items = ['A', 'B', 'C'];
        $n = 2;

        $result = $this->collect($items, $n);

        $expected = [
            ['A', 'B'],
            ['A', 'C'],
            ['B', 'A'],
            ['B', 'C'],
            ['C', 'A'],
            ['C', 'B'],
        ];

        $this->assertSame($expected, $result);
    }

    public function testEachArrangementHasExactLengthN(): void
    {
        $items = [1, 2, 3, 4];
        $n = 3;

        foreach ($this->collect($items, $n) as $arrangement) {
            $this->assertCount($n, $arrangement);
        }
    }

    public function testNoRepeatedElementsInsideArrangement(): void
    {
        $items = [1, 2, 3];
        $n = 3;

        foreach ($this->collect($items, $n) as $arrangement) {
            /** @var array $arrangement */
            $this->assertCount(
                count(array_unique($arrangement)),
                $arrangement,
                'Arrangement contains duplicate values'
            );
        }

    }

    public function testCorrectNumberOfPermutations(): void
    {
        $items = [1, 2, 3, 4];
        $n = 2;

        $result = $this->collect($items, $n);

        // P(4,2) = 4 * 3 = 12
        $this->assertCount(12, $result);
    }

    public function testNEqualsZeroYieldsSingleEmptyPermutation(): void
    {
        $items = [1, 2, 3];

        $result = $this->collect($items, 0);

        $this->assertSame([[]], $result);
    }

    public function testNEqualsItemsCountYieldsAllPermutations(): void
    {
        $items = [1, 2, 3];

        $result = $this->collect($items, 3);

        // 3! = 6
        $this->assertCount(6, $result);
    }

    public function testNGreaterThanItemsCountYieldsEmptyGenerator(): void
    {
        $items = [1, 2];

        $result = $this->collect($items, 3);

        $this->assertSame([], $result);
    }

    public function testNegativeNYieldsEmptyGenerator(): void
    {
        $items = [1, 2, 3];

        $result =$this->collect($items, -1);

        $this->assertSame([], $result);
    }
}