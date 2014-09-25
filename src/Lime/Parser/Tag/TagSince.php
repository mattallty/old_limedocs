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
 * The <code>since</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagSince extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'The @since tag may be used to document the release version of any element that can be documented.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Since';
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        Core::getLogger()->debug('Parsing @since tag');
        return trim($tagValue);
    }

}