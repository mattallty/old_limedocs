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

/**
 * Logger Aware interface
 *
 * Make classes able to log informations by accessing the
 * {\Psr\Log\LoggerInterface}
 */
interface RuntimeParameterAware {

    public function getParameter($name);
    public function setParameter($name, $value);
    public function getParameters($namespace = null);

}
