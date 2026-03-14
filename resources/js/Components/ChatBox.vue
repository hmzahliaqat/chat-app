<script setup>
import { ref, nextTick, onMounted, onUnmounted, watch, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
    currentUser: {
        type: Object,
        required: true,
    },
    recipient: {
        type: Object,
        required: true,
    },
    showBack: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['back', 'messageSent', 'chatCleared'])

const messages = ref([])
const newMessage = ref('')
const messagesEnd = ref(null)
const inputRef = ref(null)
const loading = ref(false)
const sending = ref(false)
const optionsOpen = ref(false)

const toggleOptions = (e) => {
    e.stopPropagation()
    optionsOpen.value = !optionsOpen.value
}

const clearChat = async () => {
    optionsOpen.value = false
    await axios.delete(`/messages/${props.recipient.id}`)
    messages.value = []
    emit('chatCleared')
}

const initials = (name) => name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)

const avatarColor = (name) => {
    const colors = ['bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-teal-500', 'bg-sky-500', 'bg-pink-500', 'bg-indigo-500', 'bg-orange-500']
    let index = 0
    for (let i = 0; i < name.length; i++) index += name.charCodeAt(i)
    return colors[index % colors.length]
}

const scrollToBottom = async () => {
    await nextTick()
    messagesEnd.value?.scrollIntoView({ behavior: 'smooth' })
}

const fetchMessages = async () => {
    loading.value = true
    try {
        const { data } = await axios.get(`/messages/${props.recipient.id}`)
        messages.value = data
        await scrollToBottom()
    } finally {
        loading.value = false
    }
}

const sendMessage = async () => {
    const text = newMessage.value.trim()
    if (!text || sending.value) return

    sending.value = true

    const optimistic = {
        id: `temp-${Date.now()}`,
        senderId: props.currentUser.id,
        text,
        time: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }),
        read: false,
    }

    messages.value.push(optimistic)
    newMessage.value = ''
    await scrollToBottom()

    try {
        const { data } = await axios.post('/messages', {
            receiver_id: props.recipient.id,
            body: text,
        })

        const idx = messages.value.findIndex(m => m.id === optimistic.id)
        if (idx !== -1) messages.value[idx] = data

        emit('messageSent', data)
    } catch {
        messages.value = messages.value.filter(m => m.id !== optimistic.id)
        newMessage.value = text
    } finally {
        sending.value = false
    }
}

const onKeydown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        sendMessage()
    }
}

const groupedMessages = computed(() => {
    const groups = []
    let lastSender = null

    messages.value.forEach((msg) => {
        if (msg.senderId !== lastSender) {
            groups.push({ senderId: msg.senderId, items: [msg] })
            lastSender = msg.senderId
        } else {
            groups[groups.length - 1].items.push(msg)
        }
    })

    return groups
})

const subscribeToChannel = () => {
    window.Echo.private(`chat.${props.currentUser.id}`)
        .listen('.message.sent', async (data) => {
            if (data.senderId === props.recipient.id) {
                messages.value.push(data)
                await scrollToBottom()
            }
        })
}

const unsubscribeFromChannel = () => {
    window.Echo.leave(`chat.${props.currentUser.id}`)
}

watch(() => props.recipient.id, fetchMessages)

onMounted(() => {
    fetchMessages()
    subscribeToChannel()
    inputRef.value?.focus()
})

onUnmounted(() => {
    unsubscribeFromChannel()
})
</script>

<template>
    <div class="flex flex-col h-full w-full bg-white overflow-hidden" @click="optionsOpen = false">

        <div class="flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-100">
            <button
                v-if="showBack"
                @click="emit('back')"
                class="mr-1 p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
            <div class="relative">
                <div :class="[avatarColor(recipient.name), 'w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0']">
                    {{ initials(recipient.name) }}
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ recipient.name }}</p>
                <p class="text-xs text-gray-400">{{ messages.length }} messages</p>
            </div>
            <div class="relative">
                <button
                    @click="toggleOptions"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-50"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                </button>

                <Transition
                    enter-active-class="transition ease-out duration-100"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="optionsOpen"
                        class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden"
                        @click.stop
                    >
                        <button
                            @click="clearChat"
                            class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Clear Chat
                        </button>
                    </div>
                </Transition>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4 bg-gray-50/60">

            <div v-if="loading" class="flex items-center justify-center h-full">
                <div class="flex gap-1.5">
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></span>
                </div>
            </div>

            <template v-else>
                <div
                    v-if="messages.length === 0"
                    class="flex flex-col items-center justify-center h-full text-gray-400 gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                    <p class="text-sm">Say hello to {{ recipient.name }}!</p>
                </div>

                <template v-for="group in groupedMessages" :key="group.senderId + '-' + group.items[0].id">
                    <div :class="['flex gap-2.5', group.senderId === currentUser.id ? 'flex-row-reverse' : 'flex-row']">
                        <div
                            v-if="group.senderId !== currentUser.id"
                            :class="[avatarColor(recipient.name), 'w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0 self-end mb-1']"
                        >
                            {{ initials(recipient.name) }}
                        </div>

                        <div :class="['flex flex-col gap-1 max-w-[72%]', group.senderId === currentUser.id ? 'items-end' : 'items-start']">
                            <div
                                v-for="(msg, idx) in group.items"
                                :key="msg.id"
                                :class="[
                                    'px-3.5 py-2 text-sm leading-relaxed',
                                    group.senderId === currentUser.id
                                        ? 'bg-indigo-500 text-white'
                                        : 'bg-white text-gray-800 shadow-sm border border-gray-100',
                                    idx === 0 && group.senderId === currentUser.id
                                        ? 'rounded-t-2xl rounded-bl-2xl rounded-br-md'
                                        : idx === group.items.length - 1 && group.senderId === currentUser.id
                                        ? 'rounded-b-2xl rounded-tl-2xl rounded-tr-md'
                                        : group.senderId === currentUser.id
                                        ? 'rounded-l-2xl rounded-r-md'
                                        : idx === 0
                                        ? 'rounded-t-2xl rounded-br-2xl rounded-bl-md'
                                        : idx === group.items.length - 1
                                        ? 'rounded-b-2xl rounded-tr-2xl rounded-tl-md'
                                        : 'rounded-r-2xl rounded-l-md',
                                    typeof msg.id === 'string' ? 'opacity-70' : '',
                                ]"
                            >
                                {{ msg.text }}
                            </div>

                            <span class="text-[11px] text-gray-400 px-1">
                                {{ group.items[group.items.length - 1].time }}
                            </span>
                        </div>
                    </div>
                </template>
            </template>

            <div ref="messagesEnd"></div>
        </div>

        <div class="px-4 py-3 bg-white border-t border-gray-100">
            <div class="flex items-end gap-2">
                <div class="flex-1 relative">
                    <textarea
                        ref="inputRef"
                        v-model="newMessage"
                        @keydown="onKeydown"
                        placeholder="Type a message…"
                        rows="1"
                        :disabled="sending"
                        class="w-full resize-none rounded-2xl bg-gray-100 border-0 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all max-h-28 overflow-y-auto disabled:opacity-60"
                    ></textarea>
                </div>
                <button
                    @click="sendMessage"
                    :disabled="!newMessage.trim() || sending"
                    class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-full bg-indigo-500 hover:bg-indigo-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white translate-x-px" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.478 2.405a.75.75 0 0 0-.926.94l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.405Z" />
                    </svg>
                </button>
            </div>
            <p class="text-[11px] text-gray-400 text-center mt-2">Press Enter to send</p>
        </div>
    </div>
</template>
