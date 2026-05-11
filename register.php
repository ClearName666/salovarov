<?php
ob_start();
session_start();
include_once "connect.php";

$errors = [];

if (isset($_POST["fullname"])) {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $password_repeat = $_POST["password_repeat"];
    
    if (empty($fullname)) {
        $errors[] = "Введите полное имя";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите корректный email адрес";
    }
    
    if (empty($phone)) {
        $errors[] = "Введите номер телефона";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Пароль должен содержать минимум 6 символов";
    }
    
    if ($password !== $password_repeat) {
        $errors[] = "Пароли не совпадают";
    }
    
    if (empty($errors)) {
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        
        if ($check_stmt === false) {
            $errors[] = "Ошибка подготовки запроса: " . $conn->error;
        } else {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = "Пользователь с таким email уже существует";
            }
            $check_stmt->close();
            
            if (empty($errors)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 0;
                $status = 1;
                
                $sql = "INSERT INTO users (fullname, email, phone, password, role, status) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                if ($stmt === false) {
                    $errors[] = "Ошибка подготовки запроса: " . $conn->error;
                } else {
                    $stmt->bind_param("ssssii", $fullname, $email, $phone, $password_hash, $role, $status);
                    
                    if ($stmt->execute()) {
                        $new_user_id = $stmt->insert_id;
                        
                        $_SESSION["user_id"] = $new_user_id;
                        $_SESSION["user_name"] = $fullname;
                        $_SESSION["user_email"] = $email;
                        $_SESSION["role"] = $role;
                        
                        $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("i", $new_user_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                        
                        $stmt->close();
                        $conn->close();
                        
                        header("Location: index.php?registered=success");
                        exit();
                    } else {
                        $errors[] = "Ошибка при регистрации. Попробуйте позже.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | Авиабилеты</title>
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- остальной код без изменений -->
    <?php include 'includes/header_auth.php'; ?>
    
    <div class="register-form">
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="fullname" class="form-label">Полное имя *</label>
                <input type="text" class="form-control" id="fullname" name="fullname" required 
                       placeholder="Иванов Иван Иванович" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email адрес *</label>
                <input type="email" class="form-control" id="email" name="email" required 
                       placeholder="example@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label">Номер телефона *</label>
                <input type="tel" class="form-control" id="phone" name="phone" required 
                       placeholder="+7 (999) 123-45-67" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Пароль *</label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Минимум 6 символов">
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">Показать</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password_repeat" class="form-label">Повторите пароль *</label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="password_repeat" name="password_repeat" required 
                               placeholder="Повторите пароль">
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('password_repeat')">Показать</button>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            
            <div class="register-links">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
                <p><a href="index.php">← Вернуться на главную</a></p>
            </div>
        </form>
    </div>
    
    <?php include 'includes/footer_auth.php'; ?>
    
    <script>
        function togglePassword(inputId) {
            var input = document.getElementById(inputId);
            var button = input.nextElementSibling;
            
            if (input.type === "password") {
                input.type = "text";
                button.textContent = "Скрыть";
            } else {
                input.type = "password";
                button.textContent = "Показать";
            }
        }
    </script>
    <?php ob_end_flush(); ?>
</body>
</html>