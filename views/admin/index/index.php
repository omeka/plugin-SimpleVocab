<?php
$head = array('bodyclass' => 'simple-vocab primary', 
              'title' => 'Simple Vocab');
head($head);
?>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
    Event.observe(window, 'load', function(){
        Event.observe('element-id', 'change', function(event){
            new Ajax.Request(<?php echo js_escape(uri(array('module' => 'simple-vocab', 
                                                            'controller' => 'index', 
                                                            'action' => 'element-terms'))); ?>, {
                method: 'get',
                parameters: {'element_id': $('element-id').getValue()},
                onComplete: function(transport) {
                    $('terms').value = transport.responseText;
                }
            })
        });
        Event.observe('display-texts', 'click', function(event){
            new Ajax.Request(<?php echo js_escape(uri(array('module' => 'simple-vocab', 
                                                            'controller' => 'index', 
                                                            'action' => 'element-texts'))); ?>, {
                method: 'get',
                parameters: {'element_id': $('element-id').getValue()},
                onComplete: function(transport) {
                    $('texts').update(transport.responseText);
                }
            })
        });
    });
//]]>
</script>
<h1><?php echo $head['title']; ?></h1>
<div id="primary">
    <?php echo flash(); ?>
    <form method="post" action="<?php echo uri(array('module' => 'simple-vocab', 
                                                     'controller' => 'index', 
                                                     'action' => 'edit-element-terms')); ?>">
        <div class="field">
            <label for="element-id">Element</label>
            <div class="inputs">
                <?php echo $this->formSelect('element_id', 
                                             null, 
                                             array('id' => 'element-id'), 
                                             $this->formSelectOptions) ?>
                <p class="explanation">Select an element to manage its custom 
                vocabulary. Elements with a custom vocabulary are marked with an 
                asterisk (*).</p>
            </div>
        </div>
        <div class="field">
            <label for="terms">Vocabulary Terms</label>
            <div class="inputs">
                <?php echo $this->formTextarea('terms', 
                                               null, 
                                               array('id' => 'terms', 
                                                     'rows' => '20', 
                                                     'cols' => '50')) ?>
                <p class="explanation">Enter the custom vocabulary terms for 
                this element, one per line. To delete the vocabulary, simply 
                remove the terms and sumbit this form.</p>
            </div>
        </div>
        <?php echo $this->formSubmit('edit_vocab', 
                                     'Add/Edit Vocabulary', 
                                     array('class' => 'submit submit-large')); ?>
    </form>
    <p><a id="display-texts" href="#display-texts"><strong>Click here</strong></a> 
    to display a list of texts that currently exist in your archive. You may use 
    this list as a reference to build a vocabulary, but be aware of some caveats:</p>
    <ul style="list-style: disc;margin-left: 1.5em;">
        <li>Vocabulary terms must not contain newlines (line breaks).</li>
        <li>Vocabulary terms are typically short and concise. If your existing 
        texts are otherwise, avoid using a controlled vocabulary for this 
        element.</li>
        <li>Vocabulary terms must be identical to their corresponding texts.</li>
        <li>Existing texts that are not in the vocabulary will be preserved — 
        however, they cannot be selected in the item edit page, and will be 
        deleted once you save the item.</li>
    </ul>
    <div id="texts"></div>
</div>
<?php foot(); ?>