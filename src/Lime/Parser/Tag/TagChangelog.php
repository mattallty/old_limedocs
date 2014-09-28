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
 * The `Changelog` Tag
 */
class TagChangelog extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '"Changelog" tag is used to document changes on classes and methods.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Changes';
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        $version = '(?<version>[0-9a-z\.\-_]+)';
        $changes = '(?<description>.*)';
        $spaces = '[\\s]+';
        $regs = null;
        $fomatFound = false;

        $formats = array(
            // full valid format
            '/^' . $version . $spaces . $changes . '$/i',
        );

        $this->debug('Parsing @changelog tag : "' . $tagValue . '"');

        // try to match with available formats
        foreach ($formats as $format) {
            if (preg_match($format, $tagValue, $regs)) {
                $fomatFound = true;
                break;
            }
        }

        // no format matched
        if (!$fomatFound) {
            $this->warning('Cannot parse @changelog tag : ' . $tagValue);
            return false;
        }

        // flat array
        $data = array_map('trim', $this->filterNumericIndexes($regs));

        $this->debug('@changelog tag parsed : ' . json_encode($data));

        return $data;
    }

}