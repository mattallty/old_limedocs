<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Common;

use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

class Cache {

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $provider;

    /**
     * @param DoctrineCacheInterface $provider
     */
    public function __construct(DoctrineCacheInterface $provider = null) {

        // take the provided provider :D
        if (false === is_null($provider)) {
            $this->provider = $provider;

        // Or take APC if it is enable in cli-mode
        } elseif(function_exists('apc_store') && true === (bool) ini_get('apc.enable_cli')) {
            $this->provider = new ApcCache();

        // Or use a simple array
        } else {
            $this->provider = new ArrayCache();
        }
    }

    /**
     * Proxy methods calls to provider
     *
     * @param string $method Method called
     * @param array $args Call arguments
     * @return mixed
     */
    public function __call($method, $args) {
        return call_user_func_array($method, $args);
    }
}