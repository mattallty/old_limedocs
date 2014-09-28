<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\App;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Lime\Filesystem\Finder;

/**
 * Class App
 *
 * @package Lime
 */
class App
{

    const VERSION = '0.1';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;


    /**
     * Create a new App
     */
    private function __construct()
    {
        $this->container = new ContainerBuilder();
        $loader = new YamlFileLoader($this->container, new FileLocator(LIMEDOCS_ROOT_DIR . DS . 'config'));
        $loader->load('config.yml');
        $loader->load('services.yml');
    }

    public function getContainer() {
        return $this->container;
    }

    /**
     * Return the finder service instance. (shortcut for `$this->get('finder')`)
     *
     * @return Finder
     */
    public function getFinder() {
        return $this->get('finder');
    }


    public function __call($method, $args) {
        return call_user_func_array(array($this->container, $method), $args);
    }

    /**
     * Returns the singleton instance
     *
     * @return App
     */
    public static function getInstance()
    {
        static $instances = array();

        $class = get_called_class();

        if (isset($instances[$class]) === false) {
            $instances[$class] = new $class();
        }

        return $instances[$class];
    }

    // Prevent users to clone the instance
    private function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}