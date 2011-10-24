<?php
add_plugin_hook('install', 'SimpleVocabPlugin::install');
add_plugin_hook('uninstall', 'SimpleVocabPlugin::uninstall');
add_plugin_hook('initialize', 'SimpleVocabPlugin::initialize');
add_plugin_hook('define_acl', 'SimpleVocabPlugin::defineAcl');

add_filter('admin_navigation_main', 'SimpleVocabPlugin::adminNavigationMain');

class SimpleVocabPlugin
{
    public static function install()
    {
        $db = get_db();
        $sql = "
        CREATE TABLE `{$db->prefix}simple_vocab_terms` (
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
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new SimpleVocab_Controller_Plugin_SelectFilter);
    }
    
    public static function adminNavigationMain($nav)
    {
        $nav['Simple Vocab'] = uri('simple-vocab');
        return $nav;
    }
    
    public static function defineAcl($acl)
    {
        $acl->loadResourceList(array('SimpleVocab_Index' => array(
            'index', 'editElementTerms', 'elementTerms', 'elementTexts'
        )));
    }
}
