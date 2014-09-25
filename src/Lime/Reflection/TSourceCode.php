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

use Doculizr\Core;

/**
 * Source Code Trait 
 */
trait TSourceCode {
    
    /**
     * Get Source Code
     * 
     * This method will get source code for the reflected object.
     * Depending on configuration, comments will be returned or not.
     * 
     * @return string
     */
    public function getSourceCode($all_file = false) {

        if($all_file) {
            return str_replace('<', '&lt;', file_get_contents($this->getFileName()));
        }

        $show_docblock = Core::getInstance()
                                ->getOption('source-code-show-docblock');
        if($show_docblock) {
            $docBlockLines = count(explode("\n", $this->getDocComment()));
        }else{
            $docBlockLines = 0;
        }
        
        $code = implode('', 
            array_slice(
                file($this->getFileName()), 
                $this->getStartLine() - 1 - $docBlockLines, 
                $this->getEndLine() - $this->getStartLine() + 1 + $docBlockLines
            )
        );
        
        return str_replace('<', '&lt;', $code);
    }

    public function getHighlightedLines()
    {
        return range($this->getStartLine(), $this->getEndLine());
    }


    
}