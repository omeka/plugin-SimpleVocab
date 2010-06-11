<?php
class SimpleVocab_IndexController extends Omeka_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('element-texts', 'html')
                    ->addActionContext('element-terms', 'html');
        // I have no idea why I have to force the HTML context here. The AJAX 
        // context switch doesn't work otherwise. This may prove to be 
        // problematic in the future.
        // See: http://framework.zend.com/manual/en/zend.controller.actionhelpers.html
        $ajaxContext->initContext('html');
    }
    
    public function indexAction()
    {
        $this->view->terms = $this->getTable('SimpleVocabTerm')->findAll();
        $this->view->formSelectOptions = $this->_getFormSelectOptions();
    }
    
    public function editElementTermsAction()
    {
        $db = get_db();
        $elementId = $this->getRequest()->getParam('element_id');
        $terms = $this->getRequest()->getParam('terms');
        
        // Don't process the empty element select option.
        if ('' == $elementId) {
            $this->redirect->goto('index');
        }
        
        $simpleVocabTerm = $this->getTable('SimpleVocabTerm')->findByElementId($elementId);
        
        // Handle an existing term record.
        if ($simpleVocabTerm) {
             // Delete term record if there are no terms.
             if ('' == trim($terms)) {
                 $simpleVocabTerm->delete();
                 $this->flashSuccess('Successfully deleted the element\'s vocabulary terms.');
                 $this->redirect->goto('index');
             }
             $simpleVocabTerm->terms = $this->_sanitizeTerms($terms);
             $this->flashSuccess('Successfully edited the element\'s vocabulary terms.');
        
        // Handle a new term record.
        } else {
            // Do not save a new term record without terms.
            if ('' == trim($terms)) {
                $this->redirect->goto('index');
            }
            $simpleVocabTerm = new SimpleVocabTerm;
            $simpleVocabTerm->element_id = $elementId;
            $simpleVocabTerm->terms = $this->_sanitizeTerms($terms);
            $this->flashSuccess('Successfully added the element\'s vocabulary terms.');
        }
        $simpleVocabTerm->save();
        $this->redirect->goto('index');
    }
    
    public function elementTermsAction()
    {
        $db = get_db();
        $elementId = $this->getRequest()->getParam('element_id');
        $simpleVocabTerm = $this->getTable('SimpleVocabTerm')->findByElementId($elementId);
        if ($simpleVocabTerm) {
            $terms = $simpleVocabTerm->terms;
        } else {
            $terms = '';
        }
        $this->view->terms = $terms;
    }
    
    public function elementTextsAction()
    {
        $db = get_db();
        $elementId = $this->getRequest()->getParam('element_id');
        $select = $db->select()
                     ->from(array('et' => $db->ElementText), 
                            array('text', 'COUNT(*) AS count'))
                     ->group('text')
                     ->where('element_id = ?', $elementId)
                     ->order('count DESC');
        $elementTexts = $this->getTable('ElementText')->fetchObjects($select);
        
        $simpleVocabTerm = $this->getTable('SimpleVocabTerm')->findByElementId($elementId);
        if ($simpleVocabTerm) {
            $terms = explode("\n", $simpleVocabTerm->terms);
        } else {
            $terms = array();
        }
        $this->view->elementTexts    = $elementTexts;
        $this->view->simpleVocabTerm = $simpleVocabTerm;
        $this->view->terms           = $terms;
    }
    
    // @todo: port below SQL to Zend_Db_Select.
    private function _getFormSelectOptions()
    {
        $db = get_db();
        $sql = "
SELECT es.name AS element_set_name, it.name AS item_type_name, 
e.id AS element_id, e.name AS element_name, svt.id AS simple_vocab_term_id 
FROM {$db->prefix}record_types rt 
JOIN {$db->prefix}element_sets es 
ON rt.id = es.record_type_id 
JOIN {$db->prefix}elements e 
ON es.id = e.element_set_id 
LEFT JOIN {$db->prefix}item_types_elements ite 
ON e.id = ite.element_id 
LEFT JOIN {$db->prefix}item_types it 
ON ite.item_type_id = it.id 
LEFT JOIN {$db->prefix}simple_vocab_terms as svt 
ON e.id = svt.element_id 
WHERE (
    rt.name = 'All' 
    OR rt.name = 'Item'
)
ORDER BY es.name, it.name, e.name";
        $elements = $db->fetchAll($sql);
        $options = array('' => 'Select Below');
        foreach ($elements as $element) {
            $optGroup = $element['item_type_name'] 
                      ? 'Item Type: ' . $element['item_type_name'] 
                      : $element['element_set_name'];
            $value = $element['element_name'];
            if ($element['simple_vocab_term_id']) {
                $value .= ' *';
            }
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }
    
    private function _sanitizeTerms($terms)
    {
        $termsArr = explode("\n", $terms);
        $termsArr = array_map('trim', $termsArr);// trim all values
        $termsArr = array_filter($termsArr); // remove empty values
        $termsArr = array_unique($termsArr); // remove duplicate values
        $terms = implode("\n", $termsArr);
        $terms = trim($terms);
        return $terms;
    }
}