<?php
class Validation
{
    static array $errors = [];
    public static function validateRequiredFields(array $requiredFields)
    {
        foreach ($requiredFields as $name => $value) {
            if (empty($value)) {
                self::addError($name, "{$name} is required");
            }
        }
    }

    public static function validateEmailSyntax(string $email)
    {
        filter_var($email, FILTER_VALIDATE_EMAIL) ?: self::addError("email", "email is not valid");
    }

    static function checkEmailAvailability(string $email, PDO $pdo)
    {
        if (!isset(self::$errors["email"])) {
            $statement = $pdo->prepare("SELECT * FROM users WHERE email=:email");
            $statement->bindValue(":email", $email);
            $statement->execute();
            if ($statement->fetch()) {
                self::addError('email', 'this email is already used');
            }
        }
    }

    static function validatePassword(string $password)
    {
        if (strlen($password) < 8) {
            self::addError('password', 'password should be more than 8 chars');
        }

        if (!preg_match('/[0-9]/', $password)) {
            self::addError('password', 'password should include numbers');
        }

        if (!preg_match('/[a-z]/', $password)) {
            self::addError('password', 'password should include small letters');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            self::addError('password', 'password should include capital letters');
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            self::addError('password', 'password should include symbols');
        }
    }

    static function validatePasswordMatch(string $password, string $passwordConfirm)
    {
        if ($password != $passwordConfirm) {
            self::addError('password confirmation', 'the two passwords should match');
        }
    }

    static function checkLoginInfo(string $email, string $password, PDO $pdo)
    {
        $statement = $pdo->prepare("SELECT * FROM users WHERE email=:email LIMIT 1");
        $statement->bindValue(":email", $email);
        $statement->execute();
        $user = $statement->fetch();
        if ($user) {
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['userId'] = $user['id'];
                session_write_close();
                return true;
            } else {
                self::addError('general', 'wrong email or password');
                return false;
            }
        } else {
            self::addError('general', 'wrong email or password');
            return false;
        }
    }

    static function validateNoteTitle(string $title)
    {
        if (strlen($title) > 40) {
            self::addError('note title','note title should not exceed 40 chars');
        }
    }
    static function addError(string $name, string $message)
    {
        self::$errors[$name][] = $message;
    }
}