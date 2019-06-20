<?php

use Console\Options\Option;
use Console\Options\OptionParser;

/**
 * /!\ SIDEEFFECT /!\
 */

$options = new OptionParser([
    (new Option('trace', 't'))->setType(Option::T_FLAG),
    (new Option('profile', 'p'))->setType(Option::T_FLAG),
    (new Option('coverage', 'c'))->setType(Option::T_FLAG),
    (new Option('monochrome', 'm'))->setType(Option::T_FLAG),
    (new Option('last', 'l'))->setType(Option::T_INTEGER),
    (new Option('filtre', 'f'))->setType(Option::T_STRING),
    (new Option('quiet', 'q'))->setType(Option::T_FLAG),
]);
$options->parse($argv);

/**
 * Liste les dossiers de test *
 * @param array $dirPath
 * @return array
 */
function listDirectoryTest(array $dirPath = []) : array
{

    if (empty($dirPath)) {
        $dirPath[] = 'test';
    }
    $dir = [];
    foreach ($dirPath as $iValue) {
        $path = $iValue;
        if (is_dir($path)) {
            $dir[] = realpath($path);
        } else {
            echo "'$path' n'est pas un dossier valide" . PHP_EOL;
        }
    }

    if (empty($dir)) {
        echo "/!\\ ATTENTION auccun dossier de test n'a été détecté." . PHP_EOL;
    }
    return $dir;
}

/**
 * liste les fichier d'un dossier en filtrant les extensions
 * @param string $directory
 * @param string $extension
 * @return array
 */
function rechercheFichier(string $directory, string $extension): array
{
    $regex = "/\\.$extension$/i";
    $files = [];
    foreach (scandir($directory, SCANDIR_SORT_NONE) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $fullItem = $directory . '/' . $item;
        if (is_dir($fullItem)) {
            foreach (rechercheFichier([$fullItem], $extension) as $fichier) {
                $files[] = $fichier;
            }
        } elseif (is_file($fullItem)) {
            if (preg_match($regex, $item)) {
                $files[] = $fullItem;
            }
        }
    }
    return $files;
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
        foreach (scandir($directory, SCANDIR_SORT_NONE) as $item) {
            if ($item === '.' || $item === '..') {
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
    return static function () use ($start) : float {
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
            static function (array $output, string $line) use ($nbCar) {
                $subOutput = explode("\n", wordwrap($line, $nbCar, "\n", true));
                return array_merge($output, $subOutput);
            },
            []
        );
    }
    return explode("\n", wordwrap((string)$input, $nbCar, "\n", true));
}

/**
 * colorise du texte pour la console au standart ANSI
 * @param string $code
 * @param string $message
 * @return string
 */
function printColor($code, $message)
{
    $monochrome = defined('MONOCHROME') ? MONOCHROME : false;
    $codes = [
        'Black' => ['FG' => 30, 'BG' => 40],
        'Red' => ['FG' => 31, 'BG' => 41],
        'Green' => ['FG' => 32, 'BG' => 42],
        'Yellow' => ['FG' => 33, 'BG' => 43],
        'Blue' => ['FG' => 34, 'BG' => 44],
        'Magenta' => ['FG' => 35, 'BG' => 45],
        'Cyan' => ['FG' => 36, 'BG' => 46],
        'White' => ['FG' => 37, 'BG' => 47],
        'LightBlack' => ['FG' => 90, 'BG' => 100],
        'LightRed' => ['FG' => 91, 'BG' => 101],
        'LightGreen' => ['FG' => 92, 'BG' => 102],
        'LightYellow' => ['FG' => 93, 'BG' => 103],
        'LightBlue' => ['FG' => 94, 'BG' => 104],
        'LightMagenta' => ['FG' => 95, 'BG' => 105],
        'LightCyan' => ['FG' => 96, 'BG' => 106],
        'LightWhite' => ['FG' => 97, 'BG' => 107]
    ];
    if (!$monochrome && isset($codes[$code])) {
        return chr(27) . '[' . $codes[$code]['FG'] . 'm' . $message . chr(27) . '[0m';
    }
    return $message;
}

/**
 * transforme un tableau d'option en chaine
 * @param array $arrayOpt tableau associatif d'options
 * @return string
 */
function array2Options(array $arrayOpt) : string
{
    $lstOpt = [];
    foreach ($arrayOpt as $opt => $value) {
        $lstOpt[] = "-d$opt=$value";
    }
    return implode(' ', $lstOpt);
}

/**
 * créer un dossier
 * @param $name
 */
function creerDossier($name)
{
    if (!is_dir($name)) {
        if (!mkdir($name) && !is_dir($name)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $name));
        }
        echo "Le dossier '$name' a été créé" . PHP_EOL;
    }
}

