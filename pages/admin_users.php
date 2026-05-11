<?php
include_once "connect.php";

// 1. Логика изменения статуса (Блокировка/Активация)
if (isset($_GET['toggle_status'])) {
    $uid = intval($_GET['toggle_status']);
    // Защита: нельзя заблокировать самого себя
    if ($uid != $_SESSION['user_id']) {
        $conn->query("UPDATE users SET status = 1 - status WHERE id = $uid");
        header("Location: admin.php?page=users&msg=status_updated");
        exit();
    }
}

// 2. Логика удаления пользователя
if (isset($_GET['delete_user'])) {
    $uid = intval($_GET['delete_user']);
    if ($uid != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $uid");
        header("Location: admin.php?page=users&msg=user_deleted");
        exit();
    }
}

// Получаем всех пользователей
$users_query = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<div class="admin-header">
    <div>
        <h1><i class="fas fa-user-shield"></i> Управление доступом</h1>
        <p>Настройка прав пользователей и блокировка аккаунтов</p>
    </div>
</div>

<div class="card animate-fade">
    <div class="table-res">
        <table>
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Роль</th>
                    <th>Регистрация</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users_query && $users_query->num_rows > 0): ?>
                    <?php while($u = $users_query->fetch_assoc()): ?>
                    <tr style="<?= $u['status'] == 0 ? 'background: #fff1f2;' : '' ?>">
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 35px; height: 35px; border-radius: 50%; background: #4f46e5; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    <?= mb_substr($u['fullname'], 0, 1) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($u['fullname']) ?></div>
                                    <small style="color: #64748b;"><?= htmlspecialchars($u['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if($u['role'] == 1): ?>
                                <span class="badge" style="background: #eef2ff; color: #4f46e5;">Админ</span>
                            <?php else: ?>
                                <span class="badge" style="background: #f1f5f9; color: #64748b;">Клиент</span>
                            <?php endif; ?>
                        </td>
                        <td><small><?= date('d.m.Y', strtotime($u['created_at'])) ?></small></td>
                        <td>
                            <?php if($u['status'] == 1): ?>
                                <span class="badge b-confirmed">Активен</span>
                            <?php else: ?>
                                <span class="badge b-cancelled">Забанен</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="admin.php?page=users&toggle_status=<?= $u['id'] ?>" class="btn" style="padding: 5px 10px; background: #f1f5f9;">
                                        <i class="fas <?= $u['status'] == 1 ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                    </a>
                                    <a href="admin.php?page=users&delete_user=<?= $u['id'] ?>" class="btn" style="padding: 5px 10px; background: #fee2e2; color: #ef4444;" onclick="return confirm('Удалить пользователя навсегда?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <small style="color: #94a3b8;">(Это вы)</small>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">Пользователей не найдено</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>