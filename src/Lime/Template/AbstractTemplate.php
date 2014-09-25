<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Doculizr\Template;

use Doculizr\Options\IOptionsAware;
use Doculizr\Options\TOptions;
use Doculizr\Parser\IParserAware;
use Doculizr\Parser\TParser;

/**
 * Templating asbtract class
 *
 */
abstract class AbstractTemplate implements ITemplate, IParserAware, IOptionsAware {
    
    use TOptions, TParser;
    
    /**
     * @var array Template informations
     */
    protected $infos;
    /**
     * @var string Template manifest file path
     */
    protected $manifestFile;
    /**
     * @var string Template name 
     */
    protected $name; 
    /**
     * @var string Template version
     */
    protected $version;
    /**
     * @var string Template path
     */
    protected $path;
    
    public function __construct(array $tpl_infos)
    {
        $this->infos = $tpl_infos;
    }
    
    public function getVersion() {
        return $this->infos['version'];
    }
    
    public function getName() {
        return $this->infos['name'];
    }
    
    public function getInfos($info = null)
    {
        return is_null($info) ? $this->infos : 
            (isset($this->infos[$info]) ? 
                $this->infos[$info] : null);
    }
    
    public function getPath()
    {
        return DOCULIZR_DATA_DIR . DS . 'templates' . DS .
                $this->getName() . DS . $this->getVersion();
    }
    
    protected function getManifestFile()
    {
        return $this->getPath() . DS . 'template.json';
    }
    

    

}
