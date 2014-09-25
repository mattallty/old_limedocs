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
 * Interface for all tags
 */
interface ITag {

    /**
     * Get the tag description
     *
     * @return string Description
     */
    public function getDescription();

    /**
     * Get the tag label
     *
     * @return string label
     */
    public function getLabel();

    /**
     * Get the tag under the form "tag"
     *
     * @return string Tag name
     */
    public function getTag();

    /**
     * Parse tag data
     *
     * @param string $tagValue tag value
     * @return array parsed data
     */
    public function parseData($tagValue);

    /**
     * Does the tag allow its associated value to be written on multiple lines
     *
     * @return bool true if the tag allows multiple lines
     */
    public function allowMultiLines();

    /**
     * Does the tag can be present multiple times in a same DocBlock
     *
     * @return bool true if the tag can be present multiple times
     */
    public function allowMultiTimes();

    /**
     * Does the tag can be present multiple times in a same DocBlock
     *
     * @return bool true if the tag can be present multiple times
     */
    public function overrideSameTag();

    /**
     * Allow tag value to be empty
     *
     * @return bool return true to allow empty tag value
     */
    public function allowEmpty();

    /**
     * Return the parsed data
     *
     * @return array
     */
    public function getParsedData();
}

?>
