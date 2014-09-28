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
 * The `api` Tag.
 *
 * This tag blabla...
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagApi extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '@api tag used to document API documentation.';
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
        $this->debug('Parsing @api tag');
        return true;
    }

}