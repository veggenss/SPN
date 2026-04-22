const authCon = document.getElementsByClassName('auth-con');
const authBtn = document.getElementById('auth-button');
const alertCon = document.getElementById('alert-con');

const url = new URL(window.location.href);
const emailToken = url.searchParams.get('token');

authBtn.addEventListener('click', () => {
    authBtn.classList.add('loading');
    setTimeout(1000);
    if(emailToken){
        console.log("email Token: ", emailToken);
        if(verifyEmail(emailToken)){
            authBtn.classList.remove('loading');
            alertCon.innerHTML = `
                <div class="success">
                    <p>Epost Verifisert!</p>
                </div>
            `;
            authBtn.textContent = "Trykk her for å logge inn!";
            authBtn.location="/login";
        }
    }
});

async function verifyEmail(emailToken){
    try{
        const req = await fetch('/api/verify-email', {
            method: 'POST',
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({token: emailToken})
        });
        const data = await req.json();
        
        if (!data || data.class) {
            alertCon.innerHTML = `
                <div class="${data.class}">
                    <p>${data.message}</p>
                </div>
             `;
            return false;
        }
        return true;
    }
    catch(err){
        console.error("Error! \n", err);
    }
}