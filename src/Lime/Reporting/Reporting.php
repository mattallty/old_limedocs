<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Reporting;


/**
 * Reporting class
 */
class Reporting {

    protected $records = array();

    public function record($message, $file = null, $line = null)
    {
        $this->records[] = compact('message', 'file', 'line');
    }
    
}