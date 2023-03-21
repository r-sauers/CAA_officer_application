<?php
require("./library/verify_login.php");
verify_login();
?>
<!DOCTYPE html>
<html>

<head>
<title>CAA Officer Application</title>
</head>

<body>
    <h1>CAA Officer Application</h1>
    <h3>(under development)</h3>
    <p>Directory: </p>
    <ul>
        <li><a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/createevent.php">Create Event</a></li>
    </ul>
</body>

</html>