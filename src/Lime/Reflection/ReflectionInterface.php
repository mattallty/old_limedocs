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
class ReflectionInterface
{

    protected $name;

    /**
     * Creates a new ReflectionInterface
     *
     * @param string $interface namespace name
     */
    public function __construct($interface)
    {
        $this->name = $interface;
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
        return substr(strrchr($this->getName(), "\\"), 1);
    }

    public function getNamespaceName() {
        $parts = explode('\\', $this->getName());
        array_pop($parts);
        return implode('\\', $parts);
    }


    public function isUserDefined() {
        return $this->inNamespace();
    }

    public function inNamespace() {
        $res = ($this->getNamespaceName() !== '');
        return $res;
    }

    /**
     * Get associated documentation filename
     *
     * @param string $base_href base href for documentation
     * @param string $extension Filename extension
     * @return string Return associated documentation filename
     */
    public function getDocFileName($base_href = '', $extension = 'html')
    {
        if (!$this->isUserDefined()) {
            return 'http://php.net/' . $this->name;
        }

        if($this->inNamespace() === false) {
            $nsPrefix = 'global';
        }else{
            $nsPrefix = str_replace('\\', '/', $this->getNamespaceName());
        }

        return $base_href . $nsPrefix . '/class.' . $this->getShortName() . '.' . $extension;
    }

}