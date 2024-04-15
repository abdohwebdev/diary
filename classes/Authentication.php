<?php
spl_autoload_register(function ($class_name) {
    include 'classes/'.$class_name . '.php';
});
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
        Validation::validateRequiredFields($requiredFields);

        // validating email address
        if (!isset(Validation::$errors["email"])) {
            Validation::validateEmailSyntax($email);
            Validation::checkEmailAvailability($email,$this->pdo);
        }

        // validating password
        if (!isset(Validation::$errors["password"])) {
            Validation::validatePassword($password);
        }

        if (!isset(Validation::$errors["password"])) {
            Validation::validatePasswordMatch($password, $passwordConfirm);
        }

        if (Validation::$errors == []) {
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
        Validation::validateRequiredFields($requiredFields);

        // validating email address
        if (!isset(Validation::$errors["email"])) {
            Validation::validateEmailSyntax($email);
        }

        // check login info
        if (Validation::$errors == []) {
            return Validation::checkLoginInfo($email,$password,$this->pdo);
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
}
