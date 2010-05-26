<?php
class SimpleVocab_Controller_Plugin_SelectFilter extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $actions = array('edit', 'add', 'element-form');
        if ('items' == $request->getControllerName() 
            && in_array($request->getActionName(), $actions)) {
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
                           array($this, 'filterElement'));
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