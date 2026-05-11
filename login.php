<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once "connect.php";

if (isset($_POST["email"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    $sql = "SELECT id, fullname, email, password, role, status FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($row["status"] == 0) {
            $error_message = "Ваш аккаунт заблокирован.";
        } else {
            if (password_verify($password, $row["password"])) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["user_name"] = $row["fullname"];
                $_SESSION["role"] = $row["role"];
                
                // Обновляем время входа
                $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $row["id"]);
                $update_stmt->execute();

                // ИСПРАВЛЕНИЕ: Перенаправление в зависимости от роли
                if ($row["role"] == 1) {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error_message = "Неверный пароль.";
            }
        }
    } else {
        $error_message = "Пользователь не найден.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему | Авиабилеты</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- остальной код без изменений -->
    <?php include 'includes/header_auth.php'; ?>
    
    <div class="login-form">
        <?php if (isset($error_message)): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
            <div class="alert success">Регистрация успешна! Теперь вы можете войти в систему.</div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="email" class="form-label">Email адрес</label>
                <input type="email" class="form-control" id="email" name="email" required 
                       placeholder="Введите ваш email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" class="form-control" id="password" name="password" required 
                       placeholder="Введите ваш пароль">
                <label class="show-password-label">
                    <input type="checkbox" onclick="this.checked ? document.getElementById('password').type='text' : document.getElementById('password').type='password'"> 
                    Показать пароль
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Войти в систему</button>
            
            <div class="login-links">
                <p>Ещё нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
                <p><a href="index.php">← Вернуться на главную</a></p>
            </div>
        </form>
    </div>
    
    <?php include 'includes/footer_auth.php'; ?>
    <?php ob_end_flush(); ?>
</body>
</html>