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

use Doctrine\Common\Cache\Cache as DtCache;

class Cache {

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $provider;

    /**
     * @param DtCache $provider
     */
    public function __construct(DtCache $provider) {
        $this->provider = $provider;
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