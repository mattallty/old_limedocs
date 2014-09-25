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

/**
 * The <code>alias</code> Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagAlias extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '"Alias" tag use to handle methods aliases.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Aliases of';
    }

    private function parseScopedElement($elParts)
    {
        if (($func = Utils::isFunc($elParts[1]))) {
            Core::getLogger()->debug('tag parsed as a method : ' . $elParts[1]);
            return array('type' => 'method', 'class' => $elParts[0],
                'value' => $func);
        }
        return false;
    }

    /**
     * Detects differents formats
     *
     * @param string $tagVal tag value
     * @return boolean
     */
    private function parseMultiFormat($tagVal)
    {

        // scope
        if (($scopedElemParts = Utils::getScopedElementParts($tagVal))) {
            if (($data = $this->parseScopedElement($scopedElemParts))) {
                return $data;
            }
            Core::getLogger()->warn('Cannot parse @alias tag "' . $tagVal .'" in ' . $this->getRefObject());
            return false;
        }

        if (!($scope = $this->getRefObject()->getInherits())) {
            $scope = $this->getRefObject()->class;
        } else {
            $scope = $scope->name;
        }

        /**
         * Matches myMethod or myMethod()
         */
        $cleanMethod = Utils::cleanMethodName($tagVal);
        if (method_exists($scope, $cleanMethod)) {
            return array('type' => 'method', 'class' => $scope,
                'name' => $cleanMethod);
        }



        // External class
        if (Utils::isClass($tagVal)) {
            return array('type' => 'class', 'name' => $tagVal);
        }


        Core::getLogger()->warn('Cannot parse @alias tag : ' . $tagVal);
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        $data = array();

        if (($parsed = $this->parseMultiFormat($tagValue))) {
            $data = $parsed;
            Core::getLogger()->debug("@alias tag ($tagValue) parsed : " .
                  json_encode($parsed));
        }

        return $data;
    }

}
