<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="IE=edge" http-equiv="X-UA-Compatible"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Admin Login - Uttaranchal University</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="assets/css/font-awesome.min.css" rel="stylesheet"/>
    <link href="assets/images/favicon.ico" rel="icon" type="image/ico">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Metropolis', sans-serif;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #21337d 0%, #1e2a6b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .login-body {
            padding: 40px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #21337d;
            box-shadow: 0 0 0 3px rgba(33, 51, 125, 0.1);
        }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #21337d 0%, #1e2a6b 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 51, 125, 0.4);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fa fa-user-shield fa-2x mb-3"></i>
                <h2>Admin Login</h2>
                <p class="mb-0">Student Result Management System</p>
            </div>
            
            <div class="login-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i> Invalid credentials. Please try again.
                    </div>
                <?php endif; ?>
                
                <form action="admin-auth.php" method="POST">
                    <div class="form-group">
                        <label for="admin_id" class="form-label">
                            <i class="fa fa-user"></i> Admin ID
                        </label>
                        <input type="text" class="form-control" id="admin_id" name="admin_id" 
                               placeholder="Enter admin ID" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fa fa-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter password" required>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fa fa-sign-in"></i> Login to Admin Panel
                    </button>
                </form>
            </div>
        </div>
        
        <div class="back-link">
            <a href="index.html">
                <i class="fa fa-arrow-left"></i> Back to Website
            </a>
        </div>
    </div>
</body>
</html>