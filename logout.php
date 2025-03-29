<?php
// Initialize the session
session_start();

// Determine user type
$user_type = "";
if(isset($_SESSION["parent_loggedin"]) && $_SESSION["parent_loggedin"] === true) {
    $user_type = "parent";
}
else if(isset($_SESSION["school_logged_in"]) && $_SESSION["school_logged_in"] === true) {
    $user_type = "school";
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page based on user type
if($user_type === "parent") {
    header("location: parent_login.php?logout_success=true");
}
else if($user_type === "school") {
    header("location: school_login.php?logout_success=true");
}
else {
    // Default to index if user type can't be determined
    header("location: index.html?logout_success=true");
}
exit;
?> 