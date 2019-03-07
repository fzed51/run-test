<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 06/03/2019
 * Time: 14:02
 */


/**
 * @param array $codeCoverageListe - liste des fichiers couverts
 * @param array $blackListePath - liste des chemins exclus
 * @return array  - liste des fichiers couverts non exclus
 */
function filterCodeCoverage(array $codeCoverageListe, array $blackListePath)
{
    $codeCoverageListeFiltred = [];
    $files = array_keys($codeCoverageListe);
    foreach ($files as $file) {
        $found = $file === __FILE__;
        foreach ($blackListePath as $black) {
            if ($found || ($black === false)) {
                continue;
            }
            $found = $found || (strpos($file, $black) === 0);
        }
        if (!$found) {
            $codeCoverageListeFiltred[$file] = $codeCoverageListe[$file];
        }
    }
    return $codeCoverageListeFiltred;
}

$path = realpath($argv[1]);

$blacklist_path = [
    realpath('./vendor/'),
    realpath('./spec/'),
    realpath('./test/')
];

xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);

include $path;

$cc = filterCodeCoverage(xdebug_get_code_coverage(), $blacklist_path);

file_put_contents('./code-coverage/cc_' . md5($path) . '.json', json_encode($cc, JSON_PRETTY_PRINT));
