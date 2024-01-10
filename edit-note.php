<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
spl_autoload_register(function ($class_name) {
    include 'classes/' . $class_name . '.php';
});
session_start();
if (!isset($_SESSION['userId'])) {
    header("location:index.php");
}

$noteId = $_GET['id'];
if (!$noteId) {
    header("location:dashboard.php");
}

$userId = $_SESSION['userId'];

$connection = new Connection();

if (isset($_POST['logout'])) {
    $authentication = new Authentication($connection->pdo);
    $authentication->logOut();
}

$note = new Note($connection->pdo, $userId);

$currentNote = $note->getNoteById($noteId);

if(!$currentNote){
    header('location:dashboard.php');
}

if (isset($_POST['save'])){
    $updatedNoteTitle = $_POST['title'];
    $updatedNoteBody = $_POST['body'];
    if($note->updateNote($noteId,$updatedNoteTitle,$updatedNoteBody)){
        header('location:dashboard.php');
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
                <form class="d-flex ms-auto" method="post"
                    action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <button type="submit" class="btn btn-danger" name="logout">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container main-content d-flex flex-column justify-content-center align-items-center">
        <form class="bg-light p-4 w-50" method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Note Title"
                    value="<?php echo htmlspecialchars($updatedNoteTitle ?? $currentNote['title']); ?>">
                <?php
                if (isset($_POST['save']) && isset(Validation::$errors['note title'])):
                    ?>
                    <div class="error-message">
                        <?php
                        foreach (Validation::$errors['note title'] as $error):
                            echo $error . "<br>";
                        endforeach;
                        ?>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            <div class="mb-3">
                <label for="body" class="form-label">Note Body</label>
                <textarea class="form-control" id="body" name="body" rows="9" placeholder="Write your diary note here .."><?php echo htmlspecialchars($updatedNoteBody ?? $currentNote['body']); ?></textarea>
                <?php
                if (isset($_POST['save']) && isset(Validation::$errors['note body'])):
                    ?>
                    <div class="error-message">
                        <?php
                        foreach (Validation::$errors['note body'] as $error):
                            echo $error . "<br>";
                        endforeach;
                        ?>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            <button type="submit" class="btn btn-primary w-100" name="save">Save</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>