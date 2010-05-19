<?php
add_plugin_hook('install', 'SimpleVocabPlugin::install');
add_plugin_hook('uninstall', 'SimpleVocabPlugin::uninstall');
add_plugin_hook('initialize', 'SimpleVocabPlugin::initialize');

add_filter('admin_navigation_main', 'SimpleVocabPlugin::adminNavigationMain');

class SimpleVocabPlugin
{
    public static function install()
    {
        $db = get_db();
        $sql = "
CREATE TABLE `simple_vocab_terms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(10) unsigned NOT NULL,
  `terms` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `element_id` (`element_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);
    }
    
    public static function uninstall()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}simple_vocab_terms`;";
        $db->query($sql);
    }
    
    public static function initialize()
    {
        // filter elements
        $db = get_db();
        $simpleVocabTerms = $db->getTable('SimpleVocabTerm')->findAll();
        foreach ($simpleVocabTerms as $simpleVocabTerm) {
            $element = $db->getTable('Element')->find($simpleVocabTerm->element_id);
            $elementSet = $db->getTable('ElementSet')->find($element->element_set_id);
            add_filter(array('Form', 
                             'Item', 
                             $elementSet->name, 
                             $element->name), 
                      'SimpleVocabPlugin::filterElement');
        }
    }
    
    public static function filterElement($html, $inputNameStem, $value, 
                                         $options, $record, $element)
    {
        $db = get_db();
        $simpleVocabTerm = $db->getTable('SimpleVocabTerm')->findByElementId($element->id);
        $terms = explode("\n", $simpleVocabTerm->terms);
        $selectTerms = array('' => 'Select Below') + array_combine($terms, $terms);
        return __v()->formSelect($inputNameStem . '[text]',
                                 $value,
                                 $options,
                                 $selectTerms);
    }
    
    public static function adminNavigationMain($nav)
    {
        $nav['Simple Vocab'] = uri('simple-vocab');
        return $nav;
    }
}
