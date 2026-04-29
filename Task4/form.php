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
        
        <?php if ($success): ?>
            <div class="message success">✓ Данные успешно сохранены</div>
        <?php endif; ?>

        <form method="GET" action="index.php">
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

            <button type="submit" name="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>