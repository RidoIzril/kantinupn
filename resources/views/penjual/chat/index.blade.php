@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-6 bg-white shadow rounded-xl p-4">

    <!-- HEADER -->
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">
            {{ strtoupper(substr($customer->nama_lengkap, 0, 1)) }}
        </div>
        <div>
            <div class="font-semibold text-lg">{{ $customer->nama_lengkap }}</div>
            <div class="text-sm text-gray-500">Online</div>
        </div>
    </div>

    <!-- CHAT BOX -->
    <div id="chatBox" class="h-80 overflow-y-auto border rounded p-3 mb-3 bg-gray-50"></div>

    <!-- INPUT -->
    <div class="flex gap-2">
        <input id="messageInput" type="text"
            class="flex-1 border rounded px-3 py-2"
            placeholder="Ketik pesan..."
            onkeypress="if(event.key === 'Enter') sendMessage()">

        <button onclick="sendMessage()"
            class="bg-blue-600 text-white px-4 rounded hover:bg-blue-700">
            Kirim
        </button>
    </div>
</div>

<script>

let receiverId = {{ $customer->users_id }};
let token = "{{ request('token') }}";

// =======================
// LOAD CHAT
// =======================
function loadChat() {
    fetch(`/penjual/chat/get/${receiverId}?token=${token}`)
    .then(res => res.json())
    .then(data => {

        let chatBox = document.getElementById('chatBox');
        chatBox.innerHTML = '';

        data.forEach(chat => {

            // 🔥 FIX FINAL: pakai dari backend
            let isMe = chat.is_me;

            chatBox.innerHTML += `
                <div class="${isMe ? 'text-right' : 'text-left'} mb-2">
                    <span class="${isMe ? 'bg-blue-500 text-white' : 'bg-gray-300'} px-3 py-1 rounded inline-block">
                        ${chat.message ?? ''}
                    </span>
                </div>
            `;
        });

        chatBox.scrollTop = chatBox.scrollHeight;
    })
    .catch(err => console.log("LOAD ERROR:", err));
}

// =======================
// SEND MESSAGE
// =======================
function sendMessage() {
    let input = document.getElementById('messageInput');
    let msg = input.value;

    if (!msg.trim()) return;

    fetch(`/penjual/chat/send?token=${token}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            receiver_id: receiverId,
            message: msg
        })
    })
    .then(res => res.json())
    .then(() => {
        input.value = '';
        loadChat();
    })
    .catch(err => console.log("SEND ERROR:", err));
}

// INIT
loadChat();
setInterval(loadChat, 2000);

</script>
@endsection