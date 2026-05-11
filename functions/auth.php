<?php
function checkAdminAccess() {
    if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != 1) {
        header("Location: login.php");
        exit();
    }
}

function checkUserAccess() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}
?>