<?php
/**
 * This file is part of SoloProyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/sys/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/sys
 */
namespace soloproyectos\i18n\translator;
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
    private $_defaultLang;
    
    /**
     * Language in use.
     * 
     * @var string
     */
    public $_lang;
    
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
     * @param string $lang Language in use
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
                $language = pathinfo($fullPath, PATHINFO_FILENAME);
                $contents = file_get_contents($fullPath);
                $dictionary = json_decode($contents);
                
                if ($dictionary === null) {
                    throw new I18nException("Invalid JSON format: $path");
                }
                
                $this->_dicts[$language] = $dictionary;
            }
        }
        
        $this->_defaultLang = $lang;
        $this->_lang = $lang;
    }
    
    /**
     * Uses a specific language.
     * 
     * @param string $lang Language
     * 
     * @return void
     */
    public function useLang($lang)
    {
        if (!array_key_exists($lang, $this->_dicts)) {
            throw new I18nException("Language not found: $lang");
        }
        
        $this->_lang = $lang;
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
        
        if ($this->_hasTranslation($key, $this->_lang)) {
            $ret = $this->_getTranslation($key, $this->_lang);
        } elseif ($this->_hasTranslation($key, $this->_defaultLang)) {
            $ret = $this->_getTranslation($key, $this->_defaultLang);
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
}