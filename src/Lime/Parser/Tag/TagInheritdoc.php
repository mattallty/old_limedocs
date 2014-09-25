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
 * The <code>inheritdoc</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagInheritdoc extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '@inheritdoc tag used to inherits documentation from parent.';
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
        Core::getLogger()->debug('Parsing @inheritdoc tag');
        return true;
    }

}