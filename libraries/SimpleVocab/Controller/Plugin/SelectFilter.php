<?php
class SimpleVocab_Controller_Plugin_SelectFilter extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $db = get_db();
        
        // Set NULL modules to default. Some routes do not have a default 
        // module, which resolves to NULL.
        $module = $request->getModuleName();
        if (is_null($module)) {
            $module = 'default';
        }
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        // Include all item actions that render an element form, including 
        // actions requested via AJAX.
        $routes = array(
            array('module' => 'default', 
                  'controller' => 'items', 
                  'actions' => array('add', 'edit', 'element-form', 'change-type'))
        );
        
        // Allow plugins to add routes that contain form inputs rendered by 
        // Omeka_View_Helper_ElementForm::_displayFormInput().
        $routes = apply_filters('simple_vocab_routes', $routes);
        
        // Apply filters to defined routes.
        foreach ($routes as $route) {
            if ($route['module'] === $module 
             && $route['controller'] === $controller 
             && in_array($action, $route['actions'])) {
                $simpleVocabTerms = $db->getTable('SimpleVocabTerm')->findAll();
                foreach ($simpleVocabTerms as $simpleVocabTerm) {
                    $element = $db->getTable('Element')->find($simpleVocabTerm->element_id);
                    $elementSet = $db->getTable('ElementSet')->find($element->element_set_id);
                    add_filter(array('Form', 
                                     'Item', 
                                     $elementSet->name, 
                                     $element->name), 
                               array($this, 'filterElement'));
                }
                // Once the filter is applied for one action it is applied
                // for all subsequent actions, so there is no need to
                // continue looping the routes.
                break;
            }
        }
    }
    
    public function filterElement($html, $inputNameStem, $value, $options, 
                                    $record, $element)
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
}
