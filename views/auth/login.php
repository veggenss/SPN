<?php
$status = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Samtaler på nett</title>
    <link rel="icon" href="assets/icons/logo.ico" />
    <link rel="stylesheet" href="/css/authStyle.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
</head>

<body>
    <div class="auth-con">
        <h2>Logg inn</h2>
        <p>For å bruke Samtaler på Nett, må du logge inn.</p> <br>
        <?php if($status) echo "<span class=\"" . $status['class'] . "\">" . $status['message'] . "</span>";?>
        <form method="post" action="/login">

            <div class="form-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="brukernavn" name="username" required>
            </div>

            <div class="form-group">
                <label>Passord:</label>
                <input type="password" placeholder="passord" name="password" required>
                <p>Glemt Passord?<br><a id="backButton" href="/password_reset">Tilbakestill Passord <i class="fa-solid fa-arrow-up-right-from-square"></i></a></p>
            </div>

            <button id="submit" type="submit">Logg inn</button>

        </form>

        <p>Har du ikke bruker enda? <a href="/register">Registrer deg her</a></p>
    </div>
</body>

</html>
