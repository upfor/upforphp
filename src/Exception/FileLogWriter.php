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

use Upfor\Interfaces\LogWriterInterface;

/**
 * FileLogWriter
 */
class FileLogWriter implements LogWriterInterface {

    private $setting;
    private $handle;

    /**
     * Constructor
     * @param array $setting
     */
    public function __construct($setting = array()) {
        $this->setting = array_merge(array(
            'path' => './log',
            'delimiter' => "\t",
                ), $setting);
    }

    /**
     * Write message
     * @param  mixed     $message
     * @param  int       $level
     * @return int|bool
     */
    public function write($message, $level = null) {
        if (!$this->handle) {
            if (!is_dir($this->setting['path'])) {
                mkdir($this->setting['path'], 0744, true);
            }

            $logFile = $this->setting['path'] . '/' . date('Y-m-d') . '.log';
            if (is_file($logFile) && !is_writable($logFile)) {
                chmod($logFile, 0744);
            }

            $this->handle = fopen($logFile, 'a');
        }

        $flag = 'DEBUG';
        if ($level && isset(Logger::$levels[$level])) {
            $flag = Logger::$levels[$level];
        }
        $content = array($flag, date('c'), (string) $message);

        fwrite($this->handle, implode($this->setting['delimiter'], $content) . PHP_EOL);
    }

}
