<?php

/**
 * Upfor Framework
 *
 * @author      Shocker Li <shocker@upfor.club>
 * @copyright   Upfor Group
 * @link        http://framework.upfor.club
 * @license     MIT
 */

namespace Upfor\Views;

use Smarty;
use RuntimeException;
use Upfor\View;

/**
 * Smarty
 */
class Smarty extends View {

    /**
     * @var string The path to the Smarty code directory WITHOUT the trailing slash
     * @access public
     */
    public $parserDirectory = null;

    /**
     * @var string The path to the Smarty compiled templates folder WITHOUT the trailing slash
     * @access public
     */
    public $parserCompileDirectory = null;

    /**
     * @var string The path to the Smarty cache folder WITHOUT the trailing slash
     * @access public
     */
    public $parserCacheDirectory = null;

    /**
     * @var SmartyExtensions The Smarty extensions directory you want to load plugins from
     * @access public
     */
    public $parserExtensions = array();

    /**
     * @var parserInstance persistent instance of the Parser object.
     * @access private
     */
    private $parserInstance = null;

    public function __construct($settings = array()) {
        parent::__construct();
        isset($settings['parserDirectory']) && $this->parserDirectory = $settings['parserDirectory'];
        isset($settings['parserCompileDirectory']) && $this->parserCompileDirectory = $settings['parserCompileDirectory'];
        isset($settings['parserCacheDirectory']) && $this->parserCacheDirectory = $settings['parserCacheDirectory'];
        isset($settings['parserExtensions']) && $this->parserExtensions = $settings['parserExtensions'];

        isset($settings['publicDirectory']) && $this->set('publicDir', $settings['publicDirectory']);
        isset($settings['leftDelimiter']) && $this->leftDelimiter = $settings['leftDelimiter'];
        isset($settings['rightDelimiter']) && $this->rightDelimiter = $settings['rightDelimiter'];
    }

    /**
     * Render Template
     *
     * This method will output the rendered template content
     *
     * @param string $template The path to the template, relative to the templates directory.
     * @param null $data
     * @return string
     */
    public function render($template, array $data = array()) {
        $parser = $this->getInstance();
        return $parser->fetch($template, array_merge($this->all(), (array) $data));
    }

    /**
     * Creates new Smarty object instance if it doesn't already exist, and returns it.
     *
     * @throws RuntimeException If Smarty lib directory does not exist
     * @return Smarty Instance
     */
    public function getInstance() {
        if (!($this->parserInstance instanceof Smarty)) {
            if (!class_exists('Smarty')) {
                if (!is_dir($this->parserDirectory)) {
                    throw new RuntimeException('Cannot set the Smarty lib directory : ' . $this->parserDirectory . '. Directory does not exist.');
                }
                require_once $this->parserDirectory . '/Smarty.class.php';
            }
            $this->parserInstance = new Smarty();
            $this->parserInstance->addTemplateDir('./public');
            $this->parserInstance->addTemplateDir($this->getTemplatesDirectory());
            if ($this->parserExtensions) {
                $this->parserInstance->addPluginsDir($this->parserExtensions);
            }
            if ($this->parserCompileDirectory) {
                $this->parserInstance->setCompileDir($this->parserCompileDirectory);
            }
            if ($this->parserCacheDirectory) {
                $this->parserInstance->setCacheDir($this->parserCacheDirectory);
            }
            if ($this->leftDelimiter) {
                $this->parserInstance->setLeftDelimiter($this->leftDelimiter);
            }
            if ($this->rightDelimiter) {
                $this->parserInstance->setRightDelimiter($this->rightDelimiter);
            }
        }
        return $this->parserInstance;
    }

}
