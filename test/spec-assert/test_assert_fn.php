<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 12/03/2019
 * Time: 11:47
 */

require __DIR__ . '/../../vendor/autoload.php';

echo "execution de : " . __FILE__;
echo PHP_EOL;

echo 'Test de Assert\boolTest';
echo PHP_EOL;
Assert\boolTest(true);
try {
    Assert\boolTest(false);
    throw \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) {
}

echo 'Test de Assert\throwTest';
echo PHP_EOL;
Assert\throwTest(function () {
    throw new \Exception("test");
}, new \Exception());

class A extends \Exception
{
}

class B extends \Exception
{
}

try {
    Assert\throwTest(function () {
    }, new \Exception());
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) {
}
try {
    Assert\throwTest(function () {
        throw new \A();
    }, new \B());
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) {
}

echo 'Test de Assert\noThrowTest';
echo PHP_EOL;
Assert\noThrowTest(function () {
});
try {
    Assert\noThrowTest(function () {
        throw new \Exception();
    });
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) {
}