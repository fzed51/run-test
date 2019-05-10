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

///** @noinspection TypeUnsafeComparisonInspection */
//if (getenv('RUNTEST') !== false && (
//        strtolower(getenv('RUNTEST')) === 'on'
//        || strtolower(getenv('RUNTEST')) === 'yes'
//        || getenv('RUNTEST') === true
//        || getenv('RUNTEST') == 1
//    )) {

    /**
     * Class Exception
     * @package Assert
     */
class Exception extends RuntimeException
    {
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

set_error_handler(function ($severity, $message, $file, $line) {
    throw (new Exception($message, $severity))->setFilePosition($file, $line);
});

/**
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
            if (!is_array($data) || !isAssoc($data)) {
                return [$location . " n'est pas une structure"];
            }
            foreach ($structure as $key => $value) {
                $isOptionnal = substr($key, -1) === '?';
                $datakey = $isOptionnal ? substr($key, 0, -1) : $key;
                if (array_key_exists($datakey, $data) && !($data[$datakey] === null && is_array($structure[$key]))) {
                    foreach (ValidateDataSchema($structure[$key], $data[$datakey], $location . '.' . $key) as $err) {
                        $out[] = $err;
                    }
                } elseif (!$isOptionnal) {
                    $out[] = "$key n'existe pas dans $location";
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
            if (!is_array($data) || !isAssoc($data)) {
                return [$location . " n'est pas une structure"];
            }
            break;
    }
    return [];
}

/**
 * @param $schema - description de la structure du JSON
 * @param string $jsonString - chaine de caractère du JSON
 * @param string $message
 */
function schemaJsonTest($schema, string $jsonString, string $message = 'structure JSON'): void
{
    $data = json_decode($jsonString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception($message . ', la chaine de caractère n\'est pas un JSON valide');
    }
    $errs = ValidateDataSchema($schema, $data, '$');
    if (!empty($errs)) {
        throw new Exception($message . ', le schema du json n\'est pas valide, ' . implode(', ', $errs));
    }
}


/**
     * @param callable $fn
     * @param \Exception $exceptionAttendu
     * @param string $message
     */
    function throwTest(callable $fn, \Exception $exceptionAttendu, string $message = 'doit lever une exception'): void
    {
        try {
            $fn();
            throw new Exception('[NO EXCEPTION] ' . $message);
        } catch (Throwable $t) {
            $classExceptionAttendu = get_class($exceptionAttendu);
            $classException = get_class($t);
            $parentsClassException = class_parents($t);
            if (!($classExceptionAttendu === $classException) || in_array($classExceptionAttendu, $parentsClassException, true)) {
                throw new Exception('[BAD EXCEPTION] ' . $message, $t->getCode(), $t);
            }
        }
    }


function isAssoc(array $arr)
{
    if ([] === $arr) {
        return false;
    }
    return array_keys($arr) !== range(0, count($arr) - 1);
}

    /**
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

//}
