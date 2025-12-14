<?php
function registerUser($email, $password, $role, $deleteFlag = 0)
{
    $errFlag = false;
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        throw new Exception("DB error: " . $db->connect_error, 500);
    }
    $insertPrep = $db->prepare("INSERT INTO `users` (passWord,email,role,deleteFlag) VALUES (?,?,?,?)");
    $selectPrep = $db->prepare("SELECT userId FROM `users` WHERE email=?");
    $selectPrep->bind_param("s", $email);
    $pass = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $insertPrep->bind_param("ssss", $password, $email, $role, $deleteFlag);
    $selectPrep->execute();
    $result = $selectPrep->get_result();
    if ($result->num_rows > 0)
        $errFlag = true;
    else {
        if (!$insertPrep->execute())
            $errFlag = true;
    }
    $db->close();
    if (!$errFlag)
        echo "Record added";
    else
        throw new Exception("Record insertion failed.", 500);
}
?>