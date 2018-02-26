<?php
/**
 * This file is part of SoloProyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/sys/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/sys
 */
namespace soloproyectos\i18n;
use soloproyectos\i18n\exception\I18nException;
use soloproyectos\sys\file\SysFile;

/**
 * Class SysException.
 *
 * @package I18n
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/sys/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/sys
 */
class I18nTranslator
{
    /**
     * Default language.
     * 
     * @var string
     */
    public $defaultLang;
    
    /**
     * Current language.
     * 
     * @var string
     */
    public $lang;
    
    /**
     * List of dictionaries.
     * 
     * Contains an associative array of objects. Each key represents a language.
     * 
     * @var stdClass[string]
     */
    private $_dicts = [];
    
    /**
     * Loads a list of dictionaries from a directory.
     * 
     * @param string $dir  Directory path
     * @param string $lang Current language
     * 
     * @return void
     */
    public function loadDictionaries($dir, $lang)
    {
        // load languages
        $this->_dicts = [];
        $paths = scandir($dir);
        foreach ($paths as $path) {
            $fullPath = SysFile::concat($dir, $path);
            if (is_file($fullPath)) {
                $this->_loadDictionary($fullPath);
            }
        }
        
        $this->defaultLang = $lang;
        $this->lang = $lang;
    }
    
    /**
     * Gets a translation.
     * 
     * @param string $key Translation key
     * 
     * @return string
     */
    public function get($key)
    {
        $ret = $key;
        $lang = $this->lang;
        
        if ($this->_hasTranslation($key, $lang)) {
            $ret = $this->_getTranslation($key, $lang);
        } elseif ($this->_hasTranslation($key, $this->defaultLang)) {
            $ret = $this->_getTranslation($key, $this->defaultLang);
        }
        
        return $ret;
    }
    
    /**
     * Gets a translation for a specific language.
     * 
     * @param string $key  Translation key
     * @param string $lang Language
     */
    private function _getTranslation($key, $lang)
    {
        $dict = $this->_dicts[$lang];
        return $dict->{$key};
    }
    
    /**
     * Checks if a language contains a specific translation.
     * 
     * @param string $key  Translation key
     * @param string $lang Language
     */
    private function _hasTranslation($key, $lang)
    {
        $dict = $this->_dicts[$lang];
        return property_exists($dict, $key);
    }
    
    /**
     * Loads a dictionary from a JSON file.
     * 
     * @param string $path JSON file
     * 
     * @return void
     */
    private function _loadDictionary($path)
    {
        if (!is_file($path)) {
            throw new I18nException("File not found");
        }
        
        $lang = pathinfo($path, PATHINFO_FILENAME);
        $contents = file_get_contents($path);
        $dictionary = json_decode($contents);
        if ($dictionary === null) {
            throw new I18nException("Invalid JSON format");
        }
        
        $this->_dicts[$lang] = $dictionary;
    }
}
