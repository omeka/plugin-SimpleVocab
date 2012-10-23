<?php if (!$element_texts): ?>
<p><strong>No texts for the selected element exist in Omeka.</strong></p>
<?php else: ?>
<table>
    <tr>
        <th>Count</th>
        <th>Warnings</th>
        <th>Text</th>
    </tr>
    <?php foreach ($element_texts as $element_text):
        $warnings = array();
        if ($simple_vocab_term && !in_array($element_text->text, $terms)) $warnings[] = 'Not in vocabulary.';
        if (100 < strlen($element_text->text)) $warnings[] = 'Long text.';
        if (strstr($element_text->text, "\n")) $warnings[] = 'Contains newlines.';
        ?>
    <tr>
        <td><?php echo $element_text->count; ?></td>
        <td style="color:red;"><?php echo implode("<br />", $warnings); ?></td>
        <td><?php echo snippet(nl2br($element_text->text), 0, 600); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
