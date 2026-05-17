let chatChannel = null;
let chatChannelId = null;
let typingTimer = null;

function subscribeToChannel(channelId) {
    if (chatChannelId && chatChannelId === channelId) return;

    leaveChannel();

    if (!channelId || !window.Echo) return;

    chatChannelId = channelId;

    window.Echo.private('chat.canal.' + channelId)
        .listen('.message.sent', (data) => {
            const component = Livewire.getByName('chat.chat-drawer')[0]
                || Livewire.getByName('chat.chat-panel')[0];
            if (component) {
                component.handleIncomingMessage(data);
            }
        })
        .listen('.message.read', (data) => {
            const component = Livewire.getByName('chat.chat-drawer')[0]
                || Livewire.getByName('chat.chat-panel')[0];
            if (component) {
                component.handleReadReceipt(data);
            }
        })
        .listen('.typing', (data) => {
            const component = Livewire.getByName('chat.chat-drawer')[0]
                || Livewire.getByName('chat.chat-panel')[0];
            if (!component) return;
            if (data.typing) {
                component.handleTyping(data);
            } else {
                component.handleStopTyping(data);
            }
        });
}

function leaveChannel() {
    if (chatChannelId && window.Echo) {
        window.Echo.leave('chat.canal.' + chatChannelId);
    }
    chatChannelId = null;
}

document.addEventListener('livewire:initialized', () => {
    Livewire.on('channel-selected', (event) => {
        subscribeToChannel(event.channelId);
    });

    Livewire.on('channel-left', () => {
        leaveChannel();
    });
});

document.addEventListener('input', (e) => {
    const textarea = e.target.closest('#chat-input, #chat-input-drawer');
    if (!textarea) return;

    clearTimeout(typingTimer);

    const component = Livewire.getByName('chat.chat-drawer')[0]
        || Livewire.getByName('chat.chat-panel')[0];
    if (component) {
        component.typing();
    }

    typingTimer = setTimeout(() => {
        const comp = Livewire.getByName('chat.chat-drawer')[0]
            || Livewire.getByName('chat.chat-panel')[0];
        if (comp) {
            comp.stopTyping();
        }
    }, 1500);
});

export { subscribeToChannel, leaveChannel };