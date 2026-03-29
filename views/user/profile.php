<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler på nett | Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="../assets/icons/logo.ico" />
    <link rel="stylesheet" href="../css/userRegLog.css">
    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
</head>

<body>
    <div class="auth-con">
        <h2><?php echo htmlspecialchars($_SESSION["username"]); ?>'s profil</h2> <!-- det er (username)s profil ikke (username)'s profil!!! vi bruker ikke apostrof for det sånt på norsk!!!!!! - isak -->
        <?php if (isset($error)): ?>
            <div class="error"><?php echo "{$error}<br>"; ?></div>
        <?php elseif(isset($message)): ?>
            <div class="positive"><?php echo "{$message}<br>"; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
            <div class="profile-group">
                <div class="current-profile">
                    <img src="../uploads/<?php echo htmlspecialchars($_SESSION["profile_picture"]); ?>" alt="Profilbilde">
                </div>
                <label for="profile_picture">Velg nytt profilbilde:</label>
                <input type="file" name="profile_picture" id="profile_picture">
            </div>

            <div class="profile-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="<?php echo htmlspecialchars($_SESSION['username']); ?>" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" name="username">
            </div>

            <div class="profile-group">
                <label>E-post:</label>
                <input type="email" placeholder="<?php echo htmlspecialchars($_SESSION['email']); ?>" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" name="email">
            </div>

            <div class="profile-group">
                <p>Bytte Passord? <br><a id="backButton" href="password_reset.php">Tilbakestill Passord <i class="fa-solid fa-arrow-up-right-from-square"></i></a></p>
            </div>
            <button id="submit" type="submit" onclick="return confirm('Hvis du har endret e-post: \nDu blir logget ut og må verifisere e-posten før du logger inn igjen')">Lagre Endringer</button>
            <div class="profile-group">
                <a href="/logout.php" id="logout">Logg ut</a>
            </div>
        </form>

        <p>Antall samtalepoeng: <?php // samtalepoeng går her ?></p>
        <a id="backButton" href="../main.php">Tilbake til Samtaler På Nett</a>
    </div>
</body>

</html>
