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

use Doculizr\Options\IOptionsAware;
use Doculizr\Options\TOptions;
use Doculizr\Parser\IParserAware;
use Doculizr\Parser\TParser;

/**
 * Templating asbtract class
 *
 */
abstract class AbstractTemplate implements ITemplate, IParserAware, IOptionsAware {
    
    use TOptions, TParser;
    
    /**
     * @var array Template informations
     */
    protected $infos;
    /**
     * @var string Template manifest file path
     */
    protected $manifestFile;
    /**
     * @var string Template name 
     */
    protected $name; 
    /**
     * @var string Template version
     */
    protected $version;
    /**
     * @var string Template path
     */
    protected $path;
    
    public function __construct(array $tpl_infos)
    {
        $this->infos = $tpl_infos;
    }
    
    public function getVersion() {
        return $this->infos['version'];
    }
    
    public function getName() {
        return $this->infos['name'];
    }
    
    public function getInfos($info = null)
    {
        return is_null($info) ? $this->infos : 
            (isset($this->infos[$info]) ? 
                $this->infos[$info] : null);
    }
    
    public function getPath()
    {
        return DOCULIZR_DATA_DIR . DS . 'templates' . DS .
                $this->getName() . DS . $this->getVersion();
    }
    
    protected function getManifestFile()
    {
        return $this->getPath() . DS . 'template.json';
    }
    

    

}
