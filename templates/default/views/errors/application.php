<h1>Application Error</h1>
<p>The application encountered an unexpected error.</p>
<?php if (isset($message) && config('exceptions.debug', false)): ?>
<div class="error-details">
    <p><strong>Error:</strong> <?php echo $message; ?></p>
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
