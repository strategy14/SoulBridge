<?php
require_once 'Core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // LOGIN
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $user = $queryBuilder->login($email, $password);
        if ($user) {
            $_SESSION['userData'] = $queryBuilder->getUserData($user['id']);
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['csrf_token'] = SessionManager::generateCSRFToken();
            header("Location: /home");
            exit();
        } else {
            echo "<script>alert('Invalid email or password.'); window.location.href = '/';</script>";
        }
    }

    // REGISTER
    if (isset($_POST['signup'])) {
        $fname    = $_POST['user_firstname'];
        $lname    = $_POST['user_lastname'];
        $birthday = $_POST['selectyear'] . '-' . $_POST['selectmonth'] . '-' . $_POST['selectday'];
        $gender   = $_POST['gender'];
        $email    = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $existing = $queryBuilder->select('users', '*', 'email = :email', ['email' => $email]);
        if ($existing && count($existing) > 0) {
            echo "<script>alert('Your email already exists.');</script>";
        } else {
            $result = $queryBuilder->insert('users', [
                'firstName' => $fname,
                'lastName'  => $lname,
                'birthdate' => $birthday,
                'gender'    => $gender,
                'email'     => $email,
                'password'  => $password
            ]);
            if ($result) {
                $user = $queryBuilder->select('users', 'id', 'email = :email', ['email' => $email]);
                $_SESSION['user_id'] = $user[0]['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['csrf_token'] = SessionManager::generateCSRFToken();
                echo "<script>alert('Signup successful! Redirecting to home page.'); window.location.href = '/home';</script>";
            } else {
                echo "<script>alert('Error during signup.');</script>";
            }
        }
    }
}