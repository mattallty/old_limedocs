<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Logger;

use \Psr\Log\LoggerInterface;
/**
 * Logger
 *
 */
class LoggerProxy {

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if(!is_null($logger))
        {
            $this->setLogger($logger);
        }
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }


    public function __call($method, $args)
    {
        return call_user_func_array(array($this->logger, $method), $args);
    }

}