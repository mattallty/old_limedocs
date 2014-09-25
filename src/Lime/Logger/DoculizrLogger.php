<?php
//------------------------------------------------------------------------------
//
//  This file is part of Doculizr -- The PHP 5 Documentation Generator
//
//  Copyright (C) 2012 Matthias ETIENNE <matt@allty.com>
//
//  Permission is hereby granted, free of charge, to any person obtaining a
//  copy of this software and associated documentation files (the "Software"),
//  to deal in the Software without restriction, including without limitation
//  the rights to use, copy, modify, merge, publish, distribute, sublicense,
//  and/or sell copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following conditions:
//
//  The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
//  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
//  IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
//  DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
//  OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
//  THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
//------------------------------------------------------------------------------
namespace Doculizr\Logger;

use Doculizr\Core;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Doculizr Logger
 * 
 * This class is used to log informations into files.
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
class DoculizrLogger extends AbstractLogger {
    
    /**
     * @var array Array of {Symfony\Component\Console\Output\StreamOutput} objects
     */
    protected $outputs;

    /**
     * Creates a new {DoculizrLogger} object
     * 
     * @param array $options Runtime options
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        $this->outputs = array(
            'out' => new StreamOutput(\STDOUT, StreamOutput::VERBOSITY_NORMAL, true),
            'error' => new StreamOutput(\STDERR, StreamOutput::VERBOSITY_NORMAL, true)
        );

        $this->outputs['out']->setDecorated(true);
        $this->outputs['error']->setDecorated(true);
    }

    /**
     * {@inheritdoc}
     */
    public function logMessage($message, $logType = self::LOG_INFO,
            $code = null, $arrBacktrace = null)
    {
        $date = date('c');
        $logTypeStr = $this->logDescriptions[$logType];

        $logMessage = $date . ' [' . $logTypeStr . ']' .
                ($code ? "($code) " : ' ') .
                $message;

        if (isset($this->options['debug-bt'])) {

            $backtrace = next($arrBacktrace);
            $file = '[..]' . Core::DS . implode(Core::DS,
                            array_slice(explode(Core::DS, $backtrace['file']),
                                    -3));
            $logMessage .= " (from " . $backtrace['function'] . '()' .
                    ' in file ' . $file . ':' . $backtrace['line'] . ')';
        }

        $logMessage = preg_replace('/[\r\n]+/', " ", $logMessage);

        if ($logType === self::LOG_NOTICE) {
            $logMessage = '<info>' . $logMessage . '</info>';
        } elseif ($logType === self::LOG_WARN || $logType === self::LOG_ERROR) {
            $logMessage = '<error>' . $logMessage . '</error>';
        }

        $this->outputs[($logType < 6 ? 'error' : 'out')]->writeln($logMessage);
    }

}