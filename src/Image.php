<?php
/**
 * @copyright   NVB | <Michał Iżewski> m.izewski@gmail.com
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Guenbakku\Cakepdf;

use Exception;

class Image {

    protected $vendor = 'app/vendor';

    // Snappy object
    protected $Snappy;

    // Path of wkhtmltopdf executable binary
    protected $binary;

    // Html that will be rendered to pdf
    protected $html;

    // Path of output pdf
    protected $output;

    protected $pageStyle = '
        <style type="text/css">
            .page-container {
                overflow: hidden;
                page-break-before: always;
                page-break-after: always;
            }
            .page-container:last-child {
                page-break-after: auto;
            }
        </style>';

    protected $pageContainer = '<div class="page-container">%s</div>';

    public function __construct() {
        $this->_setBinary();
        $this->Snappy = new \Knp\Snappy\Image($this->binary);
    }

    /**
     * Set output path for image file
     *
     * @param   string: output path
     * @return  object: this object
     */
    public function output($path) {
        $this->output = $path;
        return $this;
    }


    /**
     * Render image from added html
     *
     * @param   string: output path
     * @param   array: an array of options for this generation only
     * @param   bool: overwrite existing file or not.
     *                Only take effect if output is not null
     * @return  string|null: image content or null if output to file
     */
    public function render($output = null, $options = array(), $overwrite = false) {
        if ($output !== null) {
            $this->output($output);
        }
        if (empty($this->output)) {
            return $this->Snappy->getOutputFromHtml($this->html, $options);
        } else {
            return $this->Snappy->generateFromHtml($this->html, $this->output, $options, $overwrite);
        }
    }

    /**
     * Return Snappy object
     *
     * @param   void
     * @return  object: Snappy object
     */
    public function getSnappy() {
        return $this->Snappy;
    }

    /**
     * This magic is used for calling methods of Snappy object
     */
    public function __call($name, $arguments) {
        return $this->Snappy->$name(...$arguments);
    }

    /**
     * Find root of application
     *
     * @param   string: path of current script
     * @return  string: path of root
     */
    protected function _findRoot($root) {
        do {
            $lastRoot = $root;
            $root = dirname($root);
            if (is_dir($root . DIRECTORY_SEPARATOR . $this->vendor)) {
                return $root;
            }
        } while ($root !== $lastRoot);

        throw new Exception('Cannot find the root of the application');
    }

    /**
     * Set path of wkhtmltopdf.
     * This will set the correct wkhtmltopdf binary
     * corresponding to structure of current processor (32 or 64 bit)
     *
     * @param   void
     * @return  string: path of binary
     */
    protected function _setBinary() {
        $is32bit = PHP_INT_SIZE === 4;
        if ($is32bit) {
            $binary = 'wkhtmltoimage-i386';
        } else {
            $binary = 'wkhtmltoimage-amd64';
        }

        $root = $this->_findRoot(__FILE__);
        $binary = implode(DIRECTORY_SEPARATOR, array(
            $root, $this->vendor, 'bin', $binary
        ));

        if (is_file($binary) || is_link($binary)) {
            $this->binary = $binary;
        }
        return $this->binary;
    }
}
