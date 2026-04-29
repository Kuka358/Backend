<?php
$db_host = 'localhost';
$db_name = 'u82285';
$db_user = 'u82285';
$db_pass = '9623711';

$languages_list = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

function getCookieValue($name, $default = '') {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
}

function setCookieValue($name, $value, $expiry = 0) {
    if ($expiry === 0) {
        setcookie($name, $value, 0, '/');
    } else {
        setcookie($name, $value, time() + $expiry, '/');
    }
}

function deleteCookie($name) {
    setcookie($name, '', time() - 3600, '/');
}

$errors = [];
$field_errors = [];
$success = false;
$old = [];

if (isset($_GET['old_data']) && !empty($_GET['old_data'])) {
    $old = json_decode($_GET['old_data'], true);
    if (!is_array($old)) {
        $old = [];
    }
}

if (empty($old)) {
    $default_values = [
        'full_name' => getCookieValue('default_full_name', ''),
        'phone' => getCookieValue('default_phone', ''),
        'email' => getCookieValue('default_email', ''),
        'birth_date' => getCookieValue('default_birth_date', ''),
        'gender' => getCookieValue('default_gender', ''),
        'languages' => getCookieValue('default_languages', ''),
        'biography' => getCookieValue('default_biography', '')
    ];
    
    if (!empty($default_values['languages']) && !is_array($default_values['languages'])) {
        $default_values['languages'] = unserialize($default_values['languages']);
        if (!is_array($default_values['languages'])) {
            $default_values['languages'] = [];
        }
    } elseif (!is_array($default_values['languages'])) {
        $default_values['languages'] = [];
    }
    
    $old = $default_values;
}

