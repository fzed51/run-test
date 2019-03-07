<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 07/03/2019
 * Time: 11:25
 */

function pgcd(int $a, int $b): int
{
    while ($b !== 0) {
        if ($a > $b) {
            $c = $a;
            $a = $b;
            $b = $c;
        }
        $b -= $a;
    }
    return $a;
}