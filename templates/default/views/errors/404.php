<div class="error-container">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="120" height="120">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#1e88e5"/>
        </svg>
    </div>
    <h1>404 Not Found</h1>
    <div class="error-description">
        <p>The page you are looking for could not be found.</p>
        <p>The requested URL was not found on this server. If you entered the URL manually, please check your spelling and try again.</p>
    </div>
    <div class="error-actions">
        <a href="/" class="btn-primary">Go back to homepage</a>
    </div>
</div>

<style>
    .error-container {
        text-align: center;
        padding: 20px;
        max-width: 600px;
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
</style>
