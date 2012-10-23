<?php
/**
 * Simple Vocab
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Simple Vocab controller.
 * 
 * @package Omeka\Plugins\SimpleVocab
 */
class SimpleVocab_IndexController extends Omeka_Controller_AbstractActionController
{
    protected $_actionContexts = array('element-texts' => 'html', 
                                       'element-terms' => 'html');
    
    /**
     * Initialize this controller.
     */
    public function init()
    {
        // Restrict actions to AJAX requests.
        $this->_helper->getHelper('AjaxContext')
                      ->addActionContexts($this->_actionContexts)
                      ->initContext();
    }
    
    /**
     * Render the edit element terms form.
     */
    public function indexAction()
    {
        $this->view->options_for_select = $this->_getOptionsForSelect();
        $this->view->terms = $this->_helper->db->getTable('SimpleVocabTerm')->findAll();
    }
    
    /**
     * Process the element terms form and redirect to the index action.
     */
    public function editElementTermsAction()
    {
        $elementId = $this->getRequest()->getParam('element_id');
        $terms = $this->getRequest()->getParam('terms');
        
        // Don't process the empty element select option.
        if ('' == $elementId) {
            $this->_helper->redirector('index');
        }
        
        $simpleVocabTerm = $this->_helper->db->getTable('SimpleVocabTerm')->findByElementId($elementId);
        
        // Handle an existing term record.
        if ($simpleVocabTerm) {
             // Delete term record if there are no terms.
             if ('' == trim($terms)) {
                 $simpleVocabTerm->delete();
                 $this->_helper->flashMessenger('Successfully deleted the element\'s vocabulary terms.', 'success');
                 $this->_helper->redirector('index');
             }
             $simpleVocabTerm->terms = $this->_sanitizeTerms($terms);
             $this->_helper->flashMessenger('Successfully edited the element\'s vocabulary terms.', 'success');
        
        // Handle a new term record.
        } else {
            // Do not save a new term record without terms.
            if ('' == trim($terms)) {
                $this->_helper->redirector('index');
            }
            $simpleVocabTerm = new SimpleVocabTerm;
            $simpleVocabTerm->element_id = $elementId;
            $simpleVocabTerm->terms = $this->_sanitizeTerms($terms);
            $this->_helper->flashMessenger('Successfully added the element\'s vocabulary terms.', 'success');
        }
        $simpleVocabTerm->save();
        $this->_helper->redirector('index');
    }
    
    /**
     * Render the edit terms.
     * 
     * Available only via an AJAX request.
     */
    public function elementTermsAction()
    {
        $elementId = $this->getRequest()->getParam('element_id');
        $simpleVocabTerm = $this->_helper->db->getTable('SimpleVocabTerm')->findByElementId($elementId);
        if ($simpleVocabTerm) {
            $terms = $simpleVocabTerm->terms;
        } else {
            $terms = '';
        }
        $this->view->terms = $terms;
    }
    
    /**
     * Render the element texts.
     * 
     * Available only via an AJAX request.
     */
    public function elementTextsAction()
    {
        $elementId = $this->getRequest()->getParam('element_id');
        $simpleVocabTerm = $this->_helper->db->getTable('SimpleVocabTerm');
        $elementTexts = $simpleVocabTerm->findElementTexts($elementId);
        $simpleVocabTerm = $simpleVocabTerm->findByElementId($elementId);
        if ($simpleVocabTerm) {
            $terms = explode("\n", $simpleVocabTerm->terms);
        } else {
            $terms = array();
        }
        $this->view->element_texts = $elementTexts;
        $this->view->simple_vocab_term = $simpleVocabTerm;
        $this->view->terms = $terms;
    }
    
    /**
     * Get the form select options.
     * 
     * @return array
     */
    private function _getOptionsForSelect()
    {
        $elements = $this->_helper->db->getTable('SimpleVocabTerm')->findElementsForSelect();
        $options = array('' => 'Select Below');
        foreach ($elements as $element) {
            if ($element['item_type_name']) {
                $optGroup = 'Item Type: ' . $element['item_type_name'];
            } else {
                $optGroup = $element['element_set_name'];
            }
            $value = $element['element_name'];
            if ($element['simple_vocab_term_id']) {
                $value .= ' *';
            }
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }
    
    /**
     * Sanitize the terms for insertion into the database.
     * 
     * @param string $terms
     * @return string
     */
    private function _sanitizeTerms($terms)
    {
        $termsArr = explode("\n", $terms);
        $termsArr = array_map('trim', $termsArr); // trim all values
        $termsArr = array_filter($termsArr); // remove empty values
        $termsArr = array_unique($termsArr); // remove duplicate values
        $terms = implode("\n", $termsArr);
        $terms = trim($terms);
        return $terms;
    }
}
