<?php
/**
 * This file is part of Doculizr -- The PHP 5 Documentation Generator
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
 * THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */
namespace Doculizr\Template;

/**
 * Main templating class
 */
class DoculizrTemplate extends AbstractTemplate {

    public function output() {
        
    }
    
    
    public function getIndexFile() {
        return 'index.twig.html';
    }
    public function getNsFile() {
        return 'ns.twig.html';
    }
    public function getClassFile() {
        return 'class.twig.html';
    }
    public function getInterfaceFile() {
        return 'interface.twig.html';
    }
    public function getTraitFile() {
        return 'trait.twig.html';
    }
    public function getMethodFile() {
        return 'method.twig.html';
    }
    
}
