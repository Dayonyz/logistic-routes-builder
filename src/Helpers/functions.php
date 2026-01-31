<?php

/**
 * Permutations of length $n from $items (order matters), without repetitions.
 * Iterates over a given sequence (array of values).
 * @param array<int, mixed> $items
 * @param int $n
 * @return Generator<int, array<int, mixed>>
 */

function arrangementsNoRepeat(array $items, int $n): Generator
{
    $m = count($items);
    if ($n < 0 || $n > $m) return;

    $used = array_fill(0, $m, false);
    $idx  = array_fill(0, $n, 0);

    $dfs = function(int $depth) use (&$dfs, $n, $m, &$used, &$idx, $items): Generator {
        if ($depth === $n) {
            $out = [];
            for ($k = 0; $k < $n; $k++) $out[$k] = $items[$idx[$k]];
            yield $out;
            return;
        }

        for ($i = 0; $i < $m; $i++) {
            if ($used[$i]) continue;
            $used[$i] = true;
            $idx[$depth] = $i;

            yield from $dfs($depth + 1);

            $used[$i] = false;
        }
    };

    yield from $dfs(0);
}

function mb_str_pad_right(string $s, int $width, string $pad = ' ', string $enc = 'UTF-8'): string
{
    $len = mb_strlen($s, $enc);
    if ($len >= $width) return $s;

    return $s . str_repeat($pad, $width - $len);
}
