@extends('layouts.app')

@section('content')
<div class="h-screen bg-[#f3f4f6] pt-14 md:pt-6 flex items-center justify-center px-2 md:px-3">

    <div class="w-full max-w-3xl h-[88vh] bg-[#f5f5f5] rounded-2xl shadow-md border border-gray-200 flex flex-col overflow-hidden">

        <!-- HEADER -->
        <div class="bg-[#f5f5f5] px-4 py-4 border-b border-gray-200 flex items-center gap-3">

            <!-- avatar -->
            <div class="w-11 h-11 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-lg shadow shrink-0">
                {{ strtoupper(substr($customer->nama_lengkap, 0, 1)) }}
            </div>

            <!-- info -->
            <div class="min-w-0">
                <div class="text-lg font-semibold text-black leading-none truncate">
                    {{ $customer->nama_lengkap }}
                </div>

                <div class="text-sm text-gray-500 mt-1">
                    Online
                </div>
            </div>

        </div>

        <!-- CHAT BOX -->
        <div id="chatBox"
             class="flex-1 overflow-y-auto px-4 py-4 space-y-4 bg-[#f5f5f5]">
        </div>

        <!-- INPUT -->
        <div class="bg-[#f5f5f5] border-t border-gray-200 px-4 py-3">

            <div class="flex gap-2 items-center">

                <input id="messageInput"
                       type="text"
                       class="flex-1 h-11 border border-gray-300 rounded-lg px-4 text-sm bg-[#f3f3f3] focus:bg-white focus:ring-2 focus:ring-green-500 outline-none min-w-0"
                       placeholder="Ketik pesan..."
                       onkeypress="if(event.key === 'Enter') sendMessage()">

                <button onclick="sendMessage()"
                    class="bg-green-600 hover:bg-green-700 transition text-white h-11 w-11 rounded-lg flex items-center justify-center shadow shrink-0">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5"
                        fill="currentColor"
                        viewBox="0 0 16 16">

                        <path d="M15.854.146a.5.5 0 0 0-.54-.11l-15 6a.5.5 0 0 0 .034.939l5.91 1.97 1.97 5.91a.5.5 0 0 0 .939.034l6-15a.5.5 0 0 0-.313-.743ZM6.636 8.07 13.5 2.5 8.07 9.364l-.761 3.043L6.636 8.07Z"/>
                    </svg>

                </button>

            </div>

        </div>

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

            let isMe = chat.is_me;

            chatBox.innerHTML += `

                <div class="${isMe ? 'flex justify-end' : 'flex justify-start'}">

                    <div class="
                        max-w-[85%]
                        md:max-w-[75%]
                        px-4
                        py-3
                        text-sm
                        shadow-sm
                        break-words
                        ${isMe
                            ? 'bg-green-600 text-white rounded-xl rounded-br-sm'
                            : 'bg-gray-200 text-black rounded-xl rounded-bl-sm'}
                    ">

                        <p>${chat.message ?? ''}</p>

                    </div>

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