if (isset($_COOKIE['form_errors'])) {
    $saved_errors = unserialize($_COOKIE['form_errors']);
    if (is_array($saved_errors)) {
        $field_errors = $saved_errors;
    }
    deleteCookie('form_errors');
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = true;
    $old = [];
    
    $old['full_name'] = getCookieValue('default_full_name', '');
    $old['phone'] = getCookieValue('default_phone', '');
    $old['email'] = getCookieValue('default_email', '');
    $old['birth_date'] = getCookieValue('default_birth_date', '');
    $old['gender'] = getCookieValue('default_gender', '');
    $old['biography'] = getCookieValue('default_biography', '');
    
    $langs_cookie = getCookieValue('default_languages', '');
    if (!empty($langs_cookie)) {
        $old['languages'] = unserialize($langs_cookie);
        if (!is_array($old['languages'])) {
            $old['languages'] = [];
        }
    } else {
        $old['languages'] = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['submit']) && !isset($_GET['old_data'])) {
    $fio = trim($_GET['full_name'] ?? '');
    $phone = trim($_GET['phone'] ?? '');
    $email = trim($_GET['email'] ?? '');
    $date = $_GET['birth_date'] ?? '';
    $gender = $_GET['gender'] ?? '';
    $langs = $_GET['languages'] ?? [];
    $bio = trim($_GET['biography'] ?? '');
    $contract = isset($_GET['contract']) ? 1 : 0;
    
    $old = [
        'full_name' => $fio,
        'phone' => $phone,
        'email' => $email,
        'birth_date' => $date,
        'gender' => $gender,
        'languages' => $langs,
        'biography' => $bio,
        'contract' => $contract
    ];
    
    if (empty($fio)) {
        $errors[] = 'ФИО обязательно для заполнения';
        $field_errors['full_name'] = 'Поле обязательно для заполнения';
    } elseif (!preg_match('/^[А-Яа-яA-Za-zЁё\s\-]{2,150}$/u', $fio)) {
        $errors[] = 'ФИО: допустимы только буквы (русские/английские), пробелы и дефисы (2-150 символов)';
        $field_errors['full_name'] = 'Допустимы только буквы (русские/английские), пробелы и дефисы';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязательно для заполнения';
        $field_errors['phone'] = 'Поле обязательно для заполнения';
    } elseif (!preg_match('/^[\+\d\s\-\(\)]{10,20}$/', $phone)) {
        $errors[] = 'Телефон: допустимы только цифры, +, пробелы, дефисы и круглые скобки (10-20 символов)';
        $field_errors['phone'] = 'Допустимы только цифры, +, пробелы, дефисы и круглые скобки';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязательно для заполнения';
        $field_errors['email'] = 'Поле обязательно для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errors[] = 'Email: неверный формат (пример: name@domain.com)';
        $field_errors['email'] = 'Неверный формат email (пример: name@domain.com)';
    }
    
    if (empty($date)) {
        $errors[] = 'Дата рождения обязательно для заполнения';
        $field_errors['birth_date'] = 'Поле обязательно для заполнения';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = 'Дата рождения: неверный формат (должен быть ГГГГ-ММ-ДД)';
        $field_errors['birth_date'] = 'Неверный формат даты (ГГГГ-ММ-ДД)';
    } else {
        $parts = explode('-', $date);
        $year = (int)$parts[0];
        $month = (int)$parts[1];
        $day = (int)$parts[2];
        
        if (!checkdate($month, $day, $year)) {
            $errors[] = 'Дата рождения: несуществующая дата';
            $field_errors['birth_date'] = 'Несуществующая дата';
        } elseif ($year < 1900 || $year > date('Y')) {
            $errors[] = 'Дата рождения: год должен быть от 1900 до текущего';
            $field_errors['birth_date'] = 'Год должен быть от 1900 до ' . date('Y');
        }
    }
    
    if (empty($gender)) {
        $errors[] = 'Пол обязательно для заполнения';
        $field_errors['gender'] = 'Поле обязательно для заполнения';
    } elseif (!preg_match('/^(male|female)$/', $gender)) {
        $errors[] = 'Пол: допустимы только значения "male" или "female"';
        $field_errors['gender'] = 'Выберите пол из предложенных вариантов';
    }
    
    if (empty($langs)) {
        $errors[] = 'Языки программирования: выберите хотя бы один язык';
        $field_errors['languages'] = 'Выберите хотя бы один язык';
    } else {
        foreach ($langs as $l) {
            if (!preg_match('/^[1-9]|1[0-2]$/', (string)$l)) {
                $errors[] = 'Языки: обнаружены недопустимые значения';
                $field_errors['languages'] = 'Выберите языки из предложенного списка';
                break;
            }
        }
    }
    
    if (!empty($bio)) {
        if (!preg_match('/^[\s\S]{0,1000}$/u', $bio)) {
            $errors[] = 'Биография: максимальная длина 1000 символов';
            $field_errors['biography'] = 'Максимум 1000 символов';
        }
    }
    
    if ($contract !== 1) {
        $errors[] = 'Контракт: необходимо подтвердить ознакомление с контрактом';
        $field_errors['contract'] = 'Необходимо подтвердить ознакомление';
    }
    
    if (empty($errors)) {
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
            
            setCookieValue('default_full_name', $fio, 365 * 24 * 3600);
            setCookieValue('default_phone', $phone, 365 * 24 * 3600);
            setCookieValue('default_email', $email, 365 * 24 * 3600);
            setCookieValue('default_birth_date', $date, 365 * 24 * 3600);
            setCookieValue('default_gender', $gender, 365 * 24 * 3600);
            setCookieValue('default_languages', serialize($langs), 365 * 24 * 3600);
            setCookieValue('default_biography', $bio, 365 * 24 * 3600);
            
            header('Location: index.php?success=1');
            exit();
            
        } catch (PDOException $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Ошибка сервера при сохранении. Попробуйте позже.';
            
            setCookieValue('form_errors', serialize($field_errors), 0);
            
            $old_data = json_encode($old);
            header("Location: index.php?old_data=" . urlencode($old_data));
            exit();
        }
    } else {
        setCookieValue('form_errors', serialize($field_errors), 0);
        
        $old_data = json_encode($old);
        header("Location: index.php?old_data=" . urlencode($old_data));
        exit();
    }
}

function hasError($field_name, $field_errors) {
    return isset($field_errors[$field_name]) ? 'error-field' : '';
}

function getErrorMessage($field_name, $field_errors) {
    return isset($field_errors[$field_name]) ? $field_errors[$field_name] : '';
}

require 'form.php';
?>