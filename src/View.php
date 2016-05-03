<?php

/**
 * Upfor Framework
 *
 * @author      Shocker Li <shocker@upfor.club>
 * @copyright   Upfor Group
 * @link        http://framework.upfor.club
 * @license     MIT
 */

namespace Upfor;

use RuntimeException;
use Upfor\Helper\Collection;

/**
 * View
 */
class View {

    /**
     * Data available to the view templates
     * 
     * @var Collection
     */
    protected $data;

    /**
     * Path to templates base directory (without trailing slash)
     * 
     * @var string
     */
    protected $templatesDirectory;

    public function __construct() {
        $this->data = new Collection();
    }

    /**
     * Return view data value with key
     * 
     * @param  string $key
     * @return mixed
     */
    public function get($key) {
        return $this->data->get($key);
    }

    /**
     * Set view data value with key
     * 
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $this->data->set($key, $value);
    }

    /**
     * Return view data
     * 
     * @return array
     */
    public function all() {
        return $this->data->all();
    }

    /**
     * Replace view data
     * 
     * @param  array  $data
     */
    public function replace(array $data) {
        $this->data->replaceItems($data);
    }

    /**
     * Clear view data
     */
    public function clear() {
        $this->data->clear();
    }

    /**
     * Set the base directory that contains view templates
     * 
     * @param   string $directory
     */
    public function setTemplatesDirectory($directory) {
        $this->templatesDirectory = rtrim($directory, DIRECTORY_SEPARATOR);
    }

    /**
     * Get templates base directory
     * 
     * @return string
     */
    public function getTemplatesDirectory() {
        return $this->templatesDirectory;
    }

    /**
     * Get fully qualified path to template file using templates base directory
     * 
     * @param  string $file The template file pathname relative to templates base directory
     * @return string
     */
    public function getTemplatePathname($file) {
        return $this->templatesDirectory . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
    }

    /**
     * Display template
     *
     * @param  string   $template   Pathname of template file relative to templates directory
     * @param  array    $data       Any additonal data to be passed to the template.
     */
    public function display($template, array $data = array()) {
        echo $this->fetch($template, $data);
    }

    /**
     * Return the contents of a rendered template file
     *
     * @param    string $template   The template pathname, relative to the template base directory
     * @param    array  $data       Any additonal data to be passed to the template.
     * @return string               The rendered template
     */
    public function fetch($template, array $data = array()) {
        return $this->render($template, $data);
    }

    /**
     * Render a template file
     *
     * @param  string $template     The template pathname, relative to the template base directory
     * @param  array  $data         Any additonal data to be passed to the template.
     * @return string               The rendered template
     * @throws RuntimeException    If resolved template pathname is not a valid file
     */
    protected function render($template, array $data = array()) {
        $templatePathname = $this->getTemplatePathname($template);
        if (!is_file($templatePathname)) {
            throw new RuntimeException("View cannot render `$template` because the template does not exist");
        }

        $data = array_merge($this->all(), (array) $data);
        extract($data);
        ob_start();
        require $templatePathname;

        return ob_get_clean();
    }

}
