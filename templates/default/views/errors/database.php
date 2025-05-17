<div class="error-container">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="120" height="120">
            <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z" fill="#1e88e5"/>
        </svg>
    </div>
    <h1>Database Error</h1>
    <div class="error-description">
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
            <div class="stack-trace">
                <pre><?php print_r($trace); ?></pre>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="error-actions">
        <a href="/" class="btn-primary">Go back to homepage</a>
    </div>
</div>

<style>
    .error-container {
        text-align: center;
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .error-icon {
        margin-bottom: 20px;
    }
    
    .error-container h1 {
        color: #1565c0;
        font-size: 36px;
        margin-bottom: 20px;
        font-weight: 300;
    }
    
    .error-description {
        color: #546e7a;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .error-details {
        text-align: left;
        background-color: #e3f2fd;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        color: #0d47a1;
    }
    
    .stack-trace {
        background-color: #263238;
        border-radius: 6px;
        margin-top: 15px;
        overflow: auto;
        max-height: 400px;
    }
    
    pre {
        color: #fff;
        padding: 15px;
        margin: 0;
        overflow: auto;
        font-family: 'Source Code Pro', 'Courier New', monospace;
        font-size: 14px;
    }
    
    .error-actions {
        margin-top: 30px;
    }
    
    .btn-primary {
        display: inline-block;
        background-color: #1e88e5;
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    
    .btn-primary:hover {
        background-color: #1565c0;
        text-decoration: none;
    }
    
    strong {
        color: #0d47a1;
    }
    
    h3 {
        color: #1565c0;
        margin-top: 20px;
    }
</style>
