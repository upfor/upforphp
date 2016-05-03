<?php

/**
 * Upfor Framework
 *
 * @author      Shocker Li <shocker@upfor.club>
 * @copyright   Upfor Group
 * @link        http://framework.upfor.club
 * @license     MIT
 */

namespace Upfor\Interfaces;

/**
 * LogWriterInterface
 */
interface LogWriterInterface {

    /**
     * Constructor
     * @param array $setting
     */
    public function __construct($setting);

    /**
     * Write message
     * @param  mixed     $message
     * @param  int       $level
     * @return int|bool
     */
    public function write($message, $level);
}
