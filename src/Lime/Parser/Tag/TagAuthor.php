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
 * The <code>author</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagAuthor extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '"Author" tag used to document authoring.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Author';
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        $name = '(?<name>[^<]+)';
        $email = '<(?<email>([[:alnum:]]([-_.]?[[:alnum:]])+_?@[[:alnum:]]' .
                '([-.]?[[:alnum:]])+\.[a-z]{2,6}))>';
        $spaces = '[\\s]+';
        $regs = null;
        $fomatFound = false;

        $formats = array(
            // full valid format
            '/^' . $name . $spaces . $email . '$/i',
            // just the name
            '/^' . $name . '/i'
        );

        Core::getLogger()->debug('Parsing @author tag : "' . $tagValue . '"');

        // try to match with available formats
        foreach ($formats as $format) {
            if (preg_match($format, $tagValue, $regs)) {
                $fomatFound = true;
                break;
            }
        }

        // no format matched
        if (!$fomatFound) {
            Core::getLogger()->warn('Cannot parse @author tag : ' . $tagValue);
            return false;
        }

        // flat array
        $data = array_map('trim', $this->filterNumericIndexes($regs));

        Core::getLogger()->debug('@author tag parsed : ' . json_encode($data));

        return $data;
    }

}