<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth Test Interface</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .code-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">🔐 Google OAuth Test Interface</h1>
        
        <!-- Configuration Check -->
        <div class="test-section">
            <h3>1. Configuration Check</h3>
            <div id="config-status">Checking...</div>
        </div>

        <!-- Frontend Web Test -->
        <div class="test-section">
            <h3>2. Frontend Web Login Test</h3>
            <p>Tests Google login for regular users (patients/customers)</p>
            <button class="btn btn-primary" onclick="testFrontendLogin()">Test Frontend Login</button>
            <div id="frontend-result" class="mt-3"></div>
        </div>

        <!-- Backend Admin Test -->
        <div class="test-section">
            <h3>3. Backend Admin Login Test</h3>
            <p>Tests Google login for admin/doctor/vendor accounts</p>
            <button class="btn btn-success" onclick="testBackendLogin()">Test Backend Login</button>
            <div id="backend-result" class="mt-3"></div>
        </div>

        <!-- Mobile API Test -->
        <div class="test-section">
            <h3>4. Mobile App API Test</h3>
            <p>Tests Google login for mobile app via API</p>
            <button class="btn btn-info" onclick="testApiLogin()">Test API Login</button>
            <div id="api-result" class="mt-3"></div>
        </div>

        <!-- Manual Test Links -->
        <div class="test-section">
            <h3>5. Manual Test Links</h3>
            <p>Click these links to test manually in browser:</p>
            <div class="row">
                <div class="col-md-6">
                    <h6>Frontend</h6>
                    <ul>
                        <li><a href="/auth/google" target="_blank">/auth/google</a></li>
                        <li><a href="/login" target="_blank">/login</a></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Backend</h6>
                    <ul>
                        <li><a href="/login/google" target="_blank">/login/google</a></li>
                        <li><a href="/admin/login" target="_blank">/admin/login</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- API Test Form -->
        <div class="test-section">
            <h3>6. API Test Form</h3>
            <p>Test the API endpoint directly:</p>
            <form id="api-test-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="api-email" 
                                   value="test.google.<?php echo time(); ?>@example.com" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">User Type</label>
                            <select class="form-control" id="api-user-type">
                                <option value="user">User (Patient)</option>
                                <option value="doctor">Doctor</option>
                                <option value="vendor">Vendor</option>
                                <option value="receptionist">Receptionist</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="api-first-name" value="Test" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="api-last-name" value="Google" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning">Test API Endpoint</button>
            </form>
            <div id="api-form-result" class="mt-3"></div>
        </div>

        <!-- Debug Information -->
        <div class="test-section">
            <h3>7. Debug Information</h3>
            <button class="btn btn-secondary" onclick="loadDebugInfo()">Load Debug Info</button>
            <div id="debug-info" class="mt-3"></div>
        </div>
    </div>

    <script>
        // Configuration check
        async function checkConfiguration() {
            try {
                const response = await fetch('/api/app-configuration');
                const data = await response.json();
                
                let html = '<h5>Configuration Status:</h5>';
                html += '<ul>';
                html += '<li>API Status: <span class="status-ok">✅ Working</span></li>';
                html += '<li>App URL: ' + (data.app_name || 'Not set') + '</li>';
                html += '</ul>';
                
                document.getElementById('config-status').innerHTML = html;
            } catch (error) {
                document.getElementById('config-status').innerHTML = 
                    '<span class="status-error">❌ API not accessible: ' + error.message + '</span>';
            }
        }

        // Test frontend login
        function testFrontendLogin() {
            const result = document.getElementById('frontend-result');
            result.innerHTML = '<div class="alert alert-info">Opening frontend Google login...</div>';
            
            // Open in new window
            const popup = window.open('/auth/google', 'google-login', 'width=500,height=600');
            
            // Check if popup was blocked
            if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                result.innerHTML = '<div class="alert alert-warning">⚠️ Popup blocked. Please allow popups and try again, or use the manual link above.</div>';
            } else {
                result.innerHTML = '<div class="alert alert-success">✅ Popup opened. Complete the Google login in the popup window.</div>';
                
                // Monitor popup
                const checkClosed = setInterval(() => {
                    if (popup.closed) {
                        clearInterval(checkClosed);
                        result.innerHTML = '<div class="alert alert-info">Popup closed. Check if you were logged in successfully.</div>';
                    }
                }, 1000);
            }
        }

        // Test backend login
        function testBackendLogin() {
            const result = document.getElementById('backend-result');
            result.innerHTML = '<div class="alert alert-info">Opening backend Google login...</div>';
            
            // Open in new window
            const popup = window.open('/login/google', 'google-admin-login', 'width=500,height=600');
            
            // Check if popup was blocked
            if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                result.innerHTML = '<div class="alert alert-warning">⚠️ Popup blocked. Please allow popups and try again, or use the manual link above.</div>';
            } else {
                result.innerHTML = '<div class="alert alert-success">✅ Popup opened. Complete the Google login in the popup window.</div>';
                
                // Monitor popup
                const checkClosed = setInterval(() => {
                    if (popup.closed) {
                        clearInterval(checkClosed);
                        result.innerHTML = '<div class="alert alert-info">Popup closed. Check if you were logged in successfully.</div>';
                    }
                }, 1000);
            }
        }

        // Test API login
        async function testApiLogin() {
            const result = document.getElementById('api-result');
            result.innerHTML = '<div class="alert alert-info">Testing API login...</div>';
            
            const testData = {
                login_type: 'google',
                email: 'test.google.' + Date.now() + '@example.com',
                user_type: 'user',
                first_name: 'Test',
                last_name: 'Google'
            };
            
            try {
                const response = await fetch('/api/auth/social-login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(testData)
                });
                
                const data = await response.json();
                
                if (response.ok && data.status) {
                    result.innerHTML = `
                        <div class="alert alert-success">
                            <h6>✅ API Login Successful!</h6>
                            <p><strong>User ID:</strong> ${data.data?.id || 'N/A'}</p>
                            <p><strong>Email:</strong> ${data.data?.email || 'N/A'}</p>
                            <p><strong>User Type:</strong> ${data.data?.user_type || 'N/A'}</p>
                            <p><strong>Token:</strong> ${data.data?.api_token ? 'Generated' : 'Not found'}</p>
                            <div class="code-block">${JSON.stringify(data, null, 2)}</div>
                        </div>
                    `;
                } else {
                    result.innerHTML = `
                        <div class="alert alert-warning">
                            <h6>⚠️ API Response:</h6>
                            <p><strong>Status:</strong> ${response.status}</p>
                            <div class="code-block">${JSON.stringify(data, null, 2)}</div>
                        </div>
                    `;
                }
            } catch (error) {
                result.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>❌ API Error:</h6>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }

        // API form submission
        document.getElementById('api-test-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const result = document.getElementById('api-form-result');
            result.innerHTML = '<div class="alert alert-info">Testing API with custom data...</div>';
            
            const testData = {
                login_type: 'google',
                email: document.getElementById('api-email').value,
                user_type: document.getElementById('api-user-type').value,
                first_name: document.getElementById('api-first-name').value,
                last_name: document.getElementById('api-last-name').value
            };
            
            try {
                const response = await fetch('/api/auth/social-login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(testData)
                });
                
                const data = await response.json();
                
                result.innerHTML = `
                    <div class="alert ${response.ok && data.status ? 'alert-success' : 'alert-warning'}">
                        <h6>${response.ok && data.status ? '✅ Success' : '⚠️ Response'}:</h6>
                        <p><strong>Status:</strong> ${response.status}</p>
                        <div class="code-block">${JSON.stringify(data, null, 2)}</div>
                    </div>
                `;
            } catch (error) {
                result.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>❌ Error:</h6>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        });

        // Load debug information
        async function loadDebugInfo() {
            const debugDiv = document.getElementById('debug-info');
            debugDiv.innerHTML = '<div class="alert alert-info">Loading debug information...</div>';
            
            let html = '<h5>Debug Information:</h5>';
            
            // Current URL
            html += '<p><strong>Current URL:</strong> ' + window.location.href + '</p>';
            
            // User agent
            html += '<p><strong>User Agent:</strong> ' + navigator.userAgent + '</p>';
            
            // Check if we're logged in
            try {
                const response = await fetch('/api/user', {
                    headers: {
                        'Authorization': 'Bearer ' + getCookie('api_token')
                    }
                });
                
                if (response.ok) {
                    const user = await response.json();
                    html += '<p><strong>Logged in user:</strong> ' + user.email + ' (' + user.user_type + ')</p>';
                } else {
                    html += '<p><strong>Logged in user:</strong> <span class="status-warning">Not logged in</span></p>';
                }
            } catch (error) {
                html += '<p><strong>Logged in user:</strong> <span class="status-error">Could not check</span></p>';
            }
            
            // Cookies
            html += '<p><strong>Session Cookie:</strong> ' + (document.cookie.includes('laravel_session') ? '✅ Present' : '❌ Not found') + '</p>';
            html += '<p><strong>API Token Cookie:</strong> ' + (getCookie('api_token') ? '✅ Present' : '❌ Not found') + '</p>';
            
            debugDiv.innerHTML = html;
        }

        // Helper function to get cookie
        function getCookie(name) {
            const value = "; " + document.cookie;
            const parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkConfiguration();
        });
    </script>
</body>
</html>
