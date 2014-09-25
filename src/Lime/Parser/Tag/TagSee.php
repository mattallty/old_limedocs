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
 * The see Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
class TagSee extends AbstractTag {

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '"See" tag use to generate "See also" section in documentation.';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'See also';
    }

    private function parseScopedElement($elParts)
    {
        if (Utils::isVar($elParts[1])) {
            Core::getLogger()->debug('tag parsed as a property : ' . $elParts[1]);
            return array('type' => 'property', 'class' => $elParts[0],
                'value' => $elParts[1]);
        } elseif (($func = Utils::isFunc($elParts[1]))) {
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

        $tagVals = array_map('trim', explode(',', $tagVal));
        $ret = array();
        
        foreach($tagVals as $tagVal) {
            if(($data = $this->parseSimpleValue($tagVal))) {
                $ret[] = $data;
            }
        }
        
        return count($ret) ? $ret : false;
    }
    
    protected function parseSimpleValue($tagVal) {
        // scope
        if (($scopedElemParts = Utils::getScopedElementParts($tagVal))) {
            if (($data = $this->parseScopedElement($scopedElemParts))) {
                return $data;
            }
            Core::getLogger()->warn('Cannot parse @see tag "' . $tagVal .'" in ' . $this->getRefObject());
            return false;
        }

        if (!($scope = $this->getRefObject()->getInherits())) {
            if (!($scope = $this->getRefObject()->getDeclaringClass())) {
                $scope = $this->getRefObject()->name;
            }else{
                $scope = $scope->name;
            }
        } else {
            Core::getLogger()->warn("scope has inherits");
            $scope = $scope->name;
        }

        /**
         * Matches myMethod or myMethod()
         */
        $cleanMethod = Utils::cleanMethodName($tagVal);
        if (method_exists($scope, $cleanMethod)) {
            return array('type' => 'method', 'class' => $scope,
                'name' => $cleanMethod);
        }else{
            Core::getLogger()->warn("method $cleanMethod dos not exist in " . $scope);
        }

        /**
         * Matches property
         */
        if (Utils::isVar($tagVal)) {
            return array('type' => 'property', 'class' => $scope,
                'value' => Utils::cleanPropertyName($tagVal));
        }

        // External class
        if (Utils::isClass($tagVal)) {
            return array('type' => 'class', 'name' => $tagVal);
        }

        // Matches a file.ext
        if (($file = Utils::detectFileString($tagVal))) {
            return array('type' => 'file', 'value' => $file);
        }

        // Matches a URL ?
        if (($file = Utils::isUrl($tagVal))) {
            return array('type' => 'url', 'value' => $file);
        }

        Core::getLogger()->warn('Cannot parse @see tag "' . $tagVal .'" in ' . $this->getRefObject());
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($tagValue)
    {
        Core::getLogger()->debug('Parsing @see tag : "' . $tagValue . '"');

        $sees = array_map('trim', explode(',', $tagValue));
        $data = array();

        foreach ($sees as $tagVal) {
            if (($parsed = $this->parseMultiFormat($tagVal))) {
                $data[] = $parsed;
                Core::getLogger()->debug("@see tag ($tagVal) parsed : " . json_encode($parsed));
            }
        }

        return $data;
    }

}