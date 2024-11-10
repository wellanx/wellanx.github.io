<?php
session_start();

// Проверка авторизации администратора
if (isset($_POST['login']) && isset($_POST['password'])) {
    $admin_login = "sorr";
    $admin_password = "password";

    if ($_POST['login'] === $admin_login && $_POST['password'] === $admin_password) {
        $_SESSION['admin'] = true;
    } else {
        $error = "Неверный логин или пароль!";
    }
}

// Проверка, что администратор авторизован
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
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
        <title>Панель Администратора</title>
      </head>
      <body>
        <header>
          <div class="logo"><h1>Нарушениям.Нет</h1></div>
        </header>

        <main>
          <div class="container">
            <h1>Вход в панель администратора</h1>
            <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
            <form class="act-form" action="admin.php" method="post">
              <input type="text" name="login" placeholder="Логин" required />
              <input type="password" name="password" placeholder="Пароль" required />
              <button type="submit">Войти</button>
            </form>
          </div>
        </main>
      </body>
    </html>
<?php
    exit();
}

// Подключение к базе данных для отображения заявлений
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "narusheniyam_net";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обработка изменения статуса
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_status'])) {
    $statement_id = $_POST['statement_id'];
    $new_status = $_POST['status'];
    $update_query = $conn->prepare("UPDATE statements SET status = ? WHERE id = ?");
    $update_query->bind_param("si", $new_status, $statement_id);
    $update_query->execute();
    $update_query->close();
}

// Получение всех заявлений
$statements_query = $conn->query("SELECT statements.id, users.fullname, statements.car_registration, statements.violation_description, statements.status 
                                  FROM statements 
                                  JOIN users ON statements.user_id = users.id
                                  ORDER BY statements.created_at DESC");

$conn->close();
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
    <title>Панель Администратора</title>
  </head>
  <body>
    <header>
      <div class="logo"><h1>Панель Администратора</h1></div>
      <div class="nav-bar">
        <a href="statements.php">Вернуться</a>
      </div>
    </header>

    <main>
      <div class="container">
        <h1>Все Заявления</h1>
        <div class="table-container">
          <?php if ($statements_query->num_rows > 0): ?>
            <table>
              <thead>
                <tr>
                  <th>ФИО</th>
                  <th>Рег. номер</th>
                  <th>Описание нарушения</th>
                  <th>Статус</th>
                  <th>Изменить статус</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $statements_query->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['car_registration']); ?></td>
                    <td><?php echo htmlspecialchars($row['violation_description']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                      <form action="admin.php" method="post">
                        <input type="hidden" name="statement_id" value="<?php echo $row['id']; ?>" />
                        <select name="status">
                          <option value="Ожидает рассмотрения" <?php if ($row['status'] == 'Ожидает рассмотрения') echo 'selected'; ?>>Ожидает рассмотрения</option>
                          <option value="Принято">Принято</option>
                          <option value="Отклонено">Отклонено</option>
                          <option value="Рассматривается">Рассматривается</option>
                          <option value="Завершено">Завершено</option>
                        </select>
                        <button type="submit" name="update_status">Обновить</button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>Нет заявлений для отображения.</p>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </body>
</html>
