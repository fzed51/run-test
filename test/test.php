<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 07/03/2019
 * Time: 08:47
 */

chdir(__DIR__);

/**
 * @param $test_path - chemin du dossier de test
 * @param array $options - option eventuelles de run-test
 */
function execute_run_test($test_path, array $options = [])
{
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
    $commande = "php \"../bin/run-test\" $strOptions \"$test_path\"";
    echo $commande . PHP_EOL;
    echo shell_exec($commande) . PHP_EOL;
}

/**
 * @param $message
 */
function message($message)
{
    echo $message . PHP_EOL;
}

message('exécution de run-test');
execute_run_test('./spec');

message('exécution de run-test sans couleurs');
execute_run_test('./spec', ['m']);

message('exécution de run-test avec des tests forcés');
execute_run_test('./spec-2');

message('exécution de run-test avec code coverage');
execute_run_test('./spec-3', ['c']);

message("exécution de run-test avec les fonctions d'assert");
execute_run_test('./spec-assert');
