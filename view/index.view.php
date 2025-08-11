<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>SoulBridge</title>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Helvetica, Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        /* Shared Styles */
        .logo {
            color: #5e4dcd;
            font-size: 3.5rem;
            font-weight: bold;
            letter-spacing: -0.5px;
            text-align: center;
            margin: 20px 0;
        }

        .card {
            width: 432px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1), 0 8px 16px rgba(0, 0, 0, .1);
            margin: 10px auto;
        }

        .form-control-custom {
            width: 100%;
            height: 40px;
            padding: 11px;
            border: 1px solid #dddfe2;
            border-radius: 5px;
            font-size: 15px;
            color: #1d2129;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .btn-primary-custom {
            background: #5e4dcd;
            color: white;
            border: none;
            font-weight: bold;
        }

        .btn-primary-custom:hover {
            background: #4a3cad;
        }

        .divider {
            border-bottom: 1px solid #dadde1;
            margin: 20px 0;
            position: relative;
        }

        .divider-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 10px;
            color: #1c1e21;
            font-size: 12px;
        }

        /* Login Specific */
        .login-alert {
            background: #5e4dcd;
            color: white;
            border-radius: 6px;
            padding: 10px;
            display: flex;
            align-items: center;
        }

        .login-input {
            height: 52px;
            font-size: 17px;
            padding: 14px 16px;
            border-radius: 6px;
        }

        /* Signup Specific */
        .form-title {
            font-size: 25px;
            text-align: center;
            margin: 10px 0 5px;
            color: #1c1e21;
        }

        .form-subtitle {
            text-align: center;
            color: #606770;
            font-size: 15px;
            margin-bottom: 20px;
        }

        .date-select-container {
            display: flex;
            gap: 10px;
        }

        .date-select-container select {
            flex: 1;
            height: 40px;
        }

        .gender-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .gender-option {
            border: 1px solid #dddfe2;
            border-radius: 5px;
            padding: 8px;
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .gender-option label {
            margin: 0;
            font-size: 15px;
            color: #1c1e21;
        }

        .policy-text {
            font-size: 11px;
            color: #777;
            line-height: 1.34;
            margin: 15px 0;
            text-align: center;
        }

        .policy-text a {
            color: #5e4dcd;
            text-decoration: none;
        }

        .switch-form {
            text-align: center;
            margin-top: 15px;
        }

        .switch-form a {
            color: #5e4dcd;
            text-decoration: none;
            font-weight: bold;
        }

        /* Toggle between forms */
        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 991px) {
            .card {
                width: 100%;
                max-width: 500px;
                padding: 10px;
            }
            .logo {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 0 5px !important;
            }
            .card {
                width: 100%;
                max-width: 100%;
                padding: 8px;
                margin: 5px auto;
            }
            .logo {
                font-size: 2rem;
                margin: 10px 0;
            }
            .form-title {
                font-size: 20px;
            }
            .form-subtitle {
                font-size: 13px;
            }
            .date-select-container,
            .gender-container {
                flex-direction: column;
                gap: 5px;
            }
            .gender-option {
                padding: 6px;
                font-size: 14px;
            }
            .form-control-custom,
            .login-input {
                font-size: 14px;
                height: 36px;
                padding: 8px;
            }
            .btn-primary-custom,
            .btn-success {
                font-size: 15px;
                padding: 8px 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container text-center py-4">
        <div class="logo">SoulBridge</div>

        <!-- Login Form -->
        <div id="login-form" class="form-container active">
            <div class="card">
                <div class="login-alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    You must log in to continue. 
                </div>

                <form class="mt-3" method="POST" action="/Auth.php">
                    <input type="email"
                           class="form-control-custom login-input"
                           name="email"
                           placeholder="Email address"
                           required>
                    
                    <input type="password" 
                           class="form-control-custom login-input" 
                           name="password"
                           placeholder="Password"
                           required>
                    
                    <button type="submit" class="btn btn-primary-custom w-100 py-2 mb-3" name="login">
                        Log in
                    </button>

                    <div class="divider">
                        <span class="divider-text">or</span>
                    </div>
                    
                    <button type="button" class="btn btn-success w-100 py-2" onclick="showSignupForm()">
                        Create new account
                    </button>
                </form>
            </div>
        </div>

        <!-- Signup Form -->
        <div id="signup-form" class="form-container">
            <div class="card">
                <h2 class="form-title">Create a new account</h2>
                <p class="form-subtitle">It's quick and easy.</p>

                <form action="/Auth.php" method="post">
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input type="text" class="form-control-custom" name="user_firstname" placeholder="First name" required>
                        <input type="text" class="form-control-custom" name="user_lastname" placeholder="Last name" required>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <div style="font-size: 12px; color: #606770; margin-bottom: 5px;">Date of birth</div>
                        <div class="date-select-container">
                            <select class="form-control-custom" name="selectmonth" required>
                                <option value="">Month</option>
                                <option value="1">Jan</option>
                                <option value="2">Feb</option>
                                <option value="3">Mar</option>
                                <option value="4">Apr</option>
                                <option value="5">May</option>
                                <option value="6">Jun</option>
                                <option value="7">Jul</option>
                                <option value="8">Aug</option>
                                <option value="9">Sep</option>
                                <option value="10">Oct</option>
                                <option value="11">Nov</option>
                                <option value="12">Dec</option>
                            </select>
                            <select class="form-control-custom" name="selectday" required>
                                <option value="">Day</option>
                                <?php for($i = 1; $i <= 31; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <select class="form-control-custom" name="selectyear" required>
                                <option value="">Year</option>
                                <?php for($i = date('Y'); $i >= 1900; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <div style="font-size: 12px; color: #606770; margin-bottom: 5px;">Gender</div>
                        <div class="gender-container">
                            <div class="gender-option">
                                <label>Female <input type="radio" name="gender" value="female" required></label>
                            </div>
                            <div class="gender-option">
                                <label>Male <input type="radio" name="gender" value="male" required></label>
                            </div>
                            <div class="gender-option">
                                <label>Other <input type="radio" name="gender" value="other" required></label>
                            </div>
                        </div>
                    </div>
                    
                    <input type="email" name="email" class="form-control-custom" placeholder="Mobile number or email" required>
                    <input type="password" name="password" class="form-control-custom" placeholder="New password" required>

                    <p class="policy-text">
                        People who use our service may have uploaded your contact information to SoulBridge. 
                        <a href="#">Learn more</a>.
                    </p>

                    <p class="policy-text">
                        By clicking Sign Up, you agree to our 
                        <a href="#">Terms</a>, 
                        <a href="#">Privacy Policy</a> and 
                        <a href="#">Cookies Policy</a>. 
                        You may receive SMS Notifications from us and can opt out any time.
                    </p>

                    <button type="submit" class="btn btn-primary-custom w-100 py-2" name="signup">Sign Up</button>

                    <div class="switch-form">
                        <a href="#" onclick="showLoginForm()">Already have an account?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showSignupForm() {
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('signup-form').classList.add('active');
        }

        function showLoginForm() {
            document.getElementById('signup-form').classList.remove('active');
            document.getElementById('login-form').classList.add('active');
        }
    </script>
</body>
</html>