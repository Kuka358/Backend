<?php
require_once 'db.php';

function checkAdminAuth(): void {
    $realm = 'Admin Panel';
    $user = '';
    $pass = '';

    if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
    }
    elseif (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        if (preg_match('/^Basic\s+(.*)$/i', $auth, $matches)) {
            $decoded = base64_decode($matches[1]);
            list($user, $pass) = explode(':', $decoded, 2);
        }
    }

    if (empty($user) || empty($pass)) {
        header('HTTP/1.0 401 Unauthorized');
        header("WWW-Authenticate: Basic realm=\"$realm\"");
        exit('Требуется авторизация администратора.');
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE login = ?");
    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($pass, $admin['password_hash'])) {
        header('HTTP/1.0 401 Unauthorized');
        header("WWW-Authenticate: Basic realm=\"$realm\"");
        exit('Неверный логин или пароль.');
    }
}
checkAdminAuth();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();
    
    if (isset($_POST['delete'])) {
        $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([(int)$_POST['id']]);
        $message = '✅ Запись успешно удалена.';
    }
    
    if (isset($_POST['update'])) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE applications SET full_name=?, phone=?, email=?, birth_date=?, gender=?, biography=?, contract_accepted=? WHERE id=?")
                ->execute([
                    trim($_POST['full_name']), trim($_POST['phone']), trim($_POST['email']), 
                    $_POST['birth_date'], $_POST['gender'], trim($_POST['biography']), 
                    isset($_POST['contract']) ? 1 : 0, (int)$_POST['id']
                ]);
            $pdo->prepare("DELETE FROM application_languages WHERE application_id=?")->execute([(int)$_POST['id']]);
            
            $langStmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($_POST['languages'] ?? [] as $lid) {
                $langStmt->execute([(int)$_POST['id'], (int)$lid]);
            }
            $pdo->commit();
            $message = '✅ Данные успешно обновлены.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '❌ Ошибка обновления: ' . $e->getMessage();
        }
    }
}

$pdo = getDB();
$stats = $pdo->query("
    SELECT l.name, COUNT(al.application_id) as count 
    FROM languages l 
    LEFT JOIN application_languages al ON l.id = al.language_id 
    GROUP BY l.id, l.name 
    ORDER BY l.name
")->fetchAll();

$apps = $pdo->query("
    SELECT a.*, GROUP_CONCAT(al.language_id) as lang_ids 
    FROM applications a 
    LEFT JOIN application_languages al ON a.id = al.application_id 
    GROUP BY a.id 
    ORDER BY a.id DESC
")->fetchAll();

$langs_list = $pdo->query("SELECT id, name FROM languages ORDER BY name")->fetchAll();

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_app = $edit_id ? array_filter($apps, fn($a) => $a['id'] == $edit_id)[0] ?? null : null;
$edit_langs = $edit_app && $edit_app['lang_ids'] ? explode(',', $edit_app['lang_ids']) : [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-wrapper { max-width: 1100px; margin: 40px auto; }
        .admin-table { width: 100%; border-collapse: collapse; margin-bottom: var(--spacing); background: var(--color-card); border-radius: var(--radius); box-shadow: var(--shadow); }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--color-border); font-size: 14px; }
        .admin-table th { background: #f8f9fa; color: var(--color-text-light); font-weight: 600; }
        .admin-table tr:last-child td { border-bottom: none; }
        .btn { display: inline-block; padding: 10px 16px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background 0.2s, transform 0.1s; line-height: 1.4; }
        .btn-primary { background: var(--color-primary); color: #fff; }
        .btn-primary:hover { background: var(--color-primary-hover); }
        .btn-danger { background: var(--color-error); color: #fff; }
        .btn-danger:hover { background: #c0392b; }
        .btn-success { background: var(--color-success); color: #fff; }
        .btn-success:hover { background: #219653; }
        .actions { display: flex; gap: 8px; align-items: center; }
        .section-title { margin: 24px 0 12px; font-size: 20px; font-weight: 600; color: var(--color-text); border-bottom: 2px solid var(--color-border); padding-bottom: 8px; }
        .message { margin-bottom: var(--spacing); }
        .back-link { display: inline-block; margin-bottom: 16px; color: var(--color-primary); text-decoration: none; font-size: 14px; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container admin-wrapper">
    <h1>Панель администратора</h1>
    
    <?php if ($message): ?>
        <div class="message <?= str_contains($message, '❌') ? 'error' : 'success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!$edit_app): ?>
        <div class="section-title">Статистика по языкам программирования</div>
        <table class="admin-table">
            <thead><tr><th>Язык программирования</th><th>Количество пользователей</th></tr></thead>
            <tbody>
                <?php foreach ($stats as $s): ?>
                    <tr><td><?= htmlspecialchars($s['name']) ?></td><td><?= (int)$s['count'] ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="section-title">Все заявки пользователей</div>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>ФИО</th><th>Email</th><th>Телефон</th><th>Языки</th><th style="width:180px">Действия</th></tr></thead>
            <tbody>
                <?php foreach ($apps as $a): 
                    $lang_names = [];
                    if ($a['lang_ids']) {
                        $ids = explode(',', $a['lang_ids']);
                        foreach ($ids as $id) {
                            foreach ($langs_list as $l) {
                                if ((string)$l['id'] === $id) { $lang_names[] = $l['name']; break; }
                            }
                        }
                    }
                ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['full_name']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><?= htmlspecialchars($a['phone']) ?></td>
                    <td><?= implode(', ', $lang_names) ?: '<span style="color:var(--color-text-light)">—</span>' ?></td>
                    <td>
                        <div class="actions">
                            <a href="?edit=<?= $a['id'] ?>" class="btn btn-primary">✏️ Изменить</a>
                            <form method="POST" onsubmit="return confirm('Удалить эту запись?');" style="margin:0;">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" name="delete" class="btn btn-danger">🗑️ Удалить</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <a href="admin.php" class="back-link">← Вернуться к списку</a>
        <div class="section-title">Редактирование заявки #<?= $edit_app['id'] ?></div>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_app['id'] ?>">
            <div class="form-group">
                <label>ФИО *</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($edit_app['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Телефон *</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($edit_app['phone']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($edit_app['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Дата рождения *</label>
                <input type="date" name="birth_date" value="<?= $edit_app['birth_date'] ?>" required>
            </div>
            <div class="form-group">
                <label>Пол *</label>
                <select name="gender" required>
                    <option value="male" <?= $edit_app['gender'] == 'male' ? 'selected' : '' ?>>Мужской</option>
                    <option value="female" <?= $edit_app['gender'] == 'female' ? 'selected' : '' ?>>Женский</option>
                </select>
            </div>
            <div class="form-group">
                <label>Любимые ЯП *</label>
                <select name="languages[]" multiple required>
                    <?php foreach ($langs_list as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= in_array((string)$l['id'], $edit_langs) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Биография</label>
                <textarea name="biography" rows="3"><?= htmlspecialchars($edit_app['biography'] ?? '') ?></textarea>
            </div>
            <div class="form-group checkbox-group">
                <label><input type="checkbox" name="contract" <?= $edit_app['contract_accepted'] ? 'checked' : '' ?> required> С контрактом ознакомлен(а) *</label>
            </div>
            <button type="submit" name="update" class="btn btn-success" style="width:auto; margin-top:8px;">Сохранить изменения</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>