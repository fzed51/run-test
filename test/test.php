<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 07/03/2019
 * Time: 08:47
 */

chdir(__DIR__);

function execute_run_test($test_path, array $options)
{
    $commande = "php \"../bin/run-test\" \"$test_path\"";
    $strOptions = '';
    foreach ($options as $k => $v) {
        $strOptions .= !empty($strOptions) ? ' ' : '';
        if (is_int($k)) {
            $strOptions .= '-' . $v;
        } else {
            $strOptions .= '-' . $k;
            if (strpos($v, ' ') !== false) {
                $strOptions .= ' ' . $v;
            } else {
                $strOptions .= ' "' . $v . '"';
            }
        }
    }
    echo shell_exec($commande) . PHP_EOL;
}

function message($message)
{
    echo $message . PHP_EOL;
}

message("exécution de run-test");
execute_run_test('./spec');

message("exécution de run-test avec des tests forcés");
execute_run_test('./spec-2');
