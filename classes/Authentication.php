<?php
class Authentication
{
    public PDO $pdo;
    public array $errors = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Sign up method
     * email and password fields are required
     * email should be valid
     * password should be more than or equal 8 chars
     * password should be mix of small letters, capital letters , numbers and symbols
     * the two passwords should be the same
     * 
     * email shouldn't be coonected with other account 
     * @param string $email
     * @param string $password
     * @param string $passwordConfirm
     * 
     */

    public function signUp(string $email, string $password, string $passwordConfirm)
    {
        // validating required fields
        $requiredFields = ["email" => $email, "password" => $password];
        $this->validateRequiredFields($requiredFields);

        // validating email address
        if (!isset($this->errors["email"])) {
            $this->validateEmailSyntax($email);
            $this->checkEmailAvailability($email);
        }

        // validating password
        if (!isset($this->errors["password"])) {
            $this->validatePassword($password);
        }

        if (!isset($this->errors["password"])) {
            $this->validatePasswordMatch($password, $passwordConfirm);
        }

        if ($this->errors == []) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $statement = $this->pdo->prepare("INSERT INTO users (email,password) Value(:email,:password)");
            $statement->bindValue(":email", $email);
            $statement->bindValue(":password", $hashedPassword);

            if ($statement->execute()) {
                session_start();
                $_SESSION['userId'] = $this->pdo->lastInsertId();
                header("location:dashboard.php");
            }
        } else {
            return false;
        }
    }

    /**
     * Login method
     * email and password fields are required
     * email should be valid
     * 
     * the email and password should be correct
     * @param string $email
     * @param string $password
     * 
     */
    public function logIn(string $email, string $password)
    {
        // validating required fields
        $requiredFields = ["email" => $email, "password" => $password];
        $this->validateRequiredFields($requiredFields);

        // validating email address
        if (!isset($this->errors["email"])) {
            $this->validateEmailSyntax($email);
        }

        // check login info
        if ($this->errors == []) {
            return $this->checkLoginInfo($email, $password);
        }else{
            return false;
        }
    }

    /**
     * logout method
     */
    public function logOut()
    {
        session_start();
        session_destroy();
        header("location:index.php");
    }

    private function validateRequiredFields(array $requiredFields)
    {
        foreach ($requiredFields as $name => $value) {
            if (empty($value)) {
                $this->errors[$name][] = "{$name} is required";
            }
        }
    }

    private function validateEmailSyntax(string $email)
    {
        filter_var($email, FILTER_VALIDATE_EMAIL) ?: $this->errors["email"][] = "email is not valid";
    }
    private function checkEmailAvailability(string $email)
    {
        if (!isset($this->errors["email"])) {
            $statement = $this->pdo->prepare("SELECT * FROM users WHERE email=:email");
            $statement->bindValue(":email", $email);
            $statement->execute();
            if ($statement->fetch()) {
                $this->errors['email'][] = "this email is already used";
            }
        }
    }

    private function validatePassword(string $password)
    {
        if (strlen($password) < 8) {
            $this->errors["password"][] = "password should be more than 8 chars";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $this->errors["password"][] = "password should include numbers";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $this->errors["password"][] = "password should include small letters";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors["password"][] = "password should include capital letters";
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $this->errors["password"][] = "password should include symbols";
        }
    }

    private function validatePasswordMatch(string $password, string $passwordConfirm)
    {
        if ($password != $passwordConfirm) {
            $this->errors["password confirmation"][] = "the two passwords should match";
        }
    }

    private function checkLoginInfo(string $email, string $password)
    {
        $statement = $this->pdo->prepare("SELECT * FROM users WHERE email=:email LIMIT 1");
        $statement->bindValue(":email", $email);
        $statement->execute();
        $user = $statement->fetch();
        if($user){
            if(password_verify($password,$user['password'])){
                session_start();
                $_SESSION['userId'] = $user['id'];
                session_write_close();
                return true;
            }else{
                $this->errors['general'][] = "wrong email or password";
                return false;
            }
        }else{
            $this->errors['general'][] = "wrong email or password";
            return false;
        }
    }
}
