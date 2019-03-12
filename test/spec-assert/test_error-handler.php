<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 12/03/2019
 * Time: 13:07
 */

require __DIR__ . '/../../vendor/autoload.php';

echo "execution de : " . __FILE__;
echo PHP_EOL;


echo "test de capture de Throwable";
echo PHP_EOL;
try {
    trigger_error("erreur");
} catch (\Assert\Exception $e){}