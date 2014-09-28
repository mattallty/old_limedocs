<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Template;

/**
 * Templating asbtract class
 *
 */
abstract class Template implements TemplateInterface
{

    /**
     * @var array Template informations
     */
    protected $infos;
    protected $files;


    public function __construct(array $tpl_infos)
    {
        foreach ($tpl_infos as $ns => $props) {
            $this->{$ns} = $props;
        }
    }

    public function getVersion()
    {
        return $this->getInfos('version');
    }

    public function getName()
    {
        return $this->getInfos('name');
    }

    public function getAuthor()
    {
        return $this->getInfos('author');
    }

    public function getPath()
    {
        return $this->files['template_dir'];
    }

    protected function getManifestPath()
    {
        return $this->getPath() . DS . 'manifest.yml';
    }

    public function getInfos($info = null)
    {
        return is_null($info) ? $this->infos :
            (isset($this->infos[$info]) ?
                $this->infos[$info] : null);
    }






}
