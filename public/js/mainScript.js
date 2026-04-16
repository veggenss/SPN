const messagesDiv = document.getElementById('messages');
const input = document.getElementById('messageInput');
const sendButton = document.getElementById('sendButton');

const newConvBtn = document.getElementById('new-conv');
const newConvOverlay = document.getElementById("newConvOverlay");
const newConvPartyInput = document.getElementById('newConvParticipants');
const newConvPartyAdd = document.getElementById('addParticipantBtn');
const newConvForm = document.getElementById('newConvForm');

const closeOverlayBtn = document.getElementById("closeOverlay");

const convList = document.getElementById('conv-list');
const globalChat = document.getElementById('global-chat');

const userId = window.currentUser.id;
const username = window.currentUser.username;
const wsToken = window.currentUser.wsToken;
// const currentProfilePictureUrl = window.currentProfilePictureUrl;

let participants_id = [];
let convParticipantsCount = 0;
let userChatLogs = {}; //Stores all fetched messages
let activeConvId = null;
let sending = false;
let ws = null;

console.log(userId, username);

document.addEventListener('DOMContentLoaded', () => {
    function init() {
        websocketConn();
        getUserLogs();
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
        
        globalChat.addEventListener('click', () => {
            activeConvId = null;
            messagesDiv.innerHTML = '';
            renderUserChatLog();
        });
        
        newConvBtn.addEventListener('click', openNewConvModal);
        
        closeOverlayBtn.addEventListener('click', closeNewConvModal);
        
        newConvPartyAdd.addEventListener('click', () => {
            if (convParticipantsCount >= 10) return;
            
            const wrapper = document.createElement("div");
            wrapper.classList.add("participant");
        
            wrapper.innerHTML = `
                <input type="text" placeholder="Brukernavn" required>
                <button type="button" class="remove">X</button>
            `;
        
            wrapper.querySelector(".remove").onclick = () => {
                wrapper.remove();
                convParticipantsCount--;
            };
        
            newConvPartyInput.appendChild(wrapper);
            convParticipantsCount++;
        });
        
        newConvForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const convName = document.getElementById('convName').value;
            
            const participants = [...newConvPartyInput.querySelectorAll('input')]
                .filter(input => !input.disabled)
                .map(input => input.value.trim())
                .filter(Boolean);
            
            if(participants.length < 1) return;
            
            makeConversation(convName, participants);
            
            newConvForm.reset();
            newConvPartyInput.innerHTML = "";
            count = 0;
            
            closeNewConvModal();
        });
        
        globalChat.addEventListener('click', () => {
            activeConvId = null;
            messagesDiv.innerHTML = '';
            renderUserChatLog();
        
            document.querySelectorAll('.conversation, #global-chat')
                .forEach(el => el.classList.remove('active'));
        
            globalChat.classList.add('active');
        });
        
        wrapper.addEventListener('click', () => {
            renderUserChatLog(data.id, data.participants_id);
            activeConvId = data.id;
        
            document.querySelectorAll('.conversation, #global-chat')
                .forEach(el => el.classList.remove('active'));
        
            wrapper.classList.add('active');
        });
    }

    async function getUserLogs() {
        try {
            const req = await fetch('/api/get-user-logs', {method: 'POST'});
            const data = await req.json();
            if(data) console.log("Fetched!", data);
            
            userChatLogs.public = data.public;
            renderUserChatLog();
            
            if(data.conversations.length > 0){
                userChatLogs.private = {}
                data.conversations.forEach(conv => {
                    userChatLogs.private[conv.id] = conv.messages;
                    renderConversationList(conv);
                });
            }
            console.log(userChatLogs);
        }
        catch(err){
            console.error('Failed to getChat ', err);
        }
    }
        
    async function makeConversation(title, participants) {
        try{
            const req = await fetch('/api/make-conv', {
                method: 'POST',
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({title: title, parties: participants})
            });
            const data = await req.json();
            
            if(data.conversation){
                getUserLogs();
            } 
        }
        catch(err){
            console.error('newConversation(); ', err)
        }
    }
    
    function appendMessage(data) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('message');
    
        if (data.sender_id == userId) {
            wrapper.classList.add('self');
        }
        
        if (data.username === "[System]") {
            wrapper.classList.add('system');
            wrapper.textContent = data.message || '';
            messagesDiv.appendChild(wrapper);
            return;
        }
        
        const avatar = document.createElement('img');
        avatar.classList.add('avatar');
        avatar.src = data.profilePictureUrl || '/assets/icons/default.png';
    
        const content = document.createElement('div');
        content.classList.add('message-content');
    
        const usernameSpan = document.createElement('span');
        usernameSpan.classList.add('username');
        usernameSpan.textContent = data.username || data.sender_username || 'Ukjent';
    
        const textDiv = document.createElement('div');
        textDiv.classList.add('text');
        textDiv.textContent = data.message || data[0];
        
        content.appendChild(usernameSpan);
        content.appendChild(textDiv);

        wrapper.appendChild(avatar);
        wrapper.appendChild(content);

        messagesDiv.appendChild(wrapper);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    
    function renderConversationList(data) {
        console.log("render", data);
        if (document.getElementById('conversation-' + data.id)) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('conversation');
        wrapper.id = 'conversation-' + data.id;

        const userWrapper = document.createElement('div');
        userWrapper.classList.add('conversation-user');

        const textWrapper = document.createElement('div');
        textWrapper.classList.add('conversation-userText');

        const username = document.createElement('span');
        username.classList.add('conversation-name');
        username.textContent = data.title || data.participants[0]; //should be data.title

        const prevStr = document.createElement('span');
        prevStr.classList.add('conversation-prevStr');
        prevStr.textContent = data.latest_message;

        const icon = document.createElement('img');
        icon.classList.add('conversation-avatar');
        icon.src = '/assets/icons/default.png'; //should be data.icon

        userWrapper.appendChild(icon);
        textWrapper.appendChild(username);
        textWrapper.appendChild(prevStr);
        userWrapper.appendChild(textWrapper);
        wrapper.appendChild(userWrapper);

        wrapper.addEventListener('click', () => {
            renderUserChatLog(data.id, data.participants_id);
            activeConvId = data.id;
        });

        convList.appendChild(wrapper);
    }
    
    function renderUserChatLog(convId, parties) {
        messagesDiv.innerHTML = '';
        if(!(convId || parties)){
            participants_id = [];
            userChatLogs.public.forEach(msg => appendMessage(msg));
            return;
        }
        participants_id = parties;
        userChatLogs.private[convId].forEach(msg => appendMessage(msg));
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
            appendMessage({
                username: "[System]",
                message: "Meldingen er for lang. Maks 400 tegn."
            });
            return;
        }

        const messageData = {
            username: username,
            sender_id: userId,
            participants_id: participants_id,
            message: text,
            // profilePictureUrl: currentProfilePictureUrl,
        }

        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(messageData));
        }
        else {
            appendMessage({ 
                    username: "[System]",
                    message: "Noe Gikk Galt!"
                });
        }

        input.value = '';
        setTimeout(() => { sending = false; }, 1000);
    }
    
    function websocketConn() {
        ws = new WebSocket(`ws://127.0.0.1:9501?token=${wsToken}`);
        console.log("token: ", wsToken);
        ws.onopen = () => {
            console.log("Tilkobling til websocket åpnet");
        }
    
        ws.onclose = () => {
            console.log("Tilkobling til websocket lukket");
            appendMessage({ 
                    username: "[System]",
                    message: "Tilkoblingen ble lukket"
                });
        }
    
        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            console.log("Incoming:", data);
        
            const isGlobalMsg = data.participants_id.length === 0;

            if (isGlobalMsg) {
                if (!userChatLogs.public) userChatLogs.public = [];
                userChatLogs.public.push(data);
            } 
            else {
                if (!userChatLogs.private){
                    userChatLogs.private = {};
                }
                
                if (!userChatLogs.private[data.conv_id]) {
                    userChatLogs.private[data.conv_id] = [];
                }
        
                userChatLogs.private[data.conv_id].push(data);
            }
            
            if (data.conv_id === activeConvId) {
                appendMessage(data);
                return;
            }
        }
    }
    
    init();
});
