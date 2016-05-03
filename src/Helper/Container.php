<?php

/**
 * Upfor Framework
 *
 * @author      Shocker Li <shocker@upfor.club>
 * @copyright   Upfor Group
 * @link        http://framework.upfor.club
 * @license     MIT
 */

namespace Upfor\Helper;

use Upfor\Helper\Collection;

/**
 * Container
 */
class Container extends Collection {

    /**
     * ArrayAccess Overloading
     */
    public function offsetGet($key) {
        if (!$this->offsetExists($key)) {
            return false;
        }

        $isInvokable = is_object($this->data[$key]) && method_exists($this->data[$key], '__invoke');

        return $isInvokable ? $this->data[$key]($this) : $this->data[$key];
    }

    /**
     * Ensure a value or object will remain globally unique
     * @param  string  $key   The value or object name
     * @param  Closure        The closure that defines the object
     * @return mixed
     */
    public function singleton($key, $value) {
        $this->offsetSet($key, function ($c) use ($value) {
            static $object;

            if (null === $object) {
                $object = $value($c);
            }

            return $object;
        });
    }

}
