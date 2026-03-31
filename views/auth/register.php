<?php
$status = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler på nett</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/userRegLog.css">
    <link rel="icon" href="assets/icons/logo.ico">
</head>

<body>
    <div class="auth-con">
        <h2>Registrering</h2>
        <p>Du må registrere deg for å bruke nettsiden</p>
        <?php if($status) echo "<span class=\"" . $status['class'] . "\">" . $status['message'] . "</span>";?>
        <form method="post" action="/register" class="register-form">

            <div class="form-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="brukernavn" name="username" required>
            </div>

            <div class="form-group">
                <label>E-post:</label>
                <input type="email" placeholder="e-post" name="email" required>
            </div>

            <div class="form-group">
                <label>Passord:</label>
                <input type="password" placeholder="passord" name="password" required>
            </div>

            <!--<div class="form-group">
                <label>Profilbilde:</label>
                <input type="file" name="profile_picture">
            </div>-->

            <button type="submit" id="submit">Registrer deg</button>

            <p>Har du allerede bruker? <a href="/login">Logg inn her</a></p>
        </form>
    </div>
</body>
</html>
