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
 * Interface for all templating classes
 */
interface ITemplate {
    
    
    public function __construct(array $tpl_infos);
    
    public function output();
    
    public function getInfos($info = null);
    public function getName();
    public function getVersion();
    public function getPath();
    
    public function getIndexFile();
    public function getNsFile();
    public function getClassFile();
    public function getInterfaceFile();
    public function getTraitFile();
    public function getMethodFile();
    
}
