<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler på nett | Main</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="/css/mainStyle.css" />
    <link rel="icon" href="/assets/icons/logo.ico" />

    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
</head>

<body>   
   <div class="spn">

      <!-- Direct message liste -->
      <div class="panel-left">
         <h4>General</h4>
         <div class="top-buttons">
             <button id="global-chat"><i class="fa-regular fa-message"></i> Global</button>
             <button onclick="window.location.href='/profile';"><i class="fa-regular fa-user"></i> Min Profil</button>
             <button onclick="window.location.href='/friends';"><i class="fa-regular fa-face-smile"></i> Venner</button>
         </div>
     
         <div class="separator"></div>
         
         <h4>Dine Samtaler</h4>
         <button id="new-conv" class="new-conv-button"><i class="fa-solid fa-plus"></i> Ny samtale</button>
     
         <div id="conv-list"></div>
      </div>


      <div class="chat">
         <div id="messages"></div>
         <div class="message-inputs">
            <input type="text" id="messageInput" placeholder="Skriv melding...">
            <button id="sendButton">Send</button>
         </div>
      </div>
      
      <div class="panel-right">
          <h3>Detaljer</h3>
          <p>Velg en samtale for å se informasjon.</p>
      </div>
   </div>
</body>
<script>
window.currentUser = {
    id: "<?php echo $_SESSION['user']['id']?>",
    username: "<?php echo $_SESSION['user']['username']?>",
    wsToken: "<?php echo $_SESSION['user']['wsToken']?>"
}
</script>
<script src="/js/wsInit.js"></script>
<script src="/js/mainScript.js"></script>
</html>
