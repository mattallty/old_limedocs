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

/**
 * The <code>subpackage</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagSubpackage extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '@subpackage tag used to organise documentation.';
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
        $this->debug('Parsing @subpackage tag');
        return trim($tagValue);
    }

}