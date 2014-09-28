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
use Psr\Log\LoggerInterface;

/**
 * Logger Aware interface
 *
 * Make classes able to log informations by accessing the
 * {\Psr\Log\LoggerInterface}
 */
interface LoggerAwareInterface extends LoggerInterface {

    /**
     * Return the {\Psr\Log\LoggerInterface} instance
     */
    public function getLogger();

}
