<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Parser\Tag;

use \Doculizr\Core;

/**
 * The <code>package</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagPackage extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '@package tag used to organise documentation.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        Core::getLogger()->debug('Parsing @package tag');
        return trim($tagValue);
    }

}