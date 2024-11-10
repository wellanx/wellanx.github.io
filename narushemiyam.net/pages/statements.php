<?php
session_start(); // Начало сессии для отслеживания авторизации

// Проверка выхода из учетной записи
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "narusheniyam_net";

$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Проверка, если пользователь авторизован, для загрузки его ID
$user_id = null;
if (isset($_SESSION['username'])) {
    $current_username = $_SESSION['username'];
    $user_query = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $user_query->bind_param("s", $current_username);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user_id = $user_result->fetch_assoc()['id'];
}

// Обработка формы добавления нового заявления, если пользователь авторизован
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_statement']) && $user_id) {
    $car_registration = $_POST['car_registration'];
    $violation_description = $_POST['violation_description'];

    $insert_statement = $conn->prepare("INSERT INTO statements (user_id, car_registration, violation_description) VALUES (?, ?, ?)");
    $insert_statement->bind_param("iss", $user_id, $car_registration, $violation_description);
    $insert_statement->execute();
    $insert_statement->close();

    header("Location: statements.php");
    exit();
}

// Получение заявлений текущего пользователя
$statements_query = $conn->prepare("SELECT car_registration, violation_description, status, created_at FROM statements WHERE user_id = ? ORDER BY created_at DESC");
$statements_query->bind_param("i", $user_id);
$statements_query->execute();
$statements_result = $statements_query->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://necolas.github.io/normalize.css/8.0.1/normalize.css" />
    <title>Нарушениям.Нет</title>
  </head>
  <body>
    <header>
      <div class="logo"><h1>Нарушениям.Нет</h1></div>
      <div class="nav-bar">
        <?php if (isset($_SESSION['username'])): ?>
          <a href="statements.php">Заявления</a>
          <a href="admin.php">Админ</a>
          <a href="?logout=true">Выход</a>
        <?php else: ?>
          <a href="login.php">Вход</a>
          <a href="statements.php">Заявления</a>
          <a href="admin.php">Админ</a>
        <?php endif; ?>
      </div>
    </header>

    <main>
      <div class="container">
        <h1>Мои Заявления</h1>

        <!-- Список заявлений пользователя -->
        <div class="table-container">
          <?php if ($statements_result->num_rows > 0): ?>
            <table>
              <thead>
                <tr>
                  <th>Регистрационный номер</th>
                  <th>Описание нарушения</th>
                  <th>Статус</th>
                  <th>Дата создания</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $statements_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['car_registration']); ?></td>
                    <td><?php echo htmlspecialchars($row['violation_description']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>У вас нет заявлений.</p>
          <?php endif; ?>
        </div>

        <!-- Форма для добавления нового заявления - доступна только авторизованным пользователям -->
        <?php if ($user_id): ?>
          <div class="act-form">
            <h2>Оставить новое заявление</h2>
            <form action="statements.php" method="post">
              <input type="text" name="car_registration" placeholder="Гос. номер автомобиля" required />
              <textarea name="violation_description" placeholder="Описание нарушения" required></textarea>
              <input type="hidden" name="new_statement" value="1" />
              <button type="submit">Отправить заявление</button>
            </form>
          </div>
        <?php else: ?>
          <p>Пожалуйста, <a href="login.php">войдите в систему</a>, чтобы оставить новое заявление.</p>
        <?php endif; ?>
      </div>
    </main>
  </body>
</html>
