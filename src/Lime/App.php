<?php
namespace Lime;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class App
 *
 * @package Lime
 */
class App
{
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
        $loader = new YamlFileLoader($this->container, new FileLocator(MPP_CONFIG_DIR));
        $loader->load('config.yml');
        $loader->load('services.yml');
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
     * Magic property handler allowing to directly access to services
     *
     * @param $param
     * @return object
     */
    public function __get($param)
    {
        return $this->get($param);
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