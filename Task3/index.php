<?php
$db_host = 'localhost';
$db_name = 'u82285';
$db_user = 'u82285';
$db_pass = '9623711'; 

$errors = [];
$success = false;
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $fio      = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $date     = $_POST['birth_date'] ?? '';
    $gender   = $_POST['gender'] ?? '';
    $langs    = $_POST['languages'] ?? [];
    $bio      = trim($_POST['biography'] ?? ''); 
    $contract = isset($_POST['contract']) ? 1 : 0;

    if (!preg_match('/^[А-Яа-яA-Za-z\s]{2,150}$/u', $fio)) $errors[] = 'ФИО: только буквы и пробелы (2-150 симв.)';
    if (!preg_match('/^[\+0-9\s\-\(\)]{10,20}$/', $phone)) $errors[] = 'Телефон: неверный формат';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email: неверный формат';
    if (!DateTime::createFromFormat('Y-m-d', $date)) $errors[] = 'Дата: неверный формат';
    if (!in_array($gender, ['male','female'], true)) $errors[] = 'Пол: выберите значение';

    if (!is_array($langs) || empty($langs)) {
        $errors[] = 'Языки: выберите хотя бы один';
    } else {
        foreach ($langs as $l) {
            if (!ctype_digit((string)$l) || (int)$l < 1 || (int)$l > 12) {
                $errors[] = 'Языки: недопустимое значение';
                break;
            }
        }
    }

    $bio_len = function_exists('mb_strlen') ? mb_strlen($bio, 'UTF-8') : (function_exists('iconv_strlen') ? iconv_strlen($bio, 'UTF-8') : strlen($bio));
    if ($bio_len > 1000) $errors[] = 'Биография: макс. 1000 символов';

    if ($contract !== 1) $errors[] = 'Контракт: отметьте галочку';

    if (empty($errors)) {
        $pdo = null; 
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_accepted) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$fio, $phone, $email, $date, $gender, $bio ?: null, $contract]);

            $appId = $pdo->lastInsertId(); 

            $langStmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($langs as $langId) {
                $langStmt->execute([$appId, (int)$langId]);
            }

            $pdo->commit();
            $success = true;
            $old = []; 
        } catch (PDOException $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Ошибка сервера при сохранении. Попробуйте позже.';
        }
    }
}

require 'form.php';
?>