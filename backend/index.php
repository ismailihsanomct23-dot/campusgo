<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CampusGo - Initializing</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .container {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      max-width: 500px;
      text-align: center;
    }
    h1 {
      color: #333;
      margin-bottom: 10px;
    }
    .status {
      margin: 20px 0;
      padding: 15px;
      border-radius: 8px;
      font-size: 14px;
    }
    .status.loading {
      background: #e3f2fd;
      color: #1976d2;
    }
    .status.success {
      background: #e8f5e9;
      color: #388e3c;
    }
    .status.error {
      background: #ffebee;
      color: #d32f2f;
    }
    .spinner {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 3px solid #ddd;
      border-top-color: #667eea;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin-right: 8px;
      vertical-align: middle;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .button {
      display: inline-block;
      margin-top: 20px;
      padding: 12px 30px;
      background: #667eea;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s;
    }
    .button:hover {
      background: #764ba2;
    }
    .debug-info {
      background: #f5f5f5;
      padding: 10px;
      border-radius: 6px;
      margin-top: 20px;
      font-size: 12px;
      text-align: left;
      color: #666;
      font-family: monospace;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🚌 CampusGo</h1>
    <p>Initializing your system...</p>
    
    <div id="status" class="status loading">
      <span class="spinner"></span>
      <span id="statusText">Connecting to database...</span>
    </div>

    <div id="result"></div>
    <div id="debugInfo" class="debug-info">
      <p>Current URL: <span id="currentUrl"></span></p>
      <p>API will call: <span id="apiUrl"></span></p>
    </div>
  </div>

  <script>
    // Show current location info
    document.getElementById('currentUrl').textContent = window.location.href;
    document.getElementById('apiUrl').textContent = window.location.origin + '/api/health.php';
    
    async function init() {
      const statusEl = document.getElementById('statusText');
      const resultEl = document.getElementById('result');
      
      try {
        // Test API health using relative path (works when on port 8000)
        statusEl.textContent = 'Testing API connection...';
        const apiUrl = '/api/health.php';
        console.log('Fetching:', apiUrl);
        
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        console.log('Response:', data);
        
        if (data.ok) {
          statusEl.textContent = '✓ Database initialized';
          document.getElementById('status').className = 'status success';
          
          resultEl.innerHTML = `
            <h2>✅ System Ready!</h2>
            <p style="color: #666; font-size: 14px;">
              Routes initialized: <strong>${data.routes}</strong><br>
              API Status: <strong>Connected</strong>
            </p>
            <a href="/campusgo.html" class="button">Open CampusGo App</a>
          `;
        } else {
          throw new Error(data.message || 'API returned error');
        }
      } catch (error) {
        console.error('Error:', error);
        document.getElementById('status').className = 'status error';
        statusEl.textContent = '❌ Error: ' + error.message;
        resultEl.innerHTML = `
          <p style="color: #d32f2f; font-size: 14px; margin-top: 10px;">
            <strong>Troubleshooting:</strong><br>
            1. Make sure you're visiting the correct port (8000)<br>
            2. Check PHP server is running<br>
            3. Wait 30 seconds for database to initialize<br>
            4. Refresh the page (Ctrl+R)
          </p>
        `;
      }
    }
    
    // Start initialization after 500ms
    setTimeout(init, 500);
  </script>
</body>
</html>
