<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 07/03/2019
 * Time: 08:48
 */

echo 'execution de : ' . __FILE__;
echo PHP_EOL;
echo 'variable d\'environement : RUNTEST = ' . getenv('RUNTEST');
echo PHP_EOL;

throw new \Exception('ce fichier ne doit pas etre execute');
