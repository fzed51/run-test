<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 07/03/2019
 * Time: 13:32
 */

require './src/pgcd.php';

echo 'le pgcd de de 135 et de 90' . PHP_EOL;
echo 'test : ' . pgcd(135, 90) . PHP_EOL;
echo 'attendu : 45'. PHP_EOL;