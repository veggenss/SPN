const messagesDiv = document.getElementById('messages');
const input = document.getElementById('messageInput');
const sendButton = document.getElementById('sendButton');
const newDM = document.getElementById('newDM');
const dmList = document.getElementById('DMList');
const globalEnable = document.getElementById('global-enable');
const currentUserId = window.currentUserId;
const currentUsername = window.currentUsername;
const currentProfilePictureUrl = window.currentProfilePictureUrl;

let privateMessages = [];
let activeChatType = "global";
let activeConvId = null;
let recipientId = "all";
let sending = false;
let ws = null;

// function updatePreviewStr(str, convId) {
//     parent = document.getElementById('conversation-' + convId);
//     child = parent.querySelector(".conversation-prevStr");
//     if (!child) {
//         console.warn("Preview child not found in conv: ", convId);
//         return;
//     }
//     child.textContent = str;
// }

document.addEventListener('DOMContentLoaded', () => {
    function init() {
        // setupWebSocket();
        getChat();
        setupEventListeners();
    }

    function setupEventListeners() {
        sendButton.onclick = sendMessage;

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        newDM.addEventListener('click', () => {
            newConversation();
        });

        globalEnable.addEventListener('click', () => {
            activeChatType = "global";
            recipientId = "all";
            loadGlobalLog();
        });
    }

    async function getChat() {
        try {
            const req = await fetch('/api/get-chat');
            const data = await req.json();
            if(data) console.log("Fetched!", data);
            data.public.forEach(message => appendMessage(message));
            if(data.conversations){
                data.coversations.forEach(conv => {
                    privateMessages[conv.id] = data.private.filer(pm => pm.conversation_id === conv.id);
                    renderConversationList(conv);
                });
            }
        } 
        catch(err){
            console.error('Failed to getChat ', err);
        }
    }
    
    async function newConversation(){
        try{
            const reciverUser = prompt('Skriv in brukernavn til bruker du vil ha samtale med');
            if (!reciverUser) {
                return;
            }
            if (currentUsername === reciverUser) {
                alert('Du kan ikke starte samtale med degselv');
                return;
            }
            
            const reciverUserReq = await fetch('/samtalerpanett/Handler/DirectMessageHandler.php?action=getUserId&reciverUser=' + encodeURIComponent(reciverUser));
            const reciverUserData = await reciverUserReq.json();
            if(reciverUserData.success === false){
                alert(reciverUserData.response);
                console.warn(reciverUserData.response);
                return;
            }
            
            const createConversationReq = await fetch('/samtalerpanett/Handler/DirectMessageHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({action: 'createConversation', user1_id: currentUserId, user2_id: reciverUserData.reciverUserId })
            });
            const createConversationData = await createConversationReq.json();
            if(createConversationData.success === false){
                alert(createConversationData.response);
                console.warn(createConversationData.response);
                return;
            }
            console.log('createConversationData', createConversationData);
            alert(createConversationData.response);
            loadConversationDiv();            
        }
        catch(err){
            console.error('newConversation(); ', err)
        }
    }
    
    function appendSystemMessage(message) {
        appendMessage({
            username: "[System]",
            message,
            profilePictureUrl: "assets/icons/default.png"
        });
    }
    
    function appendMessage(data) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('message');
    
        const avatar = document.createElement('img');
        avatar.classList.add('avatar');
        avatar.src = data.profilePictureUrl || '/assets/icons/default.png';
    
        const content = document.createElement('div');
        content.classList.add('message-content');
    
        const username = document.createElement('span');
        username.classList.add('username');
        username.textContent = data.username || 'Ukjent';
    
        const text = document.createElement('div');
        text.classList.add('text');
        text.textContent = data.message;
    
        if (data.username === "[System]") {
            text.style.color = "#8B193C";
            username.style.color = "#8B193C";
            wrapper.style.backgroundColor = "#FFF1F2";
        }
    
        if (data.userId == currentUserId) {
            wrapper.style.backgroundColor = "#E9E9FF";
            wrapper.style.flexDirection = "row-reverse";
            wrapper.style.textAlign = "right";
            wrapper.style.marginLeft = "auto";
        }
    
        content.appendChild(username);
        content.appendChild(text);
        wrapper.appendChild(avatar);
        wrapper.appendChild(content);
    
        messagesDiv.prepend(wrapper);
    }
    
    function renderConversationList(data) {
        if (document.getElementById('conversation-' + data.conversation_id)) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('conversation');
        wrapper.id = 'conversation-' + data.id;

        const recipientWrapper = document.createElement('div');
        recipientWrapper.classList.add('conversation-user');

        const recipientTextWrapper = document.createElement('div');
        recipientTextWrapper.classList.add('conversation-userText');

        const recipientUsername = document.createElement('span');
        recipientUsername.classList.add('conversation-name');
        recipientUsername.textContent = data.user2_username;

        const recipientPrevStr = document.createElement('span');
        recipientPrevStr.classList.add('conversation-prevStr');
        recipientPrevStr.textContent = data.latest_message;

        const recipientAvatar = document.createElement('img');
        recipientAvatar.classList.add('conversation-avatar');
        recipientAvatar.src = data.recipient_profile_icon;

        recipientWrapper.appendChild(recipientAvatar);
        recipientTextWrapper.appendChild(recipientUsername);
        recipientTextWrapper.appendChild(recipientPrevStr);
        recipientWrapper.appendChild(recipientTextWrapper);
        wrapper.appendChild(recipientWrapper);

        wrapper.addEventListener('click', () => {
            messagesDiv.innerHTML = '';
            activeChatType = "direct";
            activeConvId = data.id;
            privateMessages[data.id].forEach(message => appendMessage(message));
        });

        dmList.appendChild(wrapper);
    }

    function sendMessage() {
        console.log("Sendt melding (sendMessage())");
        if (sending) return;
        sending = true;

        const text = input.value.trim();
        if (text === '') {
            sending = false;
            return;
        }

        if (text.length > 400) {
            sending = false;
            appendSystemMessage("Meldingen er for lang. Maks 400 tegn.");
            return;
        }

        const messageData = {
            recipientId: recipientId,
            type: activeChatType,
            username: currentUsername,
            userId: currentUserId,
            message: text,
            profilePictureUrl: currentProfilePictureUrl,
        };

        // console.log(messageData);

        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(messageData));
        }
        else {
            appendSystemMessage("WebSocket er frakoblet.");
        }

        input.value = '';
        setTimeout(() => { sending = false; }, 100);
    }

    init();
});
