<h1>Database Error</h1>
<p>A database error has occurred while processing your request.</p>
<?php if (isset($message) && config('exceptions.debug', false)): ?>
<div class="error-details">
    <p><strong>Error:</strong> <?php echo $message; ?></p>
    <?php if (isset($code)): ?>
    <p><strong>Code:</strong> <?php echo $code; ?></p>
    <?php endif; ?>
    
    <?php if (isset($file) && isset($line)): ?>
    <p><strong>Location:</strong> <?php echo $file; ?> (line <?php echo $line; ?>)</p>
    <?php endif; ?>
    
    <?php if (isset($trace)): ?>
    <h3>Stack Trace:</h3>
    <pre><?php print_r($trace); ?></pre>
    <?php endif; ?>
</div>
<?php endif; ?>
<p><a href="/">Go back to homepage</a></p>
