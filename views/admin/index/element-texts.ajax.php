<?php if (!count($this->elementTexts)): ?>
<p><strong>No texts for the selected element exist in your archive.</strong></p>
<?php else: ?>
<table>
    <tr>
        <th>Count</th>
        <th>Warnings</th>
        <th>Text</th>
    </tr>
    <?php foreach ($this->elementTexts as $elementText):
        $warnings = array();
        if ($this->simpleVocabTerm && !in_array($elementText->text, $this->terms)) $warnings[] = 'Not in vocabulary.';
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
<?php endif; ?>
