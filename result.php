<?php
session_start();
unset($_SESSION['form_errors']);

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Пользователь';
$user_login = $_SESSION['user_login'] ?? '';


//unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_login']);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация завершена</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="d-flex flex-column">
                    <h1>Регистрация успешно завершена!</h1>
                    <p>Добро пожаловать, <?php echo $user_name; ?></p>
                    <p>Ваш логин: <?php echo $user_login; ?></p>
                    <div>
                        <a class="btn btn-primary" href="users.php">На главную</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>