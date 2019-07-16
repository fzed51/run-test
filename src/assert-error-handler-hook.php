<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 12/03/2019
 * Time: 11:29
 */

namespace Assert;

use RuntimeException;
use Throwable;
use function set_error_handler;

/**
 * Class Exception
 * @package Assert
 */
class Exception extends RuntimeException
{
    /**
     * Assert\Exception constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        if ($previous === null) {
            $backTrace = debug_backtrace();
            $lastBackTrace = $backTrace[1];
            if (isset($lastBackTrace['file'])) {
                $this->setFilePosition($lastBackTrace['file'], $lastBackTrace['line']);
            } elseif (isset($lastBackTrace['function']) && $lastBackTrace['function'] === 'Assert\{closure}') {
                $this->setFilePosition($lastBackTrace['args'][2], $lastBackTrace['args'][3]);
            }
        } else {
            $this->setFilePosition($previous->getFile(), $previous->getLine());
        }
    }

    public function setFilePosition(string $file, int $line): Exception
    {
        $this->file = $file;
        $this->line = $line;
        return $this;
    }
}

/**
 * Modification du error handler de PHP
 */
set_error_handler(static function ($severity, $message, $file, $line) {
    throw (new Exception($message, $severity))->setFilePosition($file, $line);
});

/**
 * Fonction de test basique
 * @param bool $boolTest
 * @param string $message
 */
function boolTest(bool $boolTest, string $message = 'doit etre vrai'): void
{
    if (!$boolTest) {
        throw new Exception($message);
    }
}

/**
 * Fonction interne de validation de schema
 * @param $structure
 * @param $data
 * @param string $location
 * @return array
 */
function ValidateDataSchema ($structure, $data, $location = '$'): array
{
    if (is_array($structure)) {
        $out = [];
        if (isAssoc($structure)) {
            if (!is_object($data)) {
                return [$location . " n'est pas une structure"];
            }
            $properties = array_keys((array)$data);
            foreach ($structure as $key => $value) {
                $isOptionnal = substr($key, -1) === '?';
                $datakey = $isOptionnal ? substr($key, 0, -1) : $key;
                if (in_array($datakey, $properties, true)) {
                    if (!$isOptionnal || $data->{$datakey} !== null) {
                        foreach (ValidateDataSchema($structure[$key], $data->{$datakey}, $location . '.' . $datakey) as $err) {
                            $out[] = $err;
                        }
                    }
                } elseif (!$isOptionnal) {
                    $out[] = "$datakey n'existe pas dans $location";
                }
            }
        } else {
            if (!is_array($data) || isAssoc($data)) {
                return [$location . " n'est pas un tableau"];
            }
            $nStruct = $structure[0];
            foreach ($data as $idx => $value) {
                foreach (ValidateDataSchema($nStruct, $value, $location . "[$idx]") as $err) {
                    $out[] = $err;
                }
            }
        }
        return $out;
    }
    $isNullable = substr($structure, -1) === '?';
    $structure = $isNullable ? substr($structure, 0, -1) : $structure;
    if ($isNullable && $data === null) {
        return [];
    }
    switch ($structure) {
        case 'int':
        case 'integer':
            if (!is_int($data)) {
                return [$location . " n'est pas de type integer"];
            }
            break;
        case 'double':
        case 'float':
        case 'real':
            if (!is_float($data)) {
                return [$location . " n'est pas de type double"];
            }
            break;
        case 'str':
        case 'string':
            if (!is_string($data)) {
                return [$location . " n'est pas de type string"];
            }
            break;
        case 'array':
            if (!is_array($data) || isAssoc($data)) {
                return [$location . " n'est pas un tableau"];
            }
            break;
        case 'object':
            if (!is_object($data)) {
                return [$location . " n'est pas une structure"];
            }
            break;
    }
    return [];
}

/**
 * Fonction interne de détection de tableau associatif
 * @param array $arr
 * @return bool
 */
function isAssoc(array $arr): bool
{
    if ([] === $arr) {
        return false;
    }
    return array_keys($arr) !== range(0, count($arr) - 1);
}

/**
 * Fonction de test de schema de JSON
 * @param $schema - description de la structure du JSON
 * @param string $jsonString - chaine de caractère du JSON
 * @param string $message
 */
function schemaJsonTest($schema, string $jsonString, string $message = 'structure JSON'): void
{
    $data = json_decode($jsonString, false);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception($message . ', la chaine de caractère n\'est pas un JSON valide');
    }
    $errs = ValidateDataSchema($schema, $data, '$');
    if (!empty($errs)) {
        throw new Exception($message . ', le schema du json n\'est pas valide, ' . implode(', ', $errs));
    }
}


/**
 * Fonction de test avec exception
 * @param callable $fn
 * @param \Exception $exceptionAttendu
 * @param string $message
 */
function throwTest(callable $fn, \Exception $exceptionAttendu, string $message = 'doit lever une exception'): void
{
    try {
        $fn();
    } catch (Throwable $t) {
        $classExceptionAttendu = get_class($exceptionAttendu);
        if (!is_a($t, $classExceptionAttendu)) {
            throw new Exception('[BAD EXCEPTION] ' . $message, $t->getCode(), $t);
        }
        return;
    }
    throw new Exception('[NO EXCEPTION] ' . $message);
}

/**
 * Fonction de test sans exception
 * @param callable $fn
 * @param string $message
 */
function noThrowTest(callable $fn, string $message = "ne doit pas lever d'exception"): void
{
    try {
        $fn();
    } catch (Throwable $t) {
        throw new Exception(
            $message .
            ' [' . get_class($t) . '(' . $t->getMessage() . ')]' .
            '-->(' . $t->getFile() . ':' . $t->getLine() . ')'
        );
    }
}

function instanceTest($object, string $className, string $message = ''): void
{
    if (!class_exists($className)) {
        throw new Exception("Le test n'est pas valide, " . $className . " n'est pas une classe.");
    }
    if (!is_a($object, $className)) {
        throw new Exception(!empty($message) ? $message : "L'objet testé n'est pas une instance de " . $className);
    }
}
