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
            <div class="message success">Данные сохранены</div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="form-group">
                <label>ФИО *</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Телефон *</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>E-mail *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Дата рождения *</label>
                <input type="date" name="birth_date" value="<?= htmlspecialchars($old['birth_date'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Пол *</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" <?= ($old['gender'] ?? '') == 'male' ? 'checked' : '' ?> required> Муж</label>
                    <label><input type="radio" name="gender" value="female" <?= ($old['gender'] ?? '') == 'female' ? 'checked' : '' ?>> Жен</label>
                    <label><input type="radio" name="gender" value="other" <?= ($old['gender'] ?? '') == 'other' ? 'checked' : '' ?>> Другой</label>
                </div>
            </div>

            <div class="form-group">
                <label>Любимый ЯП</label>
                <select name="languages[]" multiple required>
                    <?php
                    $list = ['Pascal','C','C++','JavaScript','PHP','Python','Java','Haskell','Clojure','Prolog','Scala','Go'];
                    foreach ($list as $i => $name) {
                        $id = $i + 1;
                        $sel = in_array((string)$id, $old['languages'] ?? []) ? 'selected' : '';
                        echo "<option value='$id' $sel>$name</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Биография</label>
                <textarea name="biography"><?= htmlspecialchars($old['biography'] ?? '') ?></textarea>
            </div>

            <div class="form-group checkbox-group">
                <label><input type="checkbox" name="contract" value="1" <?= isset($old['contract']) ? 'checked' : '' ?> required> С контрактом ознакомлен(а) *</label>
            </div>

            <button type="submit" name="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>