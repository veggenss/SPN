const authBtn = document.getElementById('auth-button');
const alertCon = document.getElementById('alert-con');

const url = new URL(window.location.href);
const emailToken = url.searchParams.get('token');

authBtn.addEventListener('click', handleAuthClick);

async function handleAuthClick() {
    setLoading(true);

    if (!emailToken) {
        showAlert('error', 'Ingen token funnet!');
        setLoading(false);
        return;
    }

    console.log("email token:", emailToken);

    try {
        const ok = await verifyEmail(emailToken);

        if (ok) {
            showAlert('success', 'E-post verifisert!');
            authBtn.textContent = "Trykk her for å logge inn";
            setBtnRedirect();
        }
    }
    catch (err) {
        console.error(err);
        showAlert('error', 'Noe gikk galt ved verifisering.');
    }
    finally {
        setLoading(false);
    }
}

async function verifyEmail(token) {
    const res = await fetch('/api/verify-email', {
        method: 'POST',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ token })
    });

    const data = await res.json();

    if (!res.ok || data?.class === "error") {
        showAlert(data?.class || 'error', data?.message || 'Ukjent feil');
        return false;
    }

    return true;
}

function showAlert(type, message) {
    alertCon.innerHTML = `
        <div class="${type}">
            <p>${message}</p>
        </div>
    `;
}

function setLoading(state) {
    authBtn.classList.toggle('loading', state);
    authBtn.disabled = state;
}

function setBtnRedirect() {
    authBtn.addEventListener('click', () => {
        window.location.href = '/login';
    });
}