<?php
declare(strict_types=1);

use function Assert\InstanceTest;

/**
 * User: Fabien Sanchez
 * Date: 16/07/2019
 * Time: 13:58
 */
class A
{
}

class B extends A
{
}

class C
{
}

function msg($msg)
{
    echo $msg . PHP_EOL;
}

$a = new A();
$b = new B();
$c = new C();

InstanceTest($a, A::class);
InstanceTest($b, B::class);
InstanceTest($b, A::class);

try {
    InstanceTest($c, A::class);
} catch (Throwable $t) {
    if (!is_a($t, \Assert\Exception::class)) {
        throw $t;
    }
    msg('Exception normale levée : ' . $t->getMessage());
}

try {
    InstanceTest($c, 'AzeClass');
} catch (Throwable $t) {
    if (!is_a($t, \Assert\Exception::class)) {
        throw $t;
    }
    msg('Exception normale levée : ' . $t->getMessage());
}