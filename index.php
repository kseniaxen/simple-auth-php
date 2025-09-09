<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $errors = [];

    if (empty($name)) {
        $errors[] = "Введите имя";
    }

    if (empty($login)) {
        $errors[] = "Введите логин";
    }

    if (empty($password)) {
        $errors[] = "Введите пароль";
    }

    if (!empty($name) && !preg_match('/^[a-zA-Zа-яА-ЯёЁ]+$/u', $name)) {
        $errors[] = "Имя пользователя содержит недопустимые символы!";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        header('Location: index.php');
        exit();
    }

    //преобразования специальных символов в HTML-сущности
    //ENT_QUOTES - флаг, указывающий какие кавычки преобразовывать: Преобразует и двойные (") и одинарные (') кавычки
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $login = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');

    try {
        //PDO - PHP Data Object
        //$database = new PDO('sqlite:database.sqlite');

        $host = 'localhost';
        $port = '5432';
        $dbname = 'test';
        $username = 'postgres';
        $password_db = '1452';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        $database = new PDO($dsn, $username, $password_db);

        //PDO::ATTR_ERRMODE - атрибут, который мы настраиваем (режим ошибок)
        //PDO::ERRMODE_EXCEPTION - значение, которое мы устанавливаем (режим исключений)
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        //Создаем таблицу если ее не существует (синтаксист Sqlite)
        /*$database->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                login TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");*/

        // Создаем таблицу если ее не существует (синтаксис PostgreSQL)
        $database->exec("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name TEXT NOT NULL,
                login TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $stmt = $database->prepare("SELECT id FROM users WHERE login = :login");
        $stmt->bindValue(':login', $login, PDO::PARAM_STR);
        //Выполняет подготовленный SQL-запрос с привязанными параметрами.
        $stmt->execute();

        //Извлекает следующую строку из результирующего набора.
        if ($stmt->fetch()) {
            $_SESSION['form_errors'] = ["Пользователь с таким логином уже существует."];
            header('Location: index.php');
            exit();
        }

        //Создает безопасный хеш пароля используя самый современный и рекомендуемый алгоритм
        $password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $database->prepare("
            INSERT INTO users (name, login, password) 
            VALUES (:name, :login, :password)
        ");

        //PDO::PARAM_STR - тип данных (строка)
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':login', $login, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $database->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_login'] = $login;
            header('Location: result.php');
            exit();
        } else {
            $_SESSION['form_errors'] = ["Ошибка при регистрации. Попробуйте еще раз."];
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['form_errors'] = ["Ошибка базы данных: " . $e->getMessage()];
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Регистрация</title>
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
                <h1>Форма регистрации</h1>
                <?php if (!empty($_SESSION['form_errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['form_errors']); ?>
                <?php endif; ?>

                <form method="POST" class="d-flex flex-column">
                    <div>
                        <div class="mb-2">
                            <label for="name">Ваше имя:</label>
                            <input type="text" id="name" name="name" placeholder="Введите имя" class="w-100 form-control" required>
                        </div>
                        <div class="mb-2">
                            <label for="login">Ваш логин:</label>
                            <input type="text" id="login" name="login" placeholder="Введите логин" class="w-100 form-control" required>
                        </div>
                        <div class="mb-2">
                            <label for="password">Ваш пароль:</label>
                            <input type="text" id="password" name="password" placeholder="Введите пароль" class="w-100 form-control" required>
                        </div>
                        <input type="submit" value="Отправить" class="w-100 btn btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>