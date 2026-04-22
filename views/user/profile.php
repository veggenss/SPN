<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler på nett | Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="../assets/icons/logo.ico" />
    <link rel="stylesheet" href="../css/authStyle.css">
    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
</head>

<body>
    <div class="auth-con">
        <h2><?php echo htmlspecialchars($_SESSION['user']['username']); ?>'s profil</h2>
        <form action="/api-updateProfile" method="post">
            <div class="profile-group">
                <div class="current-profile">
                    <!--<img src="../uploads/<?php echo htmlspecialchars($_SESSION['user']['profile_picture']); ?>" alt="Profilbilde">-->
                </div>
                <label for="profile_picture">Velg nytt profilbilde:</label>
                <input type="file" name="profile_picture" id="profile_picture">
            </div>

            <div class="profile-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="<?php echo htmlspecialchars($_SESSION['user']['username']); ?>" value="<?php echo htmlspecialchars($_SESSION['user']['username']); ?>" name="username">
            </div>

            <div class="profile-group">
                <label>E-post:</label>
                <input type="email" placeholder="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" name="email">
            </div>

            <div class="profile-group">
                <p>Bytte Passord? <br><a id="backButton" href="password_reset.php">Tilbakestill Passord <i class="fa-solid fa-arrow-up-right-from-square"></i></a></p>
            </div>
            <button id="submit" type="submit" onclick="return confirm('Hvis du har endret e-post: \nDu blir logget ut og må verifisere e-posten før du logger inn igjen')">Lagre Endringer</button>
            <div class="profile-group">
                <a href="/logout" id="logout">Logg ut</a>
            </div>
        </form>

        <p>Antall samtalepoeng: <?php // samtalepoeng går her ?></p>
        <a id="backButton" href="/chat">Tilbake til Samtaler På Nett</a>
    </div>
</body>

</html>
