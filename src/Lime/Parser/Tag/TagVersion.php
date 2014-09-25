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
 * The <code>version</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagVersion extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '@version tag used to document code versions.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Version';
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        Core::getLogger()->debug('Parsing @version tag');
        return trim($tagValue);
    }

}