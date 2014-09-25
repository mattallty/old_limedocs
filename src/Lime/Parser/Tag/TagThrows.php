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
 * The <code>throws</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagThrows extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Throws tag used to document possible exceptions thrown within methods.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Throws';
    }

    /**
     * Detect multi formats accepted by @throws tag
     *
     * Tag '@throws' accepts the following value formats:
     *  - <type> <desc>
     *  - <type>
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
        $desc = '(?<description>.*)';
        $spaces = '[\\s]+';
        $regs = null;
        $value = trim($value);

        $formats = array(
            // type + desc
            '/^' . $type . $spaces . $desc . '/i',
            // type only
            '/^' . $type . '$/i'
        );

        foreach ($formats as $format) {
            if (preg_match($format, $value, $regs)) {
                $regs['type'] = DoculizrUtils::stripStartBackslash($regs['type']);
                return $this->filterNumericIndexes($regs);
            }
        }

        Core::getLogger()->warn('Cannot parse @throws tag : ' . $value);
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
                    sprintf('@throws tag is not allowed in %s:%s',
                            $this->getFileInfo()->getFilename(),
                            $refObj->getStartLine())
            );

            return false;
        }


        Core::getLogger()->debug('Parsing @throws tag : "' . $tagValue . '"');

        if (!($data = $this->parseMultiFormat($tagValue))) {
            return false;
        }

        // redraw type
        $data['type'] = $this->scopeElement($data['type']);

        Core::getLogger()->debug('@throws tag parsed : ' . json_encode($data));

        return $data;
    }

}