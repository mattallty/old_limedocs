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
 * Main templating class
 */
class DefaultTemplate extends Template
{

    public function output()
    {

    }

    public function getIndexFile()
    {
        return 'index.twig.html';
    }
    public function getNsFile()
    {
        return 'ns.twig.html';
    }
    public function getClassFile()
    {
        return 'class.twig.html';
    }
    public function getInterfaceFile()
    {
        return 'interface.twig.html';
    }
    public function getTraitFile()
    {
        return 'trait.twig.html';
    }
    public function getMethodFile()
    {
        return 'method.twig.html';
    }

}
