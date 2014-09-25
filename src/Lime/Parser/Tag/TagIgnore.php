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
 * The <code>ignore</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagIgnore extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Used to ignore certain parts of code.';
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedData()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        return true;
    }

}
