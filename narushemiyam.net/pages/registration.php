<?php
// Обработка формы регистрации
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

    // Получение данных из формы
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Проверка на существование пользователя с тем же email или username
    $check_sql = "SELECT * FROM users WHERE email = '$email' OR username = '$user'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // Если пользователь уже существует, показываем уведомление
        $error_message = "Пользователь с таким логином или email уже зарегистрирован.";
    } else {
        // Хеширование пароля для безопасности
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        // Подготовка и выполнение SQL-запроса
        $sql = "INSERT INTO users (fullname, phone, email, username, password)
                VALUES ('$fullname', '$phone', '$email', '$user', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            // Уведомление об успешной регистрации
            $success_message = "Регистрация прошла успешно! Вы можете <a href='login.php'>войти в систему</a>.";
        } else {
            $error_message = "Ошибка регистрации: " . $conn->error;
        }
    }

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
    <title>Регистрация</title>
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
        <h1>Регистрация</h1>
        <h2>Уже есть аккаунт? <a href="login.php">Войти</a></h2>

        <?php if (isset($error_message)): ?>
          <div class="error-message">
            <?php echo $error_message; ?>
          </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
          <div class="success-message">
            <?php echo $success_message; ?>
          </div>
        <?php else: ?>
          <form class="act-form" action="registration.php" method="post">
            <input
              type="text"
              id="fullname"
              name="fullname"
              placeholder="ФИО"
              required
            />
            <input
              type="tel"
              id="phone"
              name="phone"
              placeholder="Телефон"
              required
            />
            <input
              type="email"
              id="email"
              name="email"
              placeholder="Адрес электронной почты"
              required
            />
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
            <button type="submit">Зарегистрироваться</button>
          </form>
        <?php endif; ?>
      </div>
    </main>
  </body>
</html>
