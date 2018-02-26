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
    private $_lang;
    
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
     * Gets the list of languages.
     * 
     * @return string[]
     */
    public function getLangs()
    {
        return array_keys($this->_dicts);
    }
    
    /**
     * Gets a translation.
     * 
     * @param string   $key    Translation key
     * @param string[] $values Replacement values (not required)
     * 
     * @return string
     */
    public function get($key, $values = [])
    {
        $keys = array_filter(explode(".", $key));
        
        $ret = $this->_searchTranslation($keys, $this->_lang);
        if ($ret === null) {
            $ret = $this->_searchTranslation($keys, $this->_defaultLang);
        }
        
        // replaces translation parameters
        if ($ret !== null && count($values) > 0) {
            $ret = preg_replace_callback(
                '/{{(\w+)}}/',
                function ($matches) use ($values) {
                    $str = $matches[0];
                    $key = trim($matches[1]);
                    
                    if (array_key_exists($key, $values)) {
                        $str = $values[$key];
                    }
                    
                    return $str;
                },
                $ret
            );
        }
        
        return $ret === null ? $key : $ret;
    }
    
    /**
     * Searches a translation by a path.
     * 
     * @param string[] $keys List of keys
     * @param string   $lang Language
     * 
     * @return string
     */
    private function _searchTranslation($keys, $lang)
    {
        $ret = $this->_dicts[$lang];
        
        foreach ($keys as $key) {
            if (!property_exists($ret, $key)) {
                return null;
            }
            
            $ret = $ret->{$key};
        }
        
        return is_string($ret) ? $ret : null;
    }
}
