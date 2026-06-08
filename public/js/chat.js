/* ================================================================
   StayU — chat.js  (ES Module, Firebase v10 modular CDN)

   Firebase Realtime Database data structure:
     /conversations/{firebase_session_id}/messages/{push_id}
       sender_id:   number  (users.user_id)
       sender_name: string
       text:        string
       timestamp:   number  (Unix ms, set via serverTimestamp())

   Firebase Security Rules (set in Firebase Console):
     {
       "rules": {
         "conversations": {
           "$session_id": {
             ".read":  true,
             ".write": true
           }
         }
       }
     }

   Required globals injected by the PHP view before this module:
     window.FIREBASE_CONFIG — Firebase project config object
     window.CHAT_SESSION    — firebase_session_id string
     window.CURRENT_USER    — { id: number, name: string, role: string }
   ================================================================ */

import { initializeApp }
    from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getDatabase, ref, push, onValue, serverTimestamp }
    from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-database.js';

// ── Validate required globals ─────────────────────────────────────
const config  = window.FIREBASE_CONFIG;
const session = window.CHAT_SESSION;
const me      = window.CURRENT_USER;

if (!config?.databaseURL || !session || !me?.id) {
    const el = document.querySelector('.chat-loading');
    if (el) el.textContent = 'Konfigurasi chat tidak lengkap. Hubungi admin.';
    throw new Error('[StayU Chat] Missing required globals.');
}

// ── Firebase init ─────────────────────────────────────────────────
const app  = initializeApp(config);
const db   = getDatabase(app);

// ── DOM refs ──────────────────────────────────────────────────────
const msgContainer = document.getElementById('chatMessages');
const chatForm     = document.getElementById('chatForm');
const msgInput     = document.getElementById('msgInput');
const sendBtn      = chatForm?.querySelector('button[type="submit"]');

if (msgContainer) msgContainer.innerHTML = '';

// ── DB ref: /conversations/{session}/messages ─────────────────────
const msgsRef = ref(db, `conversations/${session}/messages`);

// ── Helpers ───────────────────────────────────────────────────────

/** True when the user is within 120 px of the bottom (not scrolled up). */
function isNearBottom() {
    if (!msgContainer) return true;
    const { scrollHeight, scrollTop, clientHeight } = msgContainer;
    return scrollHeight - scrollTop - clientHeight < 120;
}

function scrollToBottom() {
    if (msgContainer) msgContainer.scrollTop = msgContainer.scrollHeight;
}

/**
 * Build and append one message bubble.
 * @param {string} key   - Firebase push key (used as data-msg-key)
 * @param {object} data  - { sender_id, sender_name, text, timestamp }
 */
function renderMessage(key, data) {
    if (!msgContainer || !data?.text) return;

    const isMine = Number(data.sender_id) === Number(me.id);

    const wrap = document.createElement('div');
    wrap.className      = 'msg ' + (isMine ? 'msg-out' : 'msg-in');
    wrap.dataset.msgKey = key;

    const textEl = document.createElement('div');
    textEl.className   = 'msg-text';
    textEl.textContent = data.text;

    const metaEl = document.createElement('div');
    metaEl.className = 'msg-meta';

    const senderLabel = isMine ? 'Anda' : (data.sender_name ?? 'Pengguna');
    const timeLabel   = data.timestamp
        ? new Date(Number(data.timestamp)).toLocaleTimeString('ms-MY', {
              hour:   '2-digit',
              minute: '2-digit',
          })
        : '';
    metaEl.textContent = senderLabel + (timeLabel ? ' · ' + timeLabel : '');

    wrap.appendChild(textEl);
    wrap.appendChild(metaEl);
    msgContainer.appendChild(wrap);
}

// ── Listen for messages ───────────────────────────────────────────
onValue(msgsRef, (snapshot) => {
    const shouldScroll = isNearBottom();

    msgContainer.innerHTML = '';

    if (snapshot.exists()) {
        snapshot.forEach((childSnap) => {
            renderMessage(childSnap.key, childSnap.val());
        });
    } else {
        const empty = document.createElement('p');
        empty.className   = 'chat-empty';
        empty.textContent = 'Tiada mesej lagi. Mulakan perbualan!';
        msgContainer.appendChild(empty);
    }

    if (shouldScroll) scrollToBottom();
}, (err) => {
    console.error('[StayU Chat] onValue error:', err);
    if (msgContainer) {
        msgContainer.innerHTML = '<p class="chat-error">Sambungan chat gagal. Muat semula halaman.</p>';
    }
});

// ── Send a message ────────────────────────────────────────────────

function sendMessage() {
    const text = msgInput?.value.trim();
    if (!text) return;

    if (msgInput)  msgInput.disabled  = true;
    if (sendBtn)   sendBtn.disabled   = true;

    push(msgsRef, {
        sender_id:   me.id,
        sender_name: me.name,
        text,
        timestamp:   serverTimestamp(),
    })
        .then(() => {
            if (msgInput) msgInput.value = '';
            scrollToBottom();
        })
        .catch((err) => {
            console.error('[StayU Chat] Send failed:', err);
            alert('Mesej gagal dihantar. Semak sambungan internet anda.');
        })
        .finally(() => {
            if (msgInput) { msgInput.disabled = false; msgInput.focus(); }
            if (sendBtn)    sendBtn.disabled  = false;
        });
}

if (chatForm) {
    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage();
    });
}

if (msgInput) {
    msgInput.addEventListener('keydown', (e) => {
        // Enter sends; Shift+Enter is a no-op (input element, no newlines anyway)
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
}
