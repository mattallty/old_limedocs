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

use Doculizr\Core;
use Doculizr\Utils\DoculizrUtils;
use Doculizr\Reflection\DoculizrReflectionFunction;
use Doculizr\Reflection\DoculizrReflectionMethod;

/**
 * The `property` Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagProperty extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Property tag used to document magic properties.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Property';
    }

    /**
     * Detect multi formats accepted by teh 'property' tag
     *
     * Tag 'property' accepts the following value formats:
     *  - <type> <name> <desc>
     *  - <type> <name>
     *  - <type> <desc>
     *
     * If none of this formats are detected, this method will return false.
     *
     * @return array|boolean If one format is detected, this method will return
     * an associative array, otherwise, {false} will be returned.
     *
     */
    private function parseMultiFormat($value)
    {
        $type = '(?<type>[a-z0-9\\\|]+)';
        $name = '(?<name>[a-z0-9$]+)';
        $desc = '(?<description>.*)';
        $spaces = '[\\s]+';
        $regs = null;

        $formats = array(
            // full (type + name + desc)
            '/^' . $type . $spaces . $name . $spaces . $desc . '$/i',
            // type + name
            '/^' . $type . $spaces . $name . '$/i',
            // type desc
            '/^' . $type . $spaces . $desc . '$/i'
        );

        foreach ($formats as $key => $format) {

            if (preg_match($format, $value, $regs)) {

                if ($key) {
                    Core::getLogger()->warn('Malformed @property tag : "' . $value
                            . '" for function/method ' .
                            $this->getRefObject()->getName() . '() in file ' .
                            $this->getFileInfo()->getFilename() . ':' .
                            $this->getRefObject()->getStartLine());
                }

                $regs['type'] = DoculizrUtils::stripStartBackslash($regs['type']);

                return $this->filterNumericIndexes($regs);
            }
        }

        Core::getLogger()->warn('Cannot parse @property tag : ' . $value);
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        // @param tag is retsricted to methods and functions
        $refObj = $this->getRefObject();

        if ($refObj instanceof DoculizrReflectionFunction === false &&
                $refObj instanceof DoculizrReflectionMethod === false) {

            Core::getLogger()->error(
                    sprintf('@property tag is not allowed in %s:%s',
                            $this->getFileInfo()->getFilename(),
                            $refObj->getStartLine())
            );

            return false;
        }


        Core::getLogger()->debug('Parsing @property tag : "' . $tagValue . '"');

        if (!($data = $this->parseMultiFormat($tagValue))) {
            return false;
        }

        // redraw type
        $data['type'] = $this->scopeElement($data['type']);


        Core::getLogger()->debug('@property tag parsed : ' . json_encode($data));

        return $data;
    }

}