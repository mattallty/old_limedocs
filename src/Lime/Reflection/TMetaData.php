<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Reflection;

/**
 * Metadata Trait for classes implementing the IMetaData interface.
 */
trait TMetaData
{
    
    /**
     * Metadata holder
     * @var array
     */
    protected $metadata = array();
    
    /**
     * Sets metadata
     *
     * @param array $metadata Metadata array to set
     */
    public function setMetaData(array $metadata = null)
    {
        $this->metadata = $metadata;
        return $this;
    }
    
    /**
     * Gets metadata
     *
     * @param string $key The key to lookup
     * @return mixed If $key is provided, this method will return the entire
     * metadata array. If $key is provided, the associated metadata will be
     * returned. If no metadata are available, NULL will be returned.
     */
    public function getMetaData($key = null)
    {
        return is_null($key) ?
                $this->metadata :
                (is_array($this->metadata) && isset($this->metadata[$key]) ?
                      $this->metadata[$key] : null
                );
    }
    
}
