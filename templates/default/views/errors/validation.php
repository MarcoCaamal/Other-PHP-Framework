<h1>Validation Errors</h1>
<p>There were validation errors with your submission.</p>

<?php if (isset($errors) && count($errors) > 0): ?>
<div class="validation-errors">
    <ul>
    <?php foreach ($errors as $field => $messages): ?>
        <?php foreach ($messages as $message): ?>
        <li><strong><?php echo ucfirst($field); ?>:</strong> <?php echo $message; ?></li>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<p><a href="javascript:history.back()">Go back</a> and correct the errors.</p>
