<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Reflection;

/**
 * Interface to attach metadata to Reflection objects
 */
interface IMetaData
{
    
    /**
     * Set metadata
     *
     * @param array $metadata Metadata array to set
     */
    public function setMetaData(array $metadata = null);
    /**
     * Get metadata
     *
     * @param string key Key to get value for
     * @return mixed If $key is provided, this method will return the entire
     * metadata array. If $key is provided, the associated metadata will be
     * returned. If no metadata are available, NULL will be returned.
     */
    public function getMetaData($key = null);
    
}
