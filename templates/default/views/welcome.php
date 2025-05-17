<h1>Welcome to LightWeight Framework</h1>

<div class="framework-intro">
    <p>You've successfully installed LightWeight, a lightweight PHP framework designed for building web applications with simplicity and efficiency.</p>
    
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
    
    <ul>
        <li><a href="https://github.com/yourusername/lightweight" target="_blank">GitHub Repository</a></li>
        <li><a href="/docs" target="_blank">Documentation</a></li>
    </ul>
</div>

<style>
    .framework-intro {
        margin-top: 30px;
    }
    .steps {
        margin: 30px 0;
    }
    .step {
        margin-bottom: 25px;
    }
    pre {
        background-color: #f5f5f5;
        padding: 15px;
        border-radius: 5px;
        overflow: auto;
    }
    code {
        background-color: #f5f5f5;
        padding: 2px 5px;
        border-radius: 3px;
        font-family: monospace;
    }
</style>
