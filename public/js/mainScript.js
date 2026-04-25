const alertCon = document.getElementById('alert-container');
const messagesDiv = document.getElementById('messages');
const input = document.getElementById('messageInput');
const sendButton = document.getElementById('sendButton');

const newConvOpen = document.getElementById('new-conv');
const newConvDialog = document.getElementById('create-conversation');
const newConvForm = document.getElementById('newConvForm');
const newConvPartyInput = document.getElementById('newConvParticipants');
const newConvPartyAdd = document.getElementById('addParticipantBtn');

const convList = document.getElementById('conv-list');
const globalChat = document.getElementById('global-chat');

const userId = window.currentUser.id;
const username = window.currentUser.username;
const wsToken = window.currentUser.wsToken;
// const currentProfilePictureUrl = window.currentProfilePictureUrl;

let participant_ids = [];
let newConvPartyCount = 0;
let userChatLogs = {}; //Stores all fetched messages
let activeConvId = null;
let sending = false;
let ws = null;

console.log(userId, username);

const getNewConvPartyCount = () => newConvPartyInput.querySelectorAll('.participant:not(.self)').length;

const chatStore = {
    public: [],
    private: {},
    
    upsertMessages(type, convId, msgs){
        if(type === 'public'){
            this.public = this.merge(this.public, msgs);
        }
        else{
            if(!convId) throw new TypeError("Undefined convId");
            this.private[convId] = this.merge(this.private[convId], msgs);
        }
    },
    
    addMessage(type, convId, msg){
        if(!msg) throw new TypeError("Undefined msg");
        
        if(type === 'public'){
            this.public = this.insertSorted(this.public, msg);
            return;
        }

        if(!convId) throw new TypeError("Undefined ConvId");
        
        if(!this.private[convId]) this.private[convId] = [];
        
        this.private[convId] = this.insertSorted(this.private[convId], msg);
    },
    
    merge(existing = [], incoming = []){
        const map = new Map();
        
        [...existing, ...incoming].forEach(msg => {
            map.set(msg.id, msg);
        });
        
        return this.sort([...map.values()]);
    },
    
    insertSorted(arr, msg){
        const newArr = [...arr, msg];
        return this.sort(newArr);
    },
    
    sort(arr){
        return [...arr].sort((a, b) => {
            if(a.date_added === b.date_added){
                return Number(a.id) - Number(b.id);
            }
            return new Date(a.date_added || a.date_sent) - new Date(b.date_added || b.date_sent);
        });
    }
};

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
        
        // toggle globalChat
        globalChat.addEventListener('click', () => {
            activeConvId = null;
            messagesDiv.innerHTML = '';
            renderMessages();
            
            document.querySelectorAll('.conversation, #global-chat')
                .forEach(el => el.classList.remove('active'));
        
            globalChat.classList.add('active');
        });

        // open newConvDialouge
        newConvOpen.addEventListener('click', () => {
            newConvPartyInput.innerHTML = `
                <div class="participant self">
                    <input type="text" value="${username}" disabled>
                </div>
            `; 
        });
        
        // close newConvDialogue
        newConvDialog.addEventListener('close', () => {
            newConvForm.reset();
            newConvPartyCount = 0;
        });
        
        // submit newConvDialogue
        newConvForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const convName = document.getElementById('convName').value;
            
            const participants = [...newConvPartyInput.querySelectorAll('input')]
                .filter(input => !input.disabled)
                .map(input => input.value.trim())
                .filter(Boolean);
            
            if (participants.length < 1) return;
            
            console.log("NewConvFormData \n", "convName: ", convName, "\n", "Parties: ", participants);
            
            makeConversation(convName, participants);
            
            newConvForm.reset();
            newConvPartyCount = 0;
        });
        
        // add/remove conv participants
        newConvPartyAdd.addEventListener('click', () => {
            if (getNewConvPartyCount() >= 9) return;
            
            const wrapper = document.createElement("div");
            wrapper.classList.add("participant");
        
            wrapper.innerHTML = `
                <input type="text" placeholder="Brukernavn" required>
                <button type="button" class="remove">X</button>
            `;
        
            wrapper.querySelector(".remove").onclick = () => {
                wrapper.remove();
                newConvPartyCount--;
            };
        
            newConvPartyInput.appendChild(wrapper);
            newConvPartyCount++;
        });
    }

    async function getUserLogs() {
        try {
            const req = await fetch('/api/get-user-logs', {method: 'POST'});
            const data = await req.json();
            if(data) console.log("Fetched!", data);
            
            chatStore.upsertMessages('public', null, data.public);
            
            data.conversations.forEach(conv => {
                chatStore.upsertMessages('private', conv.id, conv.messages);
                renderConversationList(conv);
            });
            
            renderMessages();
            console.log("public: ", chatStore.public, "\n", "private: ", chatStore.private);
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
            
            if (data.class) {
                alertCon.innerHTML = `
                    <div class="${data.class}">
                        <p>${data.message}</p>
                    </div>
                 `;
                return
            }
            
            if(data.conversation){
                getUserLogs();
            } 
        }
        catch(err){
            console.error('newConversation(); ', err)
        }
    }
    
    function renderMessages(){
        messagesDiv.innerHTML = '';
        
        let messages;
        if(activeConvId){
            messages = chatStore.private[activeConvId] || [];
        }
        else{
            messages = chatStore.public;
        }
        
        messages.forEach(msg => appendMessage(msg));
    }
    
    function renderConversationList(data) {
        console.log("render", data);
        if (document.getElementById('conversation-' + data.id)) return;

        const convWrapper = document.createElement('div');
        convWrapper.classList.add('conversation');
        convWrapper.id = 'conversation-' + data.id;

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
        convWrapper.appendChild(userWrapper);

        convWrapper.addEventListener('click', () => {
            activeConvId = data.id;
            renderMessages();
        });

        convList.appendChild(convWrapper);
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
                id: 'sys-' + Date.now(),
                username: "[System]",
                message: "Meldingen er for lang. Maks 400 tegn.",
                date_added: new Date().toISOString()
            });
            return;
        }

        const messageData = {
            username: username,
            sender_id: userId,
            conv_id: activeConvId,
            message: text,
            // profilePictureUrl: currentProfilePictureUrl,
        }

        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(messageData));
        }
        else {
            appendMessage({
                id: 'sys-' + Date.now(),
                username: "[System]",
                message: "Noe Gikk Galt!",
                date_added: new Date().toISOString()
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
            console.error("Tilkobling til websocket lukket");
            appendMessage({
                id: 'sys-' + Date.now(),
                username: "[System]",
                message: "Tilkoblingen ble lukket",
                date_added: new Date().toISOString()
            });
        }
    
        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            console.log("Incoming:", data);
        
            const isGlobalMsg = data.conv_id === null;

            if (isGlobalMsg) {
                chatStore.addMessage('public', null, data);
            }
            else{
                chatStore.addMessage('private', data.conv_id, data);
            }
            
            if((!data.conv_id && !activeConvId) || data.conv_id === activeConvId){
                renderMessages();
            }
        }
    }
    
    init();
});
