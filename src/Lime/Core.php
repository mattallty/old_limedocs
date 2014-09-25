<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime;

/**
 * Core class
 *
 */
class Core {

    const VERSION = '1.0';
    const DS = \DIRECTORY_SEPARATOR;

    /**
     * @var Doculizr\Doculizr Doculizr instance
     */
    static private $instance;

    /**
     * @var Doculizr\Logger\ILogger ILogger instance
     */
    static private $logger;

    /**
     * @var Doculizr\Cache\ICache ICache instance
     */
    static private $cache;
    
    /**
     * @var array PHP internal functions documentation
     */
    static $quickref;

    /**
     * @var array Running options
     */
    private $options = array();

    /**
     * Constructor — Private since used in a singleton
     *
     * Instantiate logger and cache.
     */
    private function __construct()
    {
        self::$logger = new Logger\DoculizrLogger(array());
        self::$cache = new Cache\DoculizrCache();
        
        $handle = fopen(DOCULIZR_CONFIG_DIR . DS . 'quickref.txt', 'r');
        while($line = fgets($handle)) {
            list($command, $description) = explode("-", $line, 2);
            self::$quickref[trim($command)] = trim($description);
        }
    }

    /**
     * @ignore
     */
    public final function __clone()
    {
        throw new \BadMethodCallException('Cloning ' . __CLASS__ . ' is not allowed');
    }

    /**
     * Return instance (singleton pattern)
     * @return Doculizr\Core
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
        return self::$instance;
    }
    
    /**
     * Gets a quick refrence, ie description, of a *native* PHP function/method.
     * @param string $function Function or method  name
     * @param string $className Class name in the case of a method.
     * @return string Returns the description {string} or {null} if the function cannot be found.
     */
    public static function getQuickRef($function, $className = '') {
        $lookup = (empty($className)) ? $function : $className.'::'.$function;
        return isset(self::$quickref[$lookup]) ? self::$quickref[$lookup] : null;
    }

    /**
     * Return the logger instance
     * @return Doculizr\Logger\ILogger
     */
    public static function getLogger()
    {
        return self::$logger;
    }

    /**
     * Return the cache instance
     * @return Doculizr\Cache\ICache
     */
    public static function getCache()
    {
        return self::$cache;
    }

    /**
     * Get base options
     * @return stdClass
     */
    public static function getBaseOptions()
    {
        return json_decode(file_get_contents(DOCULIZR_CONFIG_DIR .
                                self::DS . 'doculizr.json'));
    }

    /**
     * Get option value
     * @param string $name option name
     * @return mixed option value
     */
    public function getOption($name = null)
    {
        return (is_null($name) ?
                        $this->options :
                        (isset($this->options[$name]) ?
                                $this->options[$name] : null));
    }

    /**
     * Set options
     *
     * @param array $options Options keys & values
     * @param boolean $mergeWithBaseOpts merge with base options
     * @return $this
     */
    public function setOptions(array $options, $mergeWithBaseOpts = false)
    {
        if ($mergeWithBaseOpts) {
            $this->options = (array) self::getBaseOptions();
        }

        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Set an option
     * @param string $name option name
     * @param mixed $value option value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function run()
    {
        $cli = new Cli\DoculizrCli();
        $cli->run();
    }

}