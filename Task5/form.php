<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма регистрации</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Анкета</h1>
        
        <?php if ($is_authorized): ?>
            <div class="auth-info">
                <p>Вы вошли как: <strong><?= htmlspecialchars($_SESSION['login']) ?></strong></p>
                <a href="?logout=1" class="logout-btn">Выйти</a>
            </div>
        <?php endif; ?>
        
        <?php if ($generated_credentials): ?>
            <div class="message success">
                <p>✓ Данные успешно сохранены</p>
                <p class="credentials"><?= $generated_credentials ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($login_errors)): ?>
            <div class="message error">
                <?php foreach ($login_errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="message error">
                <strong>Исправьте ошибки:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success && !$generated_credentials): ?>
            <div class="message success">✓ Данные успешно сохранены</div>
        <?php endif; ?>

        <?php if (!$is_authorized): ?>
            <div class="login-form">
                <h2>Вход для редактирования</h2>
                <form method="POST" action="index.php">
                    <div class="form-group">
                        <label>Логин</label>
                        <input type="text" name="login" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" name="login_submit">Войти</button>
                </form>
            </div>
        <?php endif; ?>

        <form method="GET" action="index.php">
            <?php if ($is_authorized): ?>
                <input type="hidden" name="edit" value="1">
            <?php endif; ?>
            
            <div class="form-group <?= hasError('full_name', $field_errors) ?>">
                <label>ФИО *</label>
                <input type="text" name="full_name" 
                       value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                <?php if (getErrorMessage('full_name', $field_errors)): ?>
                    <div class="field-error"><?= htmlspecialchars(getErrorMessage('full_name', $field_errors)) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= hasError('phone', $field_errors) ?>">
                <label>Телефон *</label>
                <input type="tel" name="phone" 
                       value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                <?php if (getErrorMessage('phone', $field_errors)): ?>
                    <div class="field-error"><?= htmlspecialchars(getErrorMessage('phone', $field_errors)) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= hasError('email', $field_errors) ?>">
                <label>E-mail *</label>
                <input type="email" name="email" 
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                <?php if (getErrorMessage('email', $field_errors)): ?>
                    <div class="field-error"><?= htmlspecialchars(getErrorMessage('email', $field_errors)) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= hasError('birth_date', $field_errors) ?>">
                <label>Дата рождения *</label>
                <input type="date" name="birth_date" 
                       value="<?= htmlspecialchars($old['birth_date'] ?? '') ?>">
                <?php if (getErrorMessage('birth_date', $field_errors)): ?>
                    <div class="field-error"><?= htmlspecialchars(getErrorMessage('birth_date', $field_errors)) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= hasError('gender', $field_errors) ?>">
                <label>Пол *</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" 
                        <?= (($old['gender'] ?? '') == 'male') ? 'checked' : '' ?>> Муж</label>
                    <label><input type="radio" name="gender" value="female" 
                        <?= (($old['gender'] ?? '') == 'female') ? 'checked' : '' ?>> Жен</label>
                </div>
                <?php if (getErrorMessage('gender', $field_errors)): ?>
                    <div class="field-error"><?= htmlspecialchars(getErrorMessage('gender', $field_errors)) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= hasError('languages', $field_errors) ?>">
                <label>Любимый ЯП *</label>
                <select name="languages[]" multiple>
                    <?php
                    $selected_langs = $old['languages'] ?? [];
                    foreach ($languages_list as $i => $name) {
                        $id = $i + 1;
                        $sel = (is_array($selected_langs) && in_array((string)$id, $selected_langs)) ? 'selected' : '';
                        echo "<option value='$id' $sel>$name</option>";
                    }
                    ?>
                </select>
                <?php if (getErrorMessage('languages', $field_errors)): ?>
                    <div class="field-error"><?= htmlspecialchars(getErrorMessage('languages', $field_errors)) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= hasError('biography', $field_errors) ?>">
                <label>Биография</label>
                <textarea name="biography"><?= htmlspecialchars($old['biography'] ?? '') ?></textarea>
                <?php if (getErrorMessage('biography', $field_errors)): ?>
                    <div class="field-error"><?= htmlspecialchars(getErrorMessage('biography', $field_errors)) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group checkbox-group <?= hasError('contract', $field_errors) ?>">
                <label>
                    <input type="checkbox" name="contract" value="1" 
                        <?= (isset($old['contract']) && $old['contract'] == 1) ? 'checked' : '' ?>> 
                    С контрактом ознакомлен(а) *
                </label>
                <?php if (getErrorMessage('contract', $field_errors)): ?>
                    <div class="field-error"><?= htmlspecialchars(getErrorMessage('contract', $field_errors)) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" name="submit">
                <?= $is_authorized ? 'Сохранить изменения' : 'Сохранить' ?>
            </button>
        </form>
    </div>
</body>
</html>