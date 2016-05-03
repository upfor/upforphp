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

use InvalidArgumentException;
use Upfor\Interfaces\LogWriterInterface;

/**
 * Logger
 */
class Logger {

    //Log Level
    const EMERGENCY = 8;
    const ALERT = 7;
    const CRITICAL = 6;
    const ERROR = 5;
    const WARNING = 4;
    const NOTICE = 3;
    const INFO = 2;
    const DEBUG = 1;

    /**
     * @var array
     */
    public static $levels = array(
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT => 'ALERT',
        self::CRITICAL => 'CRITICAL',
        self::ERROR => 'ERROR',
        self::WARNING => 'WARNING',
        self::NOTICE => 'NOTICE',
        self::INFO => 'INFO',
        self::DEBUG => 'DEBUG',
    );

    /**
     * @var mixed
     */
    protected $writer;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * Constructor
     * 
     * @param  mixed $writer
     */
    public function __construct(LogWriterInterface $writer) {
        $this->writer = $writer;
    }

    /**
     * Set is enabled for logging
     * @param bool $enabled
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled ? true : false;
    }

    /**
     * Is logging enabled?
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array()) {
        return $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array()) {
        return $this->log(self::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array()) {
        return $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array()) {
        return $this->log(self::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array()) {
        return $this->log(self::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array()) {
        return $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array()) {
        return $this->log(self::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array()) {
        return $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log message
     * @param  mixed       $level
     * @param  mixed       $message
     * @param  array       $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     * @throws InvalidArgumentException If invalid log level
     */
    public function log($level, $message, $context = array()) {
        if (!isset(self::$levels[$level])) {
            throw new InvalidArgumentException('Invalid log level supplied to function');
        }

        if ($this->enabled && $this->writer) {
            $message = (string) $message;
            if (count($context) > 0) {
                if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
                    $message .= ' - ' . $context['exception'];
                    unset($context['exception']);
                }
                $message = $this->interpolate($message, $context);
            }
            return $this->writer->write($message, $level);
        }

        return false;
    }

    /**
     * Interpolates context values into the message placeholders.
     * @param  mixed     $message               The log message
     * @param  array     $context               An array of placeholder values
     * @return string    The processed string
     */
    public function interpolate($message, array $context = array()) {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

}
