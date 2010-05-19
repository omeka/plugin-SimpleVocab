<?php
class SimpleVocab_IndexController extends Omeka_Controller_Action
{
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
    
    // @todo: try to implement AjaxContext, which did not work in early testing.
    public function elementTermsAction()
    {
        $db = get_db();
        $elementId = $this->getRequest()->getParam('element_id');
        $simpleVocabTerm = $this->getTable('SimpleVocabTerm')->findByElementId($elementId);
        if ($simpleVocabTerm) {
             echo $simpleVocabTerm->terms;
        } else {
            echo '';
        }
        exit;
    }
    
    // @todo: try to implement AjaxContext, which did not work in early testing.
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
            $termsArr = explode("\n", $simpleVocabTerm->terms);
        }
?>
<table>
    <tr>
        <th>Count</th>
        <th>Warnings</th>
        <th>Text</th>
    </tr>
    <?php foreach ($elementTexts as $elementText):
        $warnings = array();
        if ($simpleVocabTerm && !in_array($elementText->text, $termsArr)) $warnings[] = 'Not in vocabulary.';
        if (100 < strlen($elementText->text)) $warnings[] = 'Long text.';
        if (strstr($elementText->text, "\n")) $warnings[] = 'Contains newlines.';
        ?>
    <tr>
        <td><?php echo $elementText->count; ?></td>
        <td style="color:red;"><?php echo implode("<br />", $warnings); ?></td>
        <td><?php echo nl2br($elementText->text); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php
        exit;
    }
    
    // @todo: seperate the item type metadata element set into discreet item 
    // types to avoid element ambiguity.
    private function _getFormSelectOptions()
    {
        $db = get_db();
        $elementSets = $db->getTable('ElementSet')->findAll();
        $options = array('' => 'Select Below');
        foreach ($elementSets as $elementSet) {
            $elements = $db->getTable('Element')->findBySet($elementSet->name);
            foreach ($elements as $element) {
                $selectValue = $element->name;
                $simpleVocabTerm = $this->getTable('SimpleVocabTerm')->findByElementId($element->id);
                if ($simpleVocabTerm) {
                    $selectValue = $selectValue . ' *';
                }
                $options[$elementSet->name][$element->id] = $selectValue;
            }
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