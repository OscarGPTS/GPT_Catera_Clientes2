let chatChannel = null;
let typingTimer = null;

function subscribeToChannel(channelId) {
    if (chatChannel) {
        window.Echo.leave('chat.canal.' + chatChannel);
    }

    if (!channelId || !window.Echo) return;

    chatChannel = channelId;

    window.Echo.channel('chat.canal.' + channelId)
        .listen('.message.sent', (data) => {
            Livewire.dispatch('echoMessageSent', data);
        })
        .listen('.message.read', (data) => {
            Livewire.dispatch('echoMessageRead', data);
        })
        .listen('.typing', (data) => {
            if (data.typing) {
                Livewire.dispatch('echoTyping', data);
            } else {
                Livewire.dispatch('echoStopTyping', data);
            }
        });
}

function leaveChannel() {
    if (chatChannel) {
        window.Echo.leave('chat.canal.' + chatChannel);
    }
    chatChannel = null;
}

document.addEventListener('livewire:initialized', () => {
    Livewire.on('channel-selected', (event) => {
        subscribeToChannel(event.channelId);
    });

    Livewire.on('channel-left', () => {
        leaveChannel();
    });
});

// Typing indicator debounce
document.addEventListener('input', (e) => {
    const textarea = e.target.closest('#chat-input');
    if (!textarea) return;

    clearTimeout(typingTimer);

    Livewire.dispatch('typing');

    typingTimer = setTimeout(() => {
        Livewire.dispatch('stop-typing');
    }, 1500);
});

export { subscribeToChannel, leaveChannel };
