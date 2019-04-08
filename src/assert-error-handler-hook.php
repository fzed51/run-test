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
