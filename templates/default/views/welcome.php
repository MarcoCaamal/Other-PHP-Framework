<div class="welcome-container">
    <div class="hero">
        <div class="hero-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="120" height="120">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="#1e88e5"/>
            </svg>
        </div>
        <div class="hero-text">
            <h1>Welcome to LightWeight</h1>
            <p class="tagline">A modern, lightweight PHP framework for elegant web applications</p>
        </div>
    </div>

    <div class="framework-intro">
        <h2>Start Building Amazing Apps</h2>
        <p>You've successfully installed LightWeight, a framework designed with simplicity, performance, and developer experience in mind.</p>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">🚀</div>
                <h3>Fast &amp; Efficient</h3>
                <p>Optimized for performance with minimal overhead.</p>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🧩</div>
                <h3>Modular Architecture</h3>
                <p>Only use the components you need.</p>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🛠️</div>
                <h3>Developer Friendly</h3>
                <p>Intuitive APIs and clear documentation.</p>
            </div>
        </div>
        
        <h2>Next Steps</h2>
        
        <div class="steps">
            <div class="step">
                <h3>1. Configure your application</h3>
                <p>Update your <code>.env</code> file with your database credentials and application settings.</p>
            </div>
            
            <div class="step">
                <h3>2. Create your first route</h3>
                <p>Open <code>routes/web.php</code> and add your routes:</p>
                <pre><code>$router->get('hello', function () {
    return 'Hello, World!';
});</code></pre>
            </div>
            
            <div class="step">
                <h3>3. Create a controller</h3>
                <p>Run the command:</p>
                <pre><code>php light make:controller HomeController</code></pre>
                <p>Then add methods to handle your requests.</p>
            </div>
        </div>
        
        <h2>Resources</h2>
        
        <div class="resources">
            <a href="https://github.com/yourusername/lightweight" class="resource-link" target="_blank">
                <span>📖 Documentation</span>
            </a>
            <a href="/docs" class="resource-link" target="_blank">
                <span>👨‍💻 GitHub Repository</span>
            </a>
        </div>
    </div>
</div>

<style>
    .welcome-container {
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }
    
    .hero {
        display: flex;
        align-items: center;
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 1px solid #e1f5fe;
    }
    
    @media (max-width: 768px) {
        .hero {
            flex-direction: column;
            text-align: center;
        }
    }
    
    .hero-logo {
        margin-right: 30px;
    }
    
    .hero-text h1 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #1565c0;
        font-weight: 300;
        font-size: 2.5em;
    }
    
    .tagline {
        color: #5c6bc0;
        font-size: 1.2em;
        margin-top: 0;
    }
    
    .framework-intro h2 {
        color: #1976d2;
        margin-top: 40px;
        font-weight: 400;
        border-bottom: 1px solid #bbdefb;
        padding-bottom: 10px;
    }
    
    .features {
        display: flex;
        justify-content: space-between;
        margin: 40px 0;
        gap: 20px;
    }
    
    @media (max-width: 768px) {
        .features {
            flex-direction: column;
        }
    }
    
    .feature {
        flex: 1;
        background-color: #e3f2fd;
        padding: 25px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 3px 5px rgba(0,0,0,0.05);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .feature:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .feature-icon {
        font-size: 2.5em;
        margin-bottom: 15px;
    }
    
    .feature h3 {
        color: #1565c0;
        margin-top: 0;
    }
    
    .steps {
        margin: 30px 0;
    }
    
    .step {
        margin-bottom: 30px;
        padding: 20px;
        border-left: 4px solid #2196f3;
        background-color: #f9fafe;
        border-radius: 0 8px 8px 0;
    }
    
    .step h3 {
        margin-top: 0;
        color: #0d47a1;
    }
    
    pre {
        background-color: #263238;
        color: #fff;
        padding: 15px;
        border-radius: 8px;
        overflow: auto;
        margin: 15px 0;
    }
    
    code {
        background-color: #e1f5fe;
        color: #0277bd;
        padding: 3px 6px;
        border-radius: 4px;
        font-family: 'Source Code Pro', 'Courier New', monospace;
        font-size: 0.9em;
    }
    
    pre code {
        background-color: transparent;
        color: #fff;
        padding: 0;
    }
    
    .resources {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    @media (max-width: 480px) {
        .resources {
            flex-direction: column;
        }
    }
    
    .resource-link {
        display: inline-block;
        padding: 12px 20px;
        background-color: #1e88e5;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    
    .resource-link:hover {
        background-color: #1565c0;
        text-decoration: none;
    }
</style>
