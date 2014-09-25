<?php
//------------------------------------------------------------------------------
//
//  This file is part of Doculizr -- The PHP 5 Documentation Generator
//
//  Copyright (C) 2012 Matthias ETIENNE <matt@allty.com>
//
//  Permission is hereby granted, free of charge, to any person obtaining a
//  copy of this software and associated documentation files (the "Software"),
//  to deal in the Software without restriction, including without limitation
//  the rights to use, copy, modify, merge, publish, distribute, sublicense,
//  and/or sell copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following conditions:
//
//  The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
//  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
//  IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
//  DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
//  OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
//  THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
//------------------------------------------------------------------------------
namespace Doculizr\Reflection;

/**
 * Metadata Trait for classes implementing the IMetaData interface.
 */
trait TMetaData {
    
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
