<?php
function createUser($xml_file_name, $login, $password, $email, $name)
{
    $session = 'none';
    $sessionApp = 'none';

    //Генерим соль для шифрования пароля
    $salt = generateSalt();

    checkXmlFile($xml_file_name);

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

    //Узел с id php сессии
    $user_session = $dom->createElement('session');
    $user_session_text = $dom->createTextNode($session);
    $user_session->appendChild($user_session_text);

    //Узел с id сессии приложения
    $user_session_app = $dom->createElement('sessionApp');
    $user_session_app_text = $dom->createTextNode($sessionApp);
    $user_session_app->appendChild($user_session_app_text);

    //Собираем сформированные узлы в родительский узел user
    $user->appendChild($user_login);
    $user->appendChild($user_password);
    $user->appendChild($user_email);
    $user->appendChild($user_name);
    $user->appendChild($user_salt);
    $user->appendChild($user_session);
    $user->appendChild($user_session_app);

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
        $user_login = $user->getElementsByTagName('login');
        $login_text = $user_login->item(0)->nodeValue;

        //Если находим нашего, то проверяем пароль
        if (strcmp($login_text, $login) == 0) {
            $user_password = $user->getElementsByTagName('password');
            $password_text = $user_password->item(0)->nodeValue;

            $user_salt = $user->getElementsByTagName('salt');
            $salt_text = $user_salt->item(0)->nodeValue;

            //Если пароли совпадают, то можно отправлять ответ
            if (strcmp(md5(md5($password) . $salt_text), $password_text) == 0) {
                $user_name = $user->getElementsByTagName('name');
                $name_text = $user_name->item(0)->nodeValue;

                $session_id = session_id();
                $session_id_app = generateIdApp();

                if (isAjax()) {
                    //Создаем cookie с id сессии
                    setcookie('session', $session_id_app, 0);
                    setcookie('name', $name_text, 0);

                    //Запоминаем в базе id сессии
                    $user->getElementsByTagName('session')->item(0)->nodeValue = "";
                    $user->getElementsByTagName('session')->item(0)->appendChild($dom->createTextNode($session_id));
                    $dom->save($xml_file_name);

                    //Запоминаем в базе id сессии приложения
                    $user->getElementsByTagName('sessionApp')->item(0)->nodeValue = "";
                    $user->getElementsByTagName('sessionApp')->item(0)->appendChild($dom->createTextNode($session_id_app));
                    $dom->save($xml_file_name);

                    $_SESSION['resp']['success'] = successOutput($name_text);
                    echo json_encode($_SESSION['resp']);
                    exit;
                }
            }
        }
    }

    if (isAjax()) {
        $_SESSION['resp']['not_found'] = 'Пользователь с таким логином или паролем не найден';
        echo json_encode($_SESSION['resp']);
        exit;
    }
}

function userExists($xml_file_name, $login, $email)
{
    checkXmlFile($xml_file_name);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->validateOnParse = true;
    $dom->load($xml_file_name);

    $users = $dom->getElementsByTagName('user');
    //Перебираем юзеров в бд
    foreach ($users as $user) {
        $user_login = $user->getElementsByTagName('login');
        $login_text = $user_login->item(0)->nodeValue;

        $user_email = $user->getElementsByTagName('email');
        $email_text = $user_email->item(0)->nodeValue;

        if (strcmp($login_text, $login) == 0 || strcmp($email_text, $email) == 0) {
            if (isAjax()) {
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

//Вывод в случае успешного входа
function successOutput($name_text)
{
    $output = '<h1>Hello, ' . $name_text . '</h1>';
    $output .= '<button class="quit" onclick="quit()">Выйти</button>';
    return $output;
}

function checkXmlFile($xml_file_name)
{
    if (!file_exists($xml_file_name)) {
        $dom = new DomDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $element = $dom->createElement('users');
        $dom->appendChild($element);
        $dom->save($xml_file_name);
    }
}

function isAjax()
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    } else {
        return false;
    }
}

function isLoggedIn($xml_file_name)
{
    if (!empty($_COOKIE['session']) && !empty($_COOKIE['name'])) {
        $session = session_id();
        $session_id_app = $_COOKIE['session'];
        $name = $_COOKIE['name'];

        checkXmlFile($xml_file_name);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->validateOnParse = true;
        $dom->load($xml_file_name);

        $users = $dom->getElementsByTagName('user');
        //Перебираем юзеров в бд
        foreach ($users as $user) {
            $user_name = $user->getElementsByTagName("name");
            $user_name_text = $user_name->item(0)->nodeValue;

            $user_session = $user->getElementsByTagName("session");
            $user_session_text = $user_session->item(0)->nodeValue;

            $user_session_id_app = $user->getElementsByTagName("sessionApp");
            $user_session_id_app_text = $user_session_id_app->item(0)->nodeValue;

            //ищем id сессии
            if (strcmp($user_name_text, $name) == 0 && strcmp($user_session_id_app_text, $session_id_app) == 0 && strcmp($user_session_text, session_id()) == 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}

function generateIdApp($length = 128)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
