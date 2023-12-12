<?php if (!$element_texts): ?>
<p class="error"><?php echo __('No texts for the selected element exist in Omeka.'); ?></p>
<?php else: ?>
<table>
    <tr>
        <th><?php echo __('Count'); ?></th>
        <th><?php echo __('Warnings'); ?></th>
        <th><?php echo __('Text'); ?></th>
    </tr>
    <?php foreach ($element_texts as $element_text): ?>
    <tr>
        <td><?php echo $element_text['count']; ?></td>
        <td class="error"><?php echo implode("<br />", $element_text['warnings']); ?></td>
        <td>
        <?php if(!get_option('simple_vocab_files')):?>
            <a target="blank" href="<?php echo html_escape(url('items/browse?search=&advanced[0][joiner]=and&advanced[0][element_id]='.$element_text['element_id'].'&advanced[0][type]=is+exactly&advanced[0][terms]='.urlencode(str_replace('\n','%0D%0A',$element_text['text'])))); ?>"><?php echo snippet(nl2br($element_text['text']), 0, 600); ?></a>
        <?php else:?>
            <?php echo snippet(nl2br($element_text['text']), 0, 600); ?>
        <?php endif;?>
        </td>
        
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
