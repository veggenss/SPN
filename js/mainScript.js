const messagesDiv = document.getElementById('messages');
const input = document.getElementById('messageInput');
const sendButton = document.getElementById('sendButton');
const newDM = document.getElementById('newDM');
const dmList = document.getElementById('DMList');
const globalEnable = document.getElementById('global-enable');
const currentUserId = window.currentUserId;
const currentUsername = window.currentUsername;
const currentProfilePictureUrl = window.currentProfilePictureUrl;

let activeChatType = "global";
let activeConvId = null;
let recipientId = "all";
let sending = false;
let ws = null;

console.log(`User Id: ${currentUserId} \nUsername: ${currentUsername} \nProfile Picture URL: ${currentProfilePictureUrl}`); //debug :3

function appendMessage(data) {
    const wrapper = document.createElement('div');
    wrapper.classList.add('message');

    const avatar = document.createElement('img');
    avatar.classList.add('avatar');
    avatar.src = data.profilePictureUrl || 'default.png';

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

    if (data.type == "direct" && (data.recipientId == currentUserId || data.userId === currentUserId) && data.message != null) {
        updatePreviewStr(data.message, data.convId);
    }

    content.appendChild(username);
    content.appendChild(text);
    wrapper.appendChild(avatar);
    wrapper.appendChild(content);

    messagesDiv.prepend(wrapper);
}

function appendSystemMessage(message) {
    appendMessage({
        username: "[System]",
        message,
        profilePictureUrl: "assets/icons/default.png"
    });
}

function updatePreviewStr(str, convId) {
    parent = document.getElementById('conversation-' + convId);
    child = parent.querySelector(".conversation-prevStr");
    if (!child) {
        console.warn("Preview child not found in conv: ", convId);
        return;
    }
    child.textContent = str;
}

document.addEventListener('DOMContentLoaded', () => {
    function init() {
        setupWebSocket();
        loadGlobalLog();
        loadConversationDiv();
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

    async function loadGlobalLog() {
        try {
            const req = await fetch('/samtalerpanett/Handler/GlobalChatHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'getGlobalLogs' })
            });
            const data = await req.json();

            messagesDiv.innerHTML = '';
            console.log("Global message data:", data);
            data.globalLog.forEach(message => {
                const standardized = {
                    userId: message.sender_id,
                    username: message.sender_name,
                    profilePictureUrl: message.sender_pfp,
                    message: message.message
                };
                appendMessage(standardized);
            })
        } 
        catch(err){
            console.error('Failed to load globalChatLogs(); ', err);
        }
    }
    
    async function loadConversationDiv(){
        try{
            const req = await fetch('/samtalerpanett/Handler/DirectMessageHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'loadConversationDiv', user_id: currentUserId })
            });
            const data = await req.json();
            
            console.log("loadConvData:", data);
            if (data.success === true && Array.isArray(data.conversations)) {
                data.conversations.forEach(conv => {
                    renderConversationList(conv);
                })
            }
        }
        catch(err){
            console.error('ConversationDiv(); ', err);
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
    

    function renderConversationList(conv) {
        if (document.getElementById('conversation-' + conv.conversation_id)) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('conversation');
        wrapper.id = 'conversation-' + conv.conversation_id;

        const recipientWrapper = document.createElement('div');
        recipientWrapper.classList.add('conversation-user');

        const recipientTextWrapper = document.createElement('div');
        recipientTextWrapper.classList.add('conversation-userText');

        const recipientUsername = document.createElement('span');
        recipientUsername.classList.add('conversation-name');
        recipientUsername.textContent = conv.recipientUsername;

        const recipientPrevStr = document.createElement('span');
        recipientPrevStr.classList.add('conversation-prevStr');
        recipientPrevStr.textContent = conv.prevStr;

        const recipientAvatar = document.createElement('img');
        recipientAvatar.classList.add('conversation-avatar');
        recipientAvatar.src = conv.recipient_profile_icon;

        recipientWrapper.appendChild(recipientAvatar);
        recipientTextWrapper.appendChild(recipientUsername);
        recipientTextWrapper.appendChild(recipientPrevStr);
        recipientWrapper.appendChild(recipientTextWrapper);
        wrapper.appendChild(recipientWrapper);

        wrapper.addEventListener('click', () => {
            activeChatType = "direct";
            activeConvId = conv.conversation_id;
            recipientId = conv.recipientId;
            loadConvLog(conv);
        });

        dmList.appendChild(wrapper);
    }

    async function loadConvLog(conv){
        try{
            const loadLogReq = await fetch('/samtalerpanett/Handler/DirectMessageHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({action: 'loadConversationLog', conversation_id: conv.conversation_id, user2_id: conv.recipientId, user1_id: currentUserId, user1_name: currentUsername, user2_name: conv.recipientUsername})
            });
            const loadLogData = await loadLogReq.json();
            
            if(loadLogData.success === false){
                alert(loadLogData.response);
                console.warn(loadLogData.response);
                return;
            }
            
            messagesDiv.innerHTML = '';
            loadLogData.messageData.forEach(message => {
                appendMessage(message, true);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            });
        }
        catch(err){
            console.error('loadConversation(); ', err);
        }
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
