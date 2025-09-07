<?php
    session_start();

    try {
        $database = new PDO('sqlite:database.sqlite');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }

    $user_id = $_GET['id'] ?? null;

    if ($user_id) {
    $stmt = $database->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Пользователь успешно удален!";
    } else {
        $_SESSION['error_message'] = "Ошибка при удалении пользователя!";
    }
}
header('Location: users.php');
exit();
?>