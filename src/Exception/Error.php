<?php

/**
 * Upfor Framework
 *
 * @author      Shocker Li <shocker@upfor.club>
 * @copyright   Upfor Group
 * @link        http://framework.upfor.club
 * @license     MIT
 */

namespace Upfor\Exception;

use Exception;
use ErrorException;
use Upfor\Exception\Logger;

/**
 * Error
 */
class Error {

    private $display = false;
    private $logger;
    private static $errorType = array(
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    );

    /**
     * Registers a new ErrorHandler for a given Logger
     *
     * @param  Logger $logger
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;

        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
        register_shutdown_function(array($this, 'handleFatalError'));
    }

    public function unregister() {
        restore_error_handler();
        restore_exception_handler();
    }

    public function handleError($errno, $errstr = '', $errfile = '', $errline = '') {
        if (error_reporting() & $errno) {
            throw new ErrorException($errstr, $errno, Logger::ERROR, $errfile, $errline);
        }
    }

    public function handleException(Exception $e) {
        $this->logger->log(
                $e->getCode() && isset(Logger::$levels[$e->getCode()]) ? $e->getCode() : Logger::ERROR, sprintf('%s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine())
        );

        if ($this->display) {
            self::halt($e);
        }
    }

    public function handleFatalError() {
        $lastError = error_get_last();
        if ($lastError) {
            $message = 'Fatal Error (' . self::codeToString($lastError['type']) . '): ' . $lastError['message'];
            $exception = new ErrorException($message, $lastError['type'], Logger::ERROR, $lastError['file'], $lastError['line']);
            $this->handleException($exception);
        }
    }

    private static function codeToString($code) {
        $error = 'Unknown PHP error';
        if (isset(self::$errorType[$code])) {
            $error = self::$errorType[$code];
        }

        return $error;
    }

    public function isDisplay($isDisplay) {
        $this->display = $isDisplay;
    }

    public static function halt($exception) {
        $title = 'Upfor Application Error';
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = str_replace(array('#', "\n"), array('<div>#', '</div>'), $exception->getTraceAsString());
        $html = sprintf('<h1>%s</h1>', $title);
        $html .= '<p>The application could not run because of the following error:</p>';
        $html .= '<h2>Details</h2>';
        $html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));
        if ($code) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }
        if ($message) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
        }
        if ($file) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }
        if ($line) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }
        if ($trace) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', $trace);
        }

        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        echo sprintf("<html><head><title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body>%s</body></html>", $title, $html);
        exit;
    }

}
