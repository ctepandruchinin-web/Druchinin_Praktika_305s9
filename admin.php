<?php
session_start();
include 'db.php';

$admin_login = 'admin';
$admin_password = '123';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$auth_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    if ($_POST['login'] === $admin_login && $_POST['password'] === $admin_password) {
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $auth_error = 'Неверный логин или пароль';
    }
}

if (!isset($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход в панель управления</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .login-wrapper { display: flex; align-items: center; justify-content: center; height: 80vh; }
            .login-card { background: var(--bg-card); padding: 40px; border-radius: 12px; border: 1px solid #334155; width: 100%; max-width: 400px; text-align: center; }
            .login-card input { width: 100%; padding: 12px; margin: 10px 0 20px; background: #020617; border: 1px solid #334155; color: white; border-radius: 6px; }
            .error-msg { color: #ef4444; margin-bottom: 15px; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="login-wrapper">
            <div class="login-card">
                <h2>Вход (Админ)</h2>
                <?php if ($auth_error): ?><div class="error-msg"><?php echo $auth_error; ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="login_action" value="1">
                    <input type="text" name="login" placeholder="Логин" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <button type="submit" class="btn-primary">Войти</button>
                </form>
                <br><a href="index.php" class="btn-link">Вернуться на сайт</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$action = $_GET['action'] ?? 'list';

if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM effects WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    header("Location: admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_effect'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $html = trim($_POST['html_markup']);
    $css = trim($_POST['css_code']);
    $tags = trim($_POST['tags']);
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare('UPDATE effects SET title = ?, description = ?, html_markup = ?, css_code = ?, tags = ? WHERE id = ?');
        $stmt->execute([$title, $desc, $html, $css, $tags, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO effects (title, description, html_markup, css_code, tags) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $desc, $html, $css, $tags]);
    }
    header("Location: admin.php");
    exit;
}

$edit_data = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM effects WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $edit_data = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель управления эффектами</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .admin-table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 12px; overflow: hidden; border: 1px solid #334155; }
        .admin-table th, .admin-table td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
.admin-table th { background: #0f172a; color: var(--accent); }
        .action-links a { margin-right: 15px; color: var(--text-main); text-decoration: none; font-size: 14px; }
        .action-links a.edit { color: #22c55e; }
        .action-links a.delete { color: #ef4444; }
        .action-links a:hover { text-decoration: underline; }
        
        .admin-form { background: var(--bg-card); padding: 30px; border-radius: 12px; border: 1px solid #334155; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--text-muted); }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; background: #020617; border: 1px solid #334155; color: white; border-radius: 6px; font-family: inherit; }
        .form-group textarea.code-font { font-family: 'Consolas', monospace; height: 150px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-flex">
            <a href="index.php" class="logo">CSS<span>Craft</span></a>
            <nav class="nav">
                <a href="admin.php?logout=1" class="nav-link admin-btn">Выйти из админки</a>
            </nav>
        </div>
    </header>

    <main class="container page-content">
        
        <?php if ($action === 'list'): ?>
            <div class="admin-header">
                <h2>Управление эффектами</h2>
                <a href="admin.php?action=add" class="btn-primary" style="width: auto; padding: 10px 20px;">+ Добавить эффект</a>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Теги</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stmt = $pdo->query('SELECT id, title, tags FROM effects ORDER BY id DESC');
                    while ($row = $stmt->fetch()): 
                    ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['tags']); ?></td>
                            <td class="action-links">
                                <a href="admin.php?action=edit&id=<?php echo $row['id']; ?>" class="edit">Редактировать</a>
                                <a href="admin.php?action=delete&id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Точно удалить эффект?');">Удалить</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-header">
                <h2><?php echo $action === 'edit' ? 'Редактировать эффект' : 'Новый эффект'; ?></h2>
                <a href="admin.php" class="btn-link">&larr; Назад к списку</a>
            </div>

            <form method="POST" class="admin-form">
                <input type="hidden" name="save_effect" value="1">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Название эффекта</label>
                    <input type="text" name="title" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['title']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Описание (коротко)</label>
                    <textarea name="description" required><?php echo $edit_data ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                </div>
<div class="form-group">
                    <label>Теги (через запятую)</label>
                    <input type="text" name="tags" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['tags']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>HTML разметка</label>
                    <textarea name="html_markup" class="code-font" required><?php echo $edit_data ? htmlspecialchars($edit_data['html_markup']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>CSS код</label>
                    <textarea name="css_code" class="code-font" style="height: 250px;" required><?php echo $edit_data ? htmlspecialchars($edit_data['css_code']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn-primary">Сохранить в базу</button>
            </form>
        <?php endif; ?>

    </main>
</body>
</html>