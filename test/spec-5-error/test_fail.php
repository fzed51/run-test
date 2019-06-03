<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 07/03/2019
 * Time: 13:32
 */

echo 'test FAIL' . PHP_EOL;
throw new \Exception('Ce message doit être affiché.');