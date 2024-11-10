<?php
session_start(); // Начало сессии для хранения данных о пользователе

// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Подключение к базе данных
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "narusheniyam_net";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Проверка соединения
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Получение данных из формы авторизации
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Подготовка SQL-запроса для поиска пользователя
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    // Проверка, существует ли пользователь
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Проверка пароля
        if (password_verify($pass, $row['password'])) {
            // Если пароль верен, сохраняем информацию о пользователе в сессии
            $_SESSION['username'] = $row['username'];
            $_SESSION['fullname'] = $row['fullname'];

            // Перенаправление на защищенную страницу после успешной авторизации
            header("Location: statements.php"); // Укажите путь к защищенной странице
            exit();
        } else {
            // Неверный логин или пароль
            $error = "Неправильный логин или пароль!";
        }
    } else {
        // Пользователь не найден
        $error = "Неправильный логин или пароль!";
    }

    // Закрытие соединения
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/style.css" />
    <link
      rel="stylesheet"
      href="https://necolas.github.io/normalize.css/8.0.1/normalize.css"
    />
    <title>Авторизация</title>
  </head>
  <body>
    <header>
      <div class="logo"><h1>Нарушениям.Нет</h1></div>
      <div class="nav-bar">
        <a href="login.php">Вход</a>
        <a href="statements.php">Заявления</a>
        <a href="admin.php">Админ</a>
      </div>
    </header>
    <main>
      <div class="container">
        <h1>Авторизация</h1>
        <h2>Нет аккаунта? <a href="registration.php">Регистрация</a></h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form class="act-form" action="login.php" method="post">
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Логин"
            required
          />
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Пароль"
            required
          />
          <button type="submit">Войти</button>
        </form>
      </div>
    </main>
  </body>
</html>
