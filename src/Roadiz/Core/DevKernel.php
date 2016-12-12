<?php
/**
 * Copyright © 2016, Ambroise Maupate and Julien Blanchet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * Except as contained in this notice, the name of the ROADIZ shall not
 * be used in advertising or otherwise to promote the sale, use or other dealings
 * in this Software without prior written authorization from Ambroise Maupate and Julien Blanchet.
 *
 * @file DevKernel.php
 * @author Ambroise Maupate
 */
namespace RZ\Roadiz\Core;

/**
 * DevKernel is meant for Vagrant and Docker development env
 * where using file sharing on Roadiz folder.
 *
 * @see http://www.whitewashing.de/2013/08/19/speedup_symfony2_on_vagrant_boxes.html
 * @package RZ\Roadiz\Core
 */
class DevKernel extends Kernel
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @param string $environment
     * @param boolean $debug
     * @param bool $preview
     * @param string $appName
     */
    public function __construct($environment, $debug, $preview = false, $appName = "roadiz_test")
    {
        parent::__construct($environment, $debug, $preview);

        $this->appName = $appName;
    }

    /**
     * @param string $environment
     * @param bool $debug
     * @param bool $preview
     * @param string $appName
     * @return null
     */
    public static function getInstance($environment = 'prod', $debug = false, $preview = false, $appName = "roadiz_test")
    {
        if (static::$instance === null) {
            static::$instance = new DevKernel($environment, $debug, $preview, $appName);
        }
        return static::$instance;
    }

    /**
     * It’s important to set cache dir outside of any shared folder. RAM disk is a good idea.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return '/dev/shm/' . $this->appName . '/cache/' .  $this->environment;
    }

    /**
     * It’s important to set logs dir outside of any shared folder. RAM disk is a good idea.
     *
     * @return string
     */
    public function getLogDir()
    {
        return '/dev/shm/' . $this->appName . '/logs';
    }
}
