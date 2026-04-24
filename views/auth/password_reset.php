<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/icons/logo.png" />
    <link rel="stylesheet" href="/css/authStyle.css">
    <title>Samtaler på nett | Oppdater Passord</title>
</head>
<body>
    <div class="auth-con">
        <h2>Tilbakestill Passord</h2><br>
        <form method="post" action="/api/password_reset">
            <div class="form-group">
                <label>Gammle Passord:</label>
                <input type="password" name="old-password" required>
                <br><br>
                <label>Nytt Passord:</label>
                <input type="password" name="new-password" required>
                <br><br>
                <label>Gjenta Nytt Passord:</label>
                <input type="password" name="r-new-password" required>
            </div>

            <button type="submit">Oppdater Passord</button>
        </form>
    </div>
</body>
</html>