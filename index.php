<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

spl_autoload_register(function ($class_name) {
    include 'classes/'.$class_name . '.php';
});

if (isset($_POST['signUp'])) {
    $email = $_POST['signUpEmail'];
    $password = $_POST['signUpPassword'];
    $passwordConfirm = $_POST['passwordConfirm'];

    $connection = new Connection();

    $authentication = new Authentication($connection->pdo);

    $authentication->signUp($email, $password, $passwordConfirm);

}

if (isset($_POST['logIn'])) {
    $email = $_POST['logInEmail'];
    $password = $_POST['logInPassword'];

    $connection = new Connection();

    $authentication = new Authentication($connection->pdo);

    $loginStatus = $authentication->logIn($email, $password);

    if ($loginStatus) {
        header("location:dashboard.php");
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secret Diary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">Diary</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="d-flex flex-column ms-auto">
                    <form class="d-flex align-items-start" method="post" action="index.php">
                        <div class="d-flex flex-column me-2">
                            <input class="form-control" type="email" name="logInEmail" placeholder="Email Address" value="<?php echo htmlspecialchars($_POST['logInEmail'] ?? ""); ?>">
                            <?php
                            if (isset($_POST['logIn']) && isset(Validation::$errors['email'])):
                                ?>
                                <div class="error-message">
                                    <?php
                                    foreach (Validation::$errors['email'] as $error):
                                        echo $error . "<br>";
                                    endforeach;
                                    ?>
                                </div>
                                <?php
                            endif;
                            ?>
                        </div>
                        <div class="d-flex flex-column me-2">
                            <input class="form-control" type="password" name="logInPassword" placeholder="Password">
                            <?php
                            if (isset($_POST['logIn']) && isset(Validation::$errors['password'])):
                                ?>
                                <div class="error-message">
                                    <?php
                                    foreach (Validation::$errors['password'] as $error):
                                        echo $error . "<br>";
                                    endforeach;
                                    ?>
                                </div>
                                <?php
                            endif;
                            ?>
                        </div>
                        <button type="submit" class="btn btn-primary" name="logIn">Login</button>
                    </form>
                    <?php
                    if (isset($_POST['logIn']) && isset(Validation::$errors['general'])):
                        ?>
                        <div class="error-message">
                            <?php
                            foreach (Validation::$errors['general'] as $error):
                                echo $error . "<br>";
                            endforeach;
                            ?>
                        </div>
                        <?php
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-content d-flex flex-column justify-content-center align-items-center">
        <form class="bg-light p-4" method="post" action="index.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="signUpEmail" placeholder="Email Address"
                    value="<?php echo htmlspecialchars($_POST['signUpEmail'] ?? ""); ?>">
                <?php
                if (isset($_POST['signUp']) && isset(Validation::$errors['email'])):
                    ?>
                    <div class="error-message">
                        <?php
                        foreach (Validation::$errors['email'] as $error):
                            echo $error . "<br>";
                        endforeach;
                        ?>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="signUpPassword" placeholder="Password">
                <?php
                if (isset($_POST['signUp']) && isset(Validation::$errors['password'])):
                    ?>
                    <div class="error-message">
                        <?php
                        foreach (Validation::$errors['password'] as $error):
                            echo $error . "<br>";
                        endforeach;
                        ?>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            <div class="mb-3">
                <label for="password2" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password2" name="passwordConfirm"
                    placeholder="Retype password">
                <?php
                if (isset($_POST['signUp']) && isset(Validation::$errors['password confirmation'])):
                    ?>
                    <div class="error-message">
                        <?php
                        foreach (Validation::$errors['password confirmation'] as $error):
                            echo $error . "<br>";
                        endforeach;
                        ?>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            <button type="submit" class="btn btn-primary w-100" name="signUp">Sign up</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>