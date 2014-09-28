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

use Lime\Common\Utils\NsUtils;

/**
 * The `var` Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagVar extends AbstractTag
{

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Var tag used to document magic properties.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Var';
    }

    /**
     * Detect multi formats accepted by teh 'var' tag
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
        $type = '(?<type>[a-z0-9_\\\|]+)';
        $desc = '(?<description>.*)';
        $spaces = '[\\s]+';
        $regs = null;

        $formats = array(
            // full (type + desc)
            '/^' . $type . $spaces . $desc . '$/i',
            // type desc
            '/^' . $type . '$/i'
        );

        foreach ($formats as $key => $format) {
            if (preg_match($format, $value, $regs)) {
                $regs['type'] = NsUtils::stripLeadingBackslash($regs['type']);
                return $this->filterNumericIndexes($regs);
            }
        }

        $this->warning('Cannot parse @var tag : ' . $value);
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        // @var tag is retsricted to methods and functions
        $refObj = $this->getRefObject();

        /*
        if ($refObj instanceof DoculizrReflectionFunction === false &&
                $refObj instanceof DoculizrReflectionMethod === false) {

            Core::getLogger()->error(
                    sprintf('@var tag is not allowed in %s:%s',
                            $this->getFileInfo()->getFilename(),
                            $refObj->getStartLine())
            );

            return false;
        }*/


        $this->debug('Parsing @var tag : "' . $tagValue . '"');

        if (!($data = $this->parseMultiFormat($tagValue))) {
            return false;
        }

        // redraw type
        $data['type'] = $this->scopeElement($data['type']);


        $this->debug('@var tag parsed : ' . json_encode($data));

        return $data;
    }

}