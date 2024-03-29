<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Reflection;

use Lime\Logger\TLogger;
use Lime\Core;

/**
 *
 *
 */
class ReflectionNamespace
{

    protected $name;

    /**
     * Creates a new ReflectionNamespace
     *
     * @param string $namespace namespace name
     */
    public function __construct($namespace)
    {
        $this->name = $namespace;
    }

    /**
     * Get the namespace name
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    /**
     * Get the namespace short name, ie the last part of the namespace
     * @return string
     */
    public function getShortName() {
        if($this->getName() == 'global') {
            return 'global';
        }
        return substr(strrchr($this->getName(), "\\"), 1);
    }

    /**
     * Gte the namespace name as a path by replacing backslashes with forwardslahes
     * @return mixed
     */
    public function asPath() {
        return str_replace('\\', '/', $this->getName());
    }

    /**
     * Gte the namespace name as a path by replacing backslashes with forwardslahes
     * @return mixed
     */
    public function getDottedName() {
        return str_replace('\\', '.', $this->getName());
    }

    /**
     * Get associated documentation filename
     *
     * @param string $extension Filename extension
     * @return string Return associated documentation filename
     */
    public function getDocFileName($base_href = '', $extension = 'html')
    {
        if($this->getName() === 'global') {
            return $base_href . '/ns.' . $this->getShortName() . '.' .$extension;
        }else{
            $dir = dirname($this->asPath());
            return $base_href . $dir . '/ns.' . $this->getShortName() . '.' .$extension;
        }

    }

}