/**
 * supprimer tous les fichier d'un dossiier
 * @param $name
 */
function netoyerDossier($name)
{
    if (is_dir($name)) {
        $files = array_diff(scandir($name, SCANDIR_SORT_NONE), ['.', '..']);
        foreach ($files as $file) {
            if (is_file("$name/$file")) {
                unlink("$name/$file");
            }
        }
        echo "Le dossier '$name' a été netoyé" . PHP_EOL;
    }
}

/**
 * garder les n dernier fichier modifié
 * @param array $listeFichier
 * @param int $nDernier
 * @return array
 */
function filtreNDernier(array $listeFichier, int $nDernier)
{
    $listeFichierInfo = array_map(
        static function (string $fichier): array {
            return [$fichier, filemtime($fichier)];
        },
        $listeFichier
    );
    usort(
        $listeFichierInfo,
        static function ($item1, $item2) {
            return $item1[1] <=> $item2[1];
        }
    );
    $listeFichierInfo = array_slice($listeFichierInfo, -1 * $nDernier);
    return array_map(
        static function (array $info): string {
            return $info[0];
        },
        $listeFichierInfo
    );
}

/**
 * filtre les noms de test
 * @param array $listeFichier
 * @param string $filtre
 * @return array
 */
function filtreNom(array $listeFichier, string $filtre)
{
    return array_filter($listeFichier, static function ($fichier) use ($filtre) {
        $name = strtolower(basename($fichier, '.php'));
        $filtre = strtolower($filtre);
        return strpos($name, $filtre) !== false;
    });
}

/**
 * donne le nombre de ligne dans un fichier
 * @param string $fileName
 * @return int
 */
function getNumberOfLinesInFile(string $fileName): int
{
    $buffer = file_get_contents($fileName);
    $lines = substr_count($buffer, "\n");
    if (substr($buffer, -1) !== "\n") {
        $lines++;
    }
    return $lines;
}

/**
 * donne les statistique de couverture du code
 * @return array
 */
function getStatCodeCoverage(): array
{
    $statCcFile = rechercheFichier('./code-coverage', 'json');
    $stats = [];
    foreach ($statCcFile as $fileCc) {
        $cc = json_decode(file_get_contents($fileCc));
        foreach ($cc as $file => $lines) {
            $stat = $stats[realpath($file)] ?? [];
            foreach ($lines as $line => $cover) {
                if ($cover > 0) {
                    $stat[$line] = 1;
                }else {
                    $stat[$line] = $stat[$line] ?? 0;
                }
            }
            $stats[realpath($file)] = $stat;
        }
    }
    foreach ($stats as $file => $stat) {
        $nbLigne = count($stat);
        $nbCover = count(array_filter($stat, static function ($i) {
            return $i > 0;
        }));
        $stats[$file] = round($nbCover * 100 / $nbLigne);
    }
    return $stats;
}

if ($options['monochrome']) {
    define('MONOCHROME', true);
} else {
    define('MONOCHROME', false);
}

$listeDirectory = listDirectoryTest($options->getParameters());
$listeTestForce = rechercheTest($listeDirectory, true);
$listeTestClassic = rechercheTest($listeDirectory, false);
if (empty($listeTestForce)) {
    $listeTest = $listeTestClassic;
} else {
    $listeTest = $listeTestForce;
}

if (isset($options['filtre'])) {
    $listeTest = filtreNom($listeTest, $options['filtre']);
}

