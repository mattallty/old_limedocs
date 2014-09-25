<?php

namespace Doculizr\Logger;

/**
 * Doculizr Abstract Logger
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
abstract class AbstractLogger implements ILogger {
    
    /**
     * @var array Logs labels base on their code
     */
    protected $logDescriptions = array(
        self::LOG_DEBUG => 'Debug',
        self::LOG_ERROR => 'Error',
        self::LOG_INFO => 'Info',
        self::LOG_NOTICE => 'Notice',
        self::LOG_WARN => 'Warning'
    );
    
    /**
     * @var array Runtime options
     */
    protected $options;

    abstract public function __construct(array $options);

    public final function error($message, $code = null)
    {
        $this->logMessage($message, self::LOG_ERROR, $code,
                debug_backtrace(
                        defined('DEBUG_BACKTRACE_PROVIDE_OBJECT') ?
                                DEBUG_BACKTRACE_PROVIDE_OBJECT : true));
    }

    public final function warn($message, $code = null)
    {
        $this->logMessage($message, self::LOG_WARN, $code,
                debug_backtrace(
                        defined('DEBUG_BACKTRACE_PROVIDE_OBJECT') ?
                                DEBUG_BACKTRACE_PROVIDE_OBJECT : true));
    }

    public final function notice($message, $code = null)
    {
        $this->logMessage($message, self::LOG_NOTICE, $code,
                debug_backtrace(
                        defined('DEBUG_BACKTRACE_PROVIDE_OBJECT') ?
                                DEBUG_BACKTRACE_PROVIDE_OBJECT : true));
    }

    public final function info($message, $code = null)
    {
        $this->logMessage($message, self::LOG_INFO, $code,
                debug_backtrace(
                        defined('DEBUG_BACKTRACE_PROVIDE_OBJECT') ?
                                DEBUG_BACKTRACE_PROVIDE_OBJECT : true));
    }

    public final function debug($message, $code = null)
    {
        $this->logMessage($message, self::LOG_DEBUG, $code,
                debug_backtrace(
                        defined('DEBUG_BACKTRACE_PROVIDE_OBJECT') ?
                                DEBUG_BACKTRACE_PROVIDE_OBJECT : true));
    }

    /**
     * Logs a message
     * 
     * @param string $message The message to log
     * @param string $logType Type of message, see {ILogger}
     * @param integer $code Message or error code
     * @param array $arrBacktrace Backtrace array
     */
    abstract public function logMessage($message, $logType = self::LOG_INFO,
            $code = null, $arrBacktrace = null);
}

?>
