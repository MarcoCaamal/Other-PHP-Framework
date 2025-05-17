<div class="error-container">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="120" height="120">
            <path d="M11,15H13V17H11V15M11,7H13V13H11V7M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z" fill="#1e88e5"/>
        </svg>
    </div>
    <h1>Validation Errors</h1>
    <div class="error-description">
        <p>There were validation errors with your submission. Please correct the errors and try again.</p>
        
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
    </div>
    <div class="error-actions">
        <a href="javascript:history.back()" class="btn-primary">Go back and correct the errors</a>
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
    
    .validation-errors {
        max-width: 600px;
        margin: 20px auto;
        background-color: #e3f2fd;
        padding: 20px 20px 20px 40px;
        border-radius: 8px;
        text-align: left;
    }
    
    .validation-errors ul {
        margin: 0;
        padding: 0;
        list-style-type: none;
    }
    
    .validation-errors li {
        padding: 8px 0;
        color: #0d47a1;
        border-bottom: 1px solid #bbdefb;
    }
    
    .validation-errors li:last-child {
        border-bottom: none;
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
        color: #1565c0;
    }
</style>
