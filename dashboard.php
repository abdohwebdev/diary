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

if (isset($_POST['logout'])) {
    $connection = new Connection();
    $authentication = new Authentication($connection->pdo);
    $authentication->logOut();
}

$userId = $_SESSION['userId'];

$connection = new Connection();
$noteObject = new Note($connection->pdo, $userId);


$notesCount = $noteObject->countNotesNumber();

$_SESSION['itemsPerPage'] = $_POST['itemsPerPage'] ?? $_SESSION['itemsPerPage'] ?? 5;
$itemsPerPage = $_SESSION['itemsPerPage'];

$numberOfPages = ceil($notesCount / $itemsPerPage);

$page = $_GET['page'] ?? 1;

$notes = $noteObject->getNotes($itemsPerPage, $page);

if ($notes == [] && $page != 1) {
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
        <h3 class="text-center mt-3 text-light">My Diary</h3>
        <div class="d-flex align-items-center">
            <form id="paginationForm" action="" class="utility" method="post">
                <label for="itemsPerPage" class="text-light">Items Per Page</label>
                <select class="form-select" name="itemsPerPage" id="itemsPerPage">
                    <?php
                    $paginationOptions = ['5', '10', '15', '25', '50'];
                    foreach ($paginationOptions as $value):
                        $selected = ($value == $itemsPerPage) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $value; ?>" <?= $selected; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        <?php if ($notes): ?>
            <div class="row my-3">
                <?php foreach ($notes as $note): ?>
                    <div class="col-lg-3 mb-3">
                        <div class="card h-100">
                            <img src="/img/field2.jpg" class="card-img-top" alt="...">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <?php
                                    if ($note['title']) {
                                        echo htmlspecialchars($note['title']);
                                    } else {
                                        echo htmlspecialchars($note['created_at']);
                                    }
                                    ?>
                                </h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($noteObject->genrateExcerpt($note['body'])); ?>
                                </p>
                                <div class="d-flex justify-content-between mt-auto">
                                    <a href="view-note.php?id=<?php echo $note['id']; ?>" class="btn btn-primary">View Note</a>
                                    <a href="edit-note.php?id=<?php echo $note['id']; ?>" class="btn btn-secondary">Edit
                                        Note</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <nav aria-label="...">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link">Previous</a>
                    </li>
                    <?php for ($pageNumber = 1; $pageNumber <= $numberOfPages; $pageNumber++): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $pageNumber; ?>">
                                <?php echo $pageNumber ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        <?php else: ?>
            <div class="alert alert-info text-center my-3">
                You have no notes <a href="add-note.php">create one</a>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <script>
        let itemsPerPage = document.querySelector('#itemsPerPage');
        let paginationForm = document.querySelector('#paginationForm');
        itemsPerPage.addEventListener('change', function () {
            paginationForm.submit();
        });
    </script>
</body>

</html>