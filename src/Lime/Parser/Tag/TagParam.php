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
 * The <code>param</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagParam extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Parameter tag used to document functions/methods parameters.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Parameter';
    }

    /**
     * Detect multi formats accepted by @param tag
     *
     * Tag '@param' accepts the following value formats:
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
        $name = '(?<name>[a-z0-9_$]+)';
        $desc = '(?<description>.*)';
        $spaces = '[\\s]+';
        $regs = null;
        $value = trim($value);

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
                    Core::getLogger()->notice('Malformed @param tag : "' . $value
                            . '" for function/method ' .
                            $this->getRefObject()->getName() . '() in file ' .
                            $this->getFileInfo()->getFilename() . ':' .
                            $this->getRefObject()->getStartLine());
                }

                $regs['type'] = DoculizrUtils::stripStartBackslash($regs['type']);

                return $this->filterNumericIndexes($regs);
            }
        }

        Core::getLogger()->warn('Cannot parse @param tag : ' . $value);
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
                    sprintf('@param tag is not allowed in %s:%s',
                            $this->getFileInfo()->getFilename(),
                            $refObj->getStartLine())
            );

            return false;
        }

        Core::getLogger()->debug('Parsing @param tag : "' . $tagValue . '"');

        if (!($data = $this->parseMultiFormat($tagValue))) {
            return false;
        }

        // redraw type
        $data['type'] = $this->scopeElement($data['type']);


        Core::getLogger()->debug('@param tag parsed : ' . json_encode($data));

        return $data;
    }

}