<?php
namespace Lime\App;

/**
 * Runtime parameters Trait for classes implementing the {LoggerAware} interface
 */
trait TRuntimeParameter
{

    /**
     * Set runtime parameter
     *
     */
    final public function setParameter($name, $value)
    {
        App::getInstance()->setParameter($name, $value);
    }

    /**
     * Get runtime parameter
     *
     * @return mixed
     */
    final public function getParameter($name)
    {
        return App::getInstance()->getParameter($name);
    }

    /**
     * Get all runtime parameters
     *
     * @return mixed
     */
    final public function getParameters($namespace = null)
    {
        $params = App::getInstance()->getParameterBag()->all();

        if (is_null($namespace)) {
            return App::getInstance()->getParameterBag()->all();
        }

        $filtered = [];
        $nslen = strlen($namespace);

        foreach ($params as $key => $value) {
            if (substr($key, 0, $nslen) == $namespace) {
                $filtered[substr($key, $nslen + 1)] = $value;
            }
        }

        return $filtered;

    }

}