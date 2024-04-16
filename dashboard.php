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

$userId = $_SESSION['userId'];

$connection = new Connection();

if (isset($_POST['logout'])) {
    $authentication = new Authentication($connection->pdo);
    $authentication->logOut();
}

$noteObject = new Note($connection->pdo, $userId);

// Sort
$_SESSION['sortBy'] = $_POST['sortBy'] ?? $_SESSION['sortBy'] ?? '';
$_SESSION['sortType'] = $_POST['sortType'] ?? $_SESSION['sortType'] ?? '';

$sortBy = $_SESSION['sortBy'];
$sortType = $_SESSION['sortType'];

// Pagination 
$notesCount = $noteObject->countNotesNumber();

$_SESSION['itemsPerPage'] = $_POST['itemsPerPage'] ?? $_SESSION['itemsPerPage'] ?? 5;

$itemsPerPage = $_SESSION['itemsPerPage'];

$numberOfPages = ceil($notesCount / $itemsPerPage);

$page = $_GET['page'] ?? 1;

// filtering 
$_SESSION['fromDate'] = $_POST['fromDate'] ?? $_SESSION['fromDate'] ?? '2024-01-01';
$_SESSION['toDate'] = $_POST['toDate'] ?? $_SESSION['toDate'] ?? date('Y-m-d', strtotime('+1 day')); // add 1 day to get this day notes also

$fromDate = $_SESSION['fromDate'];
$toDate = $_SESSION['toDate'];

$notes = $noteObject->getNotes($itemsPerPage, $page, $sortBy, $sortType, $fromDate, $toDate);

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
        <div class="d-flex align-items-center mb-3">
            <form id="paginationForm" action="" class="utility me-4" method="post">
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
            <form id="sortForm" action="" class="utility me-4" method="post">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <label for="sortBy" class="text-light">Sort By</label>
                        <select class="form-select" name="sortBy" id="sortBy">
                            <?php
                            $sortOptions = ['Create Date' => 'created_at', 'Update Date' => 'updated_at', 'Title' => 'title'];
                            foreach ($sortOptions as $key => $value):
                                $selected = ($value == $sortBy) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $value; ?>" <?= $selected; ?>>
                                    <?php echo $key; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="sortType" class="text-light">Sort Type</label>
                        <select class="form-select" name="sortType" id="sortType">
                            <?php
                            $sortTypes = ['ASC', 'DESC'];
                            foreach ($sortTypes as $value):
                                $selected = ($value == $sortType) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $value; ?>" <?= $selected; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
            <form id="filterForm" action="" class="utility me-4" method="post">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <label for="fromDate" class="text-light">From</label>
                        <input class="form-control" type="date" name="fromDate" value="<?php echo $fromDate; ?>">
                    </div>
                    <div class="me-4">
                        <label for="toDate" class="text-light">To</label>
                        <input class="form-control" type="date" name="toDate" value=<?php echo $toDate; ?>>
                    </div>
                    <div class="align-self-end">
                        <input class="btn btn-success" type="submit" name="filter" value="Filter">
                    </div>
                </div>
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
                    <?php $previousDisabled = ($page <= 1) ? 'disabled' : ''; ?>
                    <li class="page-item <?php echo $previousDisabled; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($pageNumber = 1; $pageNumber <= $numberOfPages; $pageNumber++): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $pageNumber; ?>">
                                <?php echo $pageNumber ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <?php $nextDisabled = ($page >= $numberOfPages) ? 'disabled' : ''; ?>
                    <li class="page-item <?php echo $nextDisabled; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
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
        submitOnChange([itemsPerPage], paginationForm);

        let sortBy = document.querySelector('#sortBy');
        let sortType = document.querySelector('#sortType');
        let sortForm = document.querySelector('#sortForm');
        submitOnChange([sortBy, sortType], sortForm);

        function submitOnChange(fields, form) {
            fields.forEach(field => {
                field.addEventListener('change', function () {
                    form.submit();
                });
            });
        }

    </script>
</body>

</html>