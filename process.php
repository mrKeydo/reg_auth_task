<?php
include_once 'lib.php';
//Параметры
$xml_file_name = 'user.xml';
$wrong_symbols_error = 'Используются недопустимые символы ( Допускаются английские буквы, цифры, - , _ )';
$empty_field_error = 'Поле не должно быть пустым';
$compare_password_error = 'Пароль должен совпадать с указанным выше';
$email_format_error = 'Email указан в неверном формате';

session_start();
unset($_SESSION['errors']);
unset($_SESSION['resp']);

if (isLoggedIn($xml_file_name)) {
    if (isAjax()) {
        $_SESSION['resp']['success'] = successOutput($_COOKIE['name']);
        echo json_encode($_SESSION['resp']);
        exit;
    }
}

//Обработчик для формы авторизации
if (isset($_POST['login_auth'])) {
    $login = trim($_POST['login_auth']);
    $password = trim($_POST['password_auth']);

    if (empty($login)) {
        $_SESSION['errors']['login_auth'] = $empty_field_error;
    } elseif (!preg_match("#^[aA-zZ0-9\-_]+$#", $login)) {
        $_SESSION['errors']['login_auth'] = $wrong_symbols_error;
    }
    if (empty($password)) {
        $_SESSION['errors']['password_auth'] = $empty_field_error;
    }

    //Отправляем ошибки пользователю
    $count = substr_count(implode(array_keys($_SESSION)), 'errors');
    if ($count > 0) {
        //Формируем ответ с ошибками (AJAX)
        if (isAjax()) {
            echo json_encode($_SESSION['errors']);
            exit;
        }
    } else {
        //Ошибок нет. Обрабатываем данные формы
        login($xml_file_name, $login, $password);
    }
}

//Обработчик для формы регистрации
if (isset($_POST['login'])) {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);

    //Ищем ошибки
    if (empty($login)) {
        $_SESSION['errors']['login'] = $empty_field_error;
    } elseif (!preg_match('#^[aA-zZ0-9\-_]+$#', $login)) {
        $_SESSION['errors']['login'] = $wrong_symbols_error;
    }

    if (empty($password)) {
        $_SESSION['errors']['password'] = $empty_field_error;
    } elseif (!preg_match('#^[aA-zZ0-9\-_]+$#', $password)) {
        $_SESSION['errors']['password'] = $wrong_symbols_error;
    }

    if (empty($confirm_password)) {
        $_SESSION['errors']['confirm_password'] = $empty_field_error;
    } elseif (strcmp($password, $confirm_password) != 0) {
        $_SESSION['errors']['confirm_password'] = $compare_password_error;
    }

    if (empty($email)) {
        $_SESSION['errors']['email'] = $empty_field_error;
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['errors']['email'] = $email_format_error;
    }

    if (empty($name)) {
        $_SESSION['errors']['name'] = $empty_field_error;
    } elseif (!preg_match('#^[aA-zZ0-9\-_]+$#', $name)) {
        $_SESSION['errors']['name'] = $wrong_symbols_error;
    }

    //Отправляем ошибки пользователю
    $count = substr_count(implode(array_keys($_SESSION)), "errors");
    if ($count > 0) {
        //Формируем ответ с ошибками (AJAX)
        if (isAjax()) {
            echo json_encode($_SESSION['errors']);
            exit;
        }
    } else {
        //Ошибок нет. Обрабатываем данные формы
        userExists($xml_file_name, $login, $email);
        createUser($xml_file_name, $login, $password, $email, $name);
        if (isAjax()) {
            $_SESSION['resp']['created'] = 'Пользователь ' . $login . ' успешно создан';
            echo json_encode($_SESSION['resp']);
            exit;
        }
    }
}