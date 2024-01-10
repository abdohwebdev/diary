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

if (isset($_POST['delete'])) {
    $note = new Note($connection->pdo, $userId);
    $note->deleteNote($noteId);
}

$note = new Note($connection->pdo, $userId);

$note = $note->getNoteById($noteId);

if (!$note) {
    header('location:dashboard.php');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <div class="ms-auto d-flex align-items-center">
                    <a href="add-note.php" class="btn btn-success me-2">Add Note</a>
                    <form class="d-flex" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" class="btn btn-danger" name="logout">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="bg-white w-50 mx-auto p-3 my-4">
            <h3 class="text-center">
                <?php
                if ($note['title']) {
                    echo htmlspecialchars($note['title']);
                } else {
                    echo htmlspecialchars($note['created_at']);
                }
                ?>
            </h3>
            <p class>
                <?php echo nl2br(htmlspecialchars($note['body'])); ?>
            </p>
            <button class="btn btn-danger" id="deleteButton">Delete Note</button>
            <div class="alert alert-danger mt-3" id="deleteAlert">
                <p>This will delete the note permanently , are you sure ?</p>
                <form class="d-flex justify-content-between" method="post">
                    <button type="submit" class="btn btn-danger" name="delete">Delete</button>
                    <button type="button" class="btn btn-secondary" id="cancelButton">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <script>
        let deleteButton = document.querySelector('#deleteButton');
        let deleteAlert = document.querySelector('#deleteAlert');
        let cancelButton = document.querySelector('#cancelButton');

        deleteButton.addEventListener('click', function () {
            deleteAlert.style.display = 'block';
        });

        cancelButton.addEventListener('click',function(){
            deleteAlert.style.display = 'none';
        });
    </script>
</body>

</html>