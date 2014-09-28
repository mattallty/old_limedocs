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
 * The `deprecated` Tag.
 *
 * This tag blabla...
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagChanges extends AbstractTag
{

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '@changes tag used to document changes between versions.';
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
        $this->debug('Parsing @changes tag');
        $elems = explode(' ', $tagValue, 2);
        return (count($elems) === 2) ? $elems : false;
    }

}