if (isset($options['last'])) {
    $listeTest = filtreNdernier($listeTest, $options['last']);
}
$assertErrorHandler = realpath(__DIR__ . '/assert-error-handler-hook.php');
$optionPhp = [
    'auto_prepend_file' =>  "\"$assertErrorHandler\"",
    'log_errors' => 0,
    'display_errors' => 1,
    'xdebug.cli_color' => MONOCHROME ? 0 : 2,
    'xdebug.default_enable' => 1,
    'xdebug.remote_enable' => 1,
    'xdebug.remote_connect_back' => 1,
    'xdebug.remote_autostart' => 1,
];
if ($options['profile']) {
    $profileDir = getcwd() . '/profile';
    creerDossier($profileDir);

    $optionPhp['xdebug.profiler_enable'] = 1;
    $optionPhp['xdebug.profiler_output_dir'] = $profileDir;
}
if ($options['trace']) {
    $traceDir = getcwd() . '/trace';
    creerDossier($traceDir);
    $optionPhp['xdebug.auto_trace'] = 1;
    $optionPhp['xdebug.trace_output_dir'] = $profileDir;
}
$codecoverage = '';
if ($options['coverage']) {
    $codecoverage = '"' . __DIR__ . '/code-coverage-hook.php' . '" ';
    $codeCoverageDir = getcwd() . '/code-coverage';
    creerDossier($codeCoverageDir);
    netoyerDossier($codeCoverageDir);
}

$optionPhpStr = array2Options($optionPhp);
$succes = 0;
$fail = 0;
putenv('RUNTEST=On');
$masterChrono = startChrono();
$quietLog = "";
foreach ($listeTest as $test) {
    $localQuietLog = '';
    $commande = "php $optionPhpStr $codecoverage\"$test\"";
    if ($options['quiet']) {
        $localQuietLog .= "\u{250C}\u{2500}< " . printColor('Cyan', $test) . PHP_EOL;
    } else {
        echo "\u{250C}\u{2500}< " . printColor('Cyan', $test) . PHP_EOL;
    }

    $output = [];
    $chrono = startChrono();
    exec("$commande 2>&1", $output, $retour);
    $time = $chrono();

    if(!empty($output)){
        if ($options['quiet']) {
            $localQuietLog .= "\u{2502} " . implode(PHP_EOL . "\u{2502} ", formatNbCar($output, 110)) . PHP_EOL;
        } else {
            echo "\u{2502} " . implode(PHP_EOL . "\u{2502} ", formatNbCar($output, 110)) . PHP_EOL;
        }
    }

    if ($retour !== 0) {
        if ($options['quiet']) {
            $localQuietLog .= "\u{2514}\u{2500}> ({$time}s) " . printColor('Red', 'FAIL') . PHP_EOL;
            $quietLog .= $localQuietLog;
            echo printColor('Red', '*');
        } else {
            echo "\u{2514}\u{2500}> ({$time}s) " . printColor('Red', 'FAIL') . PHP_EOL;
        }
        $fail++;
    } else {
        if ($options['quiet']) {
            $localQuietLog .= "\u{2514}\u{2500}> ({$time}s) " . printColor('Green', 'PASS') . PHP_EOL;
            echo printColor('Green', '.');
        } else {
            echo "\u{2514}\u{2500}> ({$time}s) " . printColor('Green', 'PASS') . PHP_EOL;
        }
        $succes++;
    }
}
$masterTime = $masterChrono();
putenv('RUNTEST');

if ($options['quiet']) {
    echo PHP_EOL;
    echo $quietLog;
}

/* Rapport de test */
$nbTest = count($listeTestForce) + count($listeTestClassic);
$nbskip = $nbTest - ($fail + $succes);
echo 'Tests terminés : ';
if($succes > 0) {
    echo printColor('Green', $succes) . ' succes ';
}
if($fail > 0) {
    echo printColor('Red', $fail) . ' test(s) KO ';
}
if($nbskip > 0) {
    echo printColor('Yellow', $nbskip) . ' non testé(s) ';
}
echo '/ ' . printColor('Cyan', $nbTest) . ' tests' . PHP_EOL;
echo "test(s) effectué(s) en $masterTime s." . PHP_EOL;


if ($options['coverage']) {
    $cc = getStatCodeCoverage();
    ksort($cc);
    echo 'CODE COVERAGE' . PHP_EOL;
    echo '--------------' . PHP_EOL;
    $nbFile = 0;
    $totalPoucent = 0;
    foreach ($cc as $file => $pourcent) {
        echo $file . ' >> ' . $pourcent . '%' . PHP_EOL;
        $nbFile++;
        $totalPoucent += (int)$pourcent;
    }
    echo 'Couverture du code moyenne : '
        . round($totalPoucent / $nbFile, 2)
        . '% pour ' . $nbFile . ' fichier(s).' . PHP_EOL;
}
