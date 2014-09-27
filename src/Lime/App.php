<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
   static private $logger;


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

    public function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    public static function getLogger()
    {
        return self::$logger;
    }

    public function getParameter($param)
    {
        return $this->container->getParameter($param);
    }

    public function getParameterBag()
    {
        return $this->container->getParameterBag();
    }

    /**
     * Returns a service
     *
     * @param $srv
     * @return object
     */
    public function get($srv)
    {
        return $this->container->get($srv);
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