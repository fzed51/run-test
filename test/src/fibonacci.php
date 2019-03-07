<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 07/03/2019
 * Time: 10:24
 */

function fibonacci(int $n): int
{
    if ($n == 0) {
        return 0;
    } elseif ($n == 1) {
        return $n;
    }
    return fibonacci($n - 1) + fibonacci($n - 2);
}