<?php
//Параметры
$xml_file_name = 'user.xml';
$wrong_symbols_error = 'Используются недопустимые символы ( Допускаются английские буквы, цифры, - , _ )';
$empty_field_error = 'Поле не должно быть пустым';
$compare_password_error = 'Пароль должен совпадать с указанным выше';
$email_format_error = 'Email указан в неверном формате';

session_start();
//Чистим старые ошибки
unset($_SESSION['errors']);
unset($_SESSION['resp']);

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
    $count = substr_count(implode(array_keys($_SESSION)), "errors");
    if ($count > 0) {
        //Формируем ответ с ошибками (AJAX)
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
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
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $session = 'none'; //пока юзер ни разу не зашел будет none

    //Ищем ошибки
    if (empty($login)) {
        $_SESSION['errors']['login'] = $empty_field_error;
    } elseif (!preg_match("#^[aA-zZ0-9\-_]+$#", $login)) {
        $_SESSION['errors']['login'] = $wrong_symbols_error;
    }

    if (empty($password)) {
        $_SESSION['errors']['password'] = $empty_field_error;
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
    } elseif (!preg_match("#^[aA-zZ0-9\-_]+$#", $name)) {
        $_SESSION['errors']['name'] = $wrong_symbols_error;
    }

    //Отправляем ошибки пользователю
    $count = substr_count(implode(array_keys($_SESSION)), "errors");
    if ($count > 0) {
        //Формируем ответ с ошибками (AJAX)
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode($_SESSION['errors']);
            exit;
        }
    } else {
        //Ошибок нет. Обрабатываем данные формы
        userExist($xml_file_name, $login, $email);
        createUser($xml_file_name, $_POST['login'], $_POST['password'], $_POST['email'], $_POST['name'], $session);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $_SESSION['resp']['created'] = 'Пользователь ' . $name . ' успешно создан';
            echo json_encode($_SESSION['resp']);
            exit;
        }
    }
}

function createUser($xml_file_name, $login, $password, $email, $name, $session)
{
    //Генерим соль для шифрования пароля
    $salt = generateSalt();

    if (!file_exists($xml_file_name)) {
        //Creates XML string and XML document using the DOM 
        $dom = new DomDocument('1.0', 'UTF-8');
        $dom->formatOutput = true; // set the formatOutput attribute of domDocument to true
        $element = $dom->createElement('users');
        $dom->appendChild($element);
        $dom->save($xml_file_name); // save as file
    }

    //ДОБАВЛЯЕМ ЮЗЕРА В XML ФАЙЛ USER.XML
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->validateOnParse = true;
    $dom->load($xml_file_name);

    //Указываем на корневой узел (users)
    $root = $dom->getElementsByTagName('users')->item(0);

    //Cоздаем пустого юзера
    $user = $dom->createElement('user');

    //Узел с логином
    $user_login = $dom->createElement('login');
    $user_login_text = $dom->createTextNode($login);
    $user_login->appendChild($user_login_text);

    //Узел с паролем
    $user_password = $dom->createElement('password');
    $user_password_text = $dom->createTextNode(md5(md5($password) . $salt));
    $user_password->appendChild($user_password_text);

    //Узел с мейлом
    $user_email = $dom->createElement('email');
    $user_email_text = $dom->createTextNode($email);
    $user_email->appendChild($user_email_text);

    //Узел с именем пользователя
    $user_name = $dom->createElement('name');
    $user_name_text = $dom->createTextNode($name);
    $user_name->appendChild($user_name_text);

    //Узел с солью
    $user_salt = $dom->createElement('salt');
    $user_salt_text = $dom->createTextNode($salt);
    $user_salt->appendChild($user_salt_text);

    //Узел с id сессии
    $user_session = $dom->createElement('session');
    $user_session_text = $dom->createTextNode($session);
    $user_session->appendChild($user_session_text);

    //Собираем сформированные узлы в родительский узел user
    $user->appendChild($user_login);
    $user->appendChild($user_password);
    $user->appendChild($user_email);
    $user->appendChild($user_name);
    $user->appendChild($user_salt);
    $user->appendChild($user_session);

    //Вставляем узел user в корневой узел users
    $root->appendChild($user);

    $dom->save($xml_file_name);
}

function generateSalt()
{
    $salt = '';
    $length = rand(5, 10); // длина соли (от 5 до 10 сомволов)
    for ($i = 0; $i < $length; $i++) {
        $salt .= chr(rand(33, 126));
    }
    return $salt;
}


function login($xml_file_name, $login, $password)
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->validateOnParse = true;
    $dom->load($xml_file_name);

    $users = $dom->getElementsByTagName('user');
    //Перебираем юзеров в бд
    foreach ($users as $user) {
        $user_login = $user->getElementsByTagName("login");
        $login_text = $user_login->item(0)->nodeValue;

        //Если находим нашего, то проверяем пароль
        if (strcmp($login_text, $login) == 0) {
            $user_password = $user->getElementsByTagName("password");
            $password_text = $user_password->item(0)->nodeValue;

            $user_salt = $user->getElementsByTagName("salt");
            $salt_text = $user_salt->item(0)->nodeValue;

            //Если пароли совпадают, то можно отправлять ответ
            if (strcmp(md5(md5($password) . $salt_text), $password_text) == 0) {
                $user_name = $user->getElementsByTagName("name");
                $name_text = $user_name->item(0)->nodeValue;

                //Берем id сессии для отправки в куках и сохранения в базе
                $session_id = session_id();

                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    //Создаем cookie с id сессии
                    setcookie("session", $session_id, time() + 600);
                    setcookie("name", $name_text, time() + 600);

                    //Запоминаем в базе id сессии
                    $user->getElementsByTagName("session")->item(0)->nodeValue = "";
                    $user->getElementsByTagName("session")->item(0)->appendChild($dom->createTextNode($session_id));
                    $dom->save($xml_file_name);

                    $_SESSION['resp']['success'] = 'Hello, ' . $name_text;
                    echo json_encode($_SESSION['resp']);
                    exit;
                }
            }
        }
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $_SESSION['resp']['not_found'] = 'Пользователь с таким логином или паролем ' . $login . ' не найден';
        echo json_encode($_SESSION['resp']);
        exit;
    }
}

function userExist($xml_file_name, $login, $email)
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->validateOnParse = true;
    $dom->load($xml_file_name);

    $users = $dom->getElementsByTagName('user');
    //Перебираем юзеров в бд
    foreach ($users as $user) {
        $user_login = $user->getElementsByTagName("login");
        $login_text = $user_login->item(0)->nodeValue;

        $user_email = $user->getElementsByTagName("email");
        $email_text = $user_email->item(0)->nodeValue;

        if (strcmp($login_text, $login) == 0 || strcmp($email_text, $email) == 0) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                if (strcmp($login_text, $login) == 0) {
                    $_SESSION['resp']['exist'] = 'Пользователь с указанным логином уже существует';
                } else {
                    $_SESSION['resp']['exist'] = 'Пользователь с указанной почтой уже существует';
                }
                echo json_encode($_SESSION['resp']);
                exit;
            }
        }
    }
}
