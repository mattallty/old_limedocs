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

use Lime\Common\Utils\StrUtils;

/**
 * Return Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagReturn extends AbstractTag
{

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '"Return" tag use to document functions/methods returned values.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Return';
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        $type = '(?<type>[a-z0-9\\\|\[\]]+)';
        $desc = '(?<description>.*)?';
        $spaces = '[\\s]+';
        $regs = null;
        $fomatFound = false;

        $formats = array(
            // full valid format
            '/^' . $type . $spaces . $desc . '$/i',
            // just the type format
            '/^' . $type . '/i',
            '/^(?<type>\$this|self|this)$/',
        );

        $this->debug('Parsing @return tag : "' . $tagValue . '"');

        // try to match with available formats
        foreach ($formats as $format) {
            if (preg_match($format, $tagValue, $regs)) {
                $fomatFound = true;
                break;
            }
        }

        // no format matched
        if (!$fomatFound) {
            $this->warning('Cannot parse @return tag : ' . $tagValue);
            return false;
        }

        // flat array
        $data = $this->filterNumericIndexes($regs);

        // redraw type
        $data['type'] = $this->scopeElement($data['type']);

        if (isset($data['description'])) {
            $data['description'] = preg_replace_callback('/\{([a-z0-9_\\\]+)\}/i', function($regs) {

                $type = $this->scopeElement($regs[1]);
                $url = StrUtils::objectTypeToFilepath($type);

                return '<a href="'.$url.'">'.$regs[1].'</a>';

            }, $data['description']);
        }

        $this->debug('@return tag parsed : ' . json_encode($data));

        return $data;
    }

}