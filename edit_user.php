<?php
session_start();

try {
    //$database = new PDO('sqlite:database.sqlite');
    $host = 'localhost';
    $port = '5432';
    $dbname = 'test';
    $username = 'postgres';
    $password_db = '1452';

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    $database = new PDO($dsn, $username, $password_db);

    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header('Location: users.php');
    exit();
}

$stmt = $database->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Пользователь не найден!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $errors = [];

    if (empty($name)) {
        $errors[] = "Имя обязательно для заполнения";
    }

    if (empty($login)) {
        $errors[] = "Логин обязателен для заполнения";
    }

    $stmt = $database->prepare("SELECT id FROM users WHERE login = :login AND id != :id");
    $stmt->bindValue(':login', $login, PDO::PARAM_STR);
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->fetch()) {
        $errors[] = "Этот логин уже занят другим пользователем";
    }

    if (empty($errors)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $database->prepare("
                UPDATE users 
                SET name = :name, login = :login, password = :password 
                WHERE id = :id
            ");
            $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
        } else {
            $stmt = $database->prepare("
                UPDATE users 
                SET name = :name, login = :login 
                WHERE id = :id
            ");
        }

        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':login', $login, PDO::PARAM_STR);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Данные пользователя успешно обновлены!";
            header('Location: users.php');
            exit();
        } else {
            $errors[] = "Ошибка при обновлении данных";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Изменение пользователя</title>
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
                <h2>Изменение данных</h2>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-2">
                        <label for="name">Имя:</label>
                        <input type="text" id="name" name="name" class="w-100 form-control" required
                            value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>

                    <div class="mb-2">
                        <label for="login">Логин:</label>
                        <input type="text" id="login" name="login" class="w-100 form-control" required
                            value="<?php echo htmlspecialchars($user['login']); ?>">
                    </div>

                    <div class="mb-2">
                        <label for="password">Новый пароль:</label>
                        <input type="password" id="password" class="w-100 form-control" name="password"
                            placeholder="Оставьте пустым, если не нужно менять">
                    </div>

                    <div>
                        <button class="btn btn-primary" type="submit">Сохранить изменения</button>
                        <a class="btn btn-danger" href="users.php">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>