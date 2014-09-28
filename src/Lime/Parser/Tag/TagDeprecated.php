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
class TagDeprecated extends AbstractTag
{

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '@deprecated tag used to mark elements as deprecated.';
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
        $this->debug('Parsing @deprecated tag');
        return true;
    }

}