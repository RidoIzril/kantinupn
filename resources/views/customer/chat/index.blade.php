@extends('layouts.app')

@section('content')
<div class="flex flex-col h-screen bg-gray-50 pt-14 md:pt-6">

    <!-- HEADER -->
    <div class="bg-white px-4 py-3 border-b shadow-sm">
        <h2 class="text-sm font-semibold text-gray-700">
            Chat dengan {{ $penjual->tenant->tenant_name ?? 'Penjual' }}
        </h2>
    </div>

    <!-- CHAT BOX -->
    <div id="chatBox"
         class="flex-1 overflow-y-auto px-3 py-2 space-y-2 pb-20">
    </div>

    <!-- INPUT -->
    <div class="sticky bottom-0 bg-white border-t px-3 py-2">
        <div class="flex gap-2">
            <input id="message"
                   type="text"
                   class="flex-1 border rounded-full px-4 py-2 text-sm bg-gray-100 focus:bg-white focus:ring-2 focus:ring-green-500 outline-none"
                   placeholder="Ketik pesan...">

            <button id="sendBtn"
                    class="bg-green-600 text-white px-4 py-2 rounded-full text-sm">
                Kirim
            </button>
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const token = "{{ $token }}";
    const myId = "{{ $myId }}";
    const userId = "{{ $penjual->users_id }}";

    const chatBox = document.getElementById("chatBox");
    const input = document.getElementById("message");
    const sendBtn = document.getElementById("sendBtn");

    if (!chatBox || !input || !sendBtn) return; // 🔥 anti crash


    // ENTER = KIRIM
    input.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            sendMessage();
        }
    });

    sendBtn.addEventListener("click", sendMessage);


    function loadChat() {
        fetch(`/customer/customer/chat/get/${userId}?token=${token}`)
        .then(res => res.json())
        .then(data => {

            chatBox.innerHTML = '';

            data.forEach(chat => appendMessage(chat));

        })
        .catch(err => console.log("LOAD ERROR:", err));
    }


    function appendMessage(chat) {
        let isMe = chat.sender_id == myId;

        let div = document.createElement('div');
        div.className = isMe ? 'flex justify-end' : 'flex justify-start';

        div.innerHTML = `
            <div class="max-w-[75%] px-3 py-2 text-sm shadow
                ${isMe 
                    ? 'bg-green-500 text-white rounded-2xl rounded-br-sm' 
                    : 'bg-white text-gray-800 rounded-2xl rounded-bl-sm border'}">

                <p>${chat.message ?? ''}</p>

                <div class="text-[10px] mt-1 text-right opacity-60">
                    ${new Date(chat.created_at ?? Date.now()).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </div>
            </div>
        `;

        chatBox.appendChild(div);

        chatBox.scrollTop = chatBox.scrollHeight;
    }


    function sendMessage() {
    let msg = input.value.trim();
    if (!msg) return;

    appendMessage({
        sender_id: myId,
        message: msg,
        created_at: new Date()
    });

    input.value = '';

    let formData = new FormData();
    formData.append('receiver_id', userId);
    formData.append('message', msg);

    fetch(`/customer/customer/chat/send?token=${token}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log("SUCCESS:", data);
    })
    .catch(err => {
        console.log("SEND ERROR:", err);
    });
}


    // realtime
    loadChat();
    setInterval(loadChat, 2000);

});
</script>

@endsection