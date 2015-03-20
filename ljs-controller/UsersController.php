<?php
/**
 * UsersController (mainly for admin authentication)
 */

function addUser(User $user) {
    $db = getDb();
    $enc_password = password_hash($user->password, PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users(userName, email, password) VALUES (:userName, :email, :password)');
    $stmt->bindParam(':userName', $user->userName);
    $stmt->bindParam(':email', $user->email);
    $stmt->bindParam(':password', $enc_password);
    $stmt->execute();
    $user->id = $db->lastInsertId();
    return $user->id;
}

function login($login, $password) {
    $stmt = getDb()->prepare('SELECT * FROM users WHERE userName = :userName');
    $stmt->bindParam(':userName', $login);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($res) == 0)
        return null;

    $enc_password = $res[0]['password'];

    if (password_verify($password, $enc_password))
        return User::createFromDb($res[0]);
    else
        return null;
}
