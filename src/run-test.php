<?php

/**
 * /!\ SIDEEFFECT /!\
 */

/**
 * Liste les dossiers de test *
 * @return array
 **/
function listDirectoryTest($argv) : array
{
    $dir = [];
    if (isset($argv[1])) {
        for ($i = 1; $i < count($argv); $i++) {
            if (is_dir($argv[$i])) {
                $dir[] = realpath($argv[$i]);
            }
        }
    }
    if (empty($dir)) {
        if (is_dir("./test")) {
            $dir[] = realpath("./test");
        }
    }
    if (empty($dir)) {
        $dir[] = realpath(getcwd());
        echo "/!\\ ATTENTION auccun dossier de test n'a été détecté." . PHP_EOL;
    }
    return $dir;
}

/**
 * Recherche les fichier de test (test_*.php)
 *
 * @param array $directories
 * @param bool $force
 * @return array
 */
function rechercheTest(array $directories, bool $force) : array
{
    $regex = $force
        ? '/ftest_[^.]+\\.php/i'
        : '/test_[^.]+\\.php/i';
    $listeTest = [];
    foreach ($directories as $directory) {
        foreach (scandir($directory) as $item) {
            if ($item == '.' || $item == '..' || $item == 'test_Run.php') {
                continue;
            }
            $fullItem = $directory . '/' . $item;
            if (is_dir($fullItem)) {
                foreach (rechercheTest([$fullItem], $force) as $test) {
                    $listeTest[] = $test;
                }
            } elseif (is_file($fullItem)) {
                if (preg_match($regex, $item)) {
                    $listeTest[] = $fullItem;
                }
            }
        }
    }
    return $listeTest;
}

/**
 * Démarre un chronomètre
 * @return callable
 */
function startChrono() : callable
{
    $start = microtime(true);
    /**
     * Mesure le temps depuis le début du chrono
     * @return float
     */
    return function () use ($start) : float {
        $end = microtime(true);
        return round($end - $start, 3);
    };
}

/**
 * format un tableau de chaine en tableau de chaine ne depassant
 * pas x caractères
 * @param mixed $input
 * @param integer $nbCar
 * @return array
 * @throws Exception
 */
function formatNbCar($input, int $nbCar) : array
{
    if (is_array($input)) {

        return array_reduce(
            $input,
            function (array $output, string $line) use ($nbCar) {
                $subOutput = explode("\n", wordwrap($line, $nbCar, "\n", true));
                return $output = array_merge($output, $subOutput);
            },
            []
        );
    } elseif (is_string($input)) {
        return explode("\n", wordwrap($input, $nbCar, "\n", true));
    }
    throw new InvalidArgumentException("Le paramètre 1 de " . __FUNCTION__ . " n'est pas pris en compte");
}

/**
 * colorise du texte pour la console au standart ANSI
 * @param string $code
 * @param string $message
 * @return string
 */
function printColor($code, $message)
{
    $codes = [
        "Black" => ["FG" => 30, "BG" => 40],
        "Red" => ["FG" => 31, "BG" => 41],
        "Green" => ["FG" => 32, "BG" => 42],
        "Yellow" => ["FG" => 33, "BG" => 43],
        "Blue" => ["FG" => 34, "BG" => 44],
        "Magenta" => ["FG" => 35, "BG" => 45],
        "Cyan" => ["FG" => 36, "BG" => 46],
        "White" => ["FG" => 37, "BG" => 47],
        "LightBlack" => ["FG" => 90, "BG" => 100],
        "LightRed" => ["FG" => 91, "BG" => 101],
        "LightGreen" => ["FG" => 92, "BG" => 102],
        "LightYellow" => ["FG" => 93, "BG" => 103],
        "LightBlue" => ["FG" => 94, "BG" => 104],
        "LightMagenta" => ["FG" => 95, "BG" => 105],
        "LightCyan" => ["FG" => 96, "BG" => 106],
        "LightWhite" => ["FG" => 97, "BG" => 107]
    ];
    if (isset($codes[$code])) {
        return chr(27) . "[" . $codes[$code]['FG'] . 'm' . $message . chr(27) . "[0m";
    } else {
        return $message;
    }
}

$listeDirectory = listDirectoryTest($argv);
$listeTest = rechercheTest($listeDirectory, true);
if (empty($listeTest)) {
    $listeTest = rechercheTest($listeDirectory, false);
}

foreach ($listeTest as $test) {
    $commande = "php -dlog_errors=0 -ddisplay_errors=1 -dxdebug.profiler_enable=1 \"$test\"";
    echo "\u{250C}\u{2500}< " . printColor('Cyan', $test) . PHP_EOL;

    $chrono = startChrono();
    $output = [];
    exec("$commande 2>&1", $output, $retour);
    $time = $chrono();

    echo "\u{2502} " . join(PHP_EOL . "\u{2502} ", formatNbCar($output, 110)) . PHP_EOL;

    if ($retour !== 0) {
        echo "\u{2514}\u{2500}> ({$time}s) " . printColor('Red', 'FAIL') . PHP_EOL;
    } else {
        echo "\u{2514}\u{2500}> ({$time}s) " . printColor('Green', 'PASS') . PHP_EOL;
    }
}
