<script setup>
import { ref, nextTick, onMounted, computed } from 'vue'

const props = defineProps({
    currentUser: {
        type: Object,
        default: () => ({ id: 1, name: 'You', avatar: null }),
    },
    recipient: {
        type: Object,
        default: () => ({ id: 2, name: 'Sarah', avatar: null }),
    },
    showBack: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['back'])

const messages = ref([
    {
        id: 1,
        senderId: 2,
        text: 'Hey! How are you doing?',
        time: '10:22 AM',
        read: true,
    },
    {
        id: 2,
        senderId: 1,
        text: "I'm good thanks! Just got back from lunch.",
        time: '10:24 AM',
        read: true,
    },
    {
        id: 3,
        senderId: 2,
        text: 'Nice, where did you go?',
        time: '10:24 AM',
        read: true,
    },
])

const newMessage = ref('')
const messagesEnd = ref(null)
const inputRef = ref(null)
const isTyping = ref(false)
const typingTimeout = ref(null)

const initials = (name) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
}

const avatarColor = (name) => {
    const colors = [
        'bg-violet-500',
        'bg-rose-500',
        'bg-amber-500',
        'bg-teal-500',
        'bg-sky-500',
        'bg-pink-500',
    ]
    let index = 0
    for (let i = 0; i < name.length; i++) {
        index += name.charCodeAt(i)
    }
    return colors[index % colors.length]
}

const now = () => {
    const d = new Date()
    let h = d.getHours()
    const m = d.getMinutes().toString().padStart(2, '0')
    const ampm = h >= 12 ? 'PM' : 'AM'
    h = h % 12 || 12
    return `${h}:${m} ${ampm}`
}

const scrollToBottom = async () => {
    await nextTick()
    messagesEnd.value?.scrollIntoView({ behavior: 'smooth' })
}

const sendMessage = async () => {
    const text = newMessage.value.trim()
    if (!text) return

    messages.value.push({
        id: Date.now(),
        senderId: props.currentUser.id,
        text,
        time: now(),
        read: false,
    })

    newMessage.value = ''
    await scrollToBottom()

    isTyping.value = true
    clearTimeout(typingTimeout.value)

    typingTimeout.value = setTimeout(async () => {
        isTyping.value = false
        const replies = [
            'That sounds really nice!',
            'Oh wow, tell me more.',
            'Haha yeah I get that.',
            'Makes sense to me.',
            'Interesting, I never thought about it that way.',
        ]
        messages.value.push({
            id: Date.now(),
            senderId: props.recipient.id,
            text: replies[Math.floor(Math.random() * replies.length)],
            time: now(),
            read: true,
        })
        await scrollToBottom()
    }, 1800)
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

    messages.value.forEach((msg, i) => {
        if (msg.senderId !== lastSender) {
            groups.push({ senderId: msg.senderId, items: [msg] })
            lastSender = msg.senderId
        } else {
            groups[groups.length - 1].items.push(msg)
        }
    })

    return groups
})

onMounted(() => {
    scrollToBottom()
    inputRef.value?.focus()
})
</script>

<template>
    <div class="flex flex-col h-full w-full bg-white overflow-hidden">

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
                <div
                    :class="[avatarColor(recipient.name), 'w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0']"
                >
                    {{ initials(recipient.name) }}
                </div>
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full"></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ recipient.name }}</p>
                <p class="text-xs text-green-500">Online</p>
            </div>
            <button class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4 bg-gray-50/60">
            <template v-for="group in groupedMessages" :key="group.senderId + group.items[0].id">
                <div
                    :class="[
                        'flex gap-2.5',
                        group.senderId === currentUser.id ? 'flex-row-reverse' : 'flex-row',
                    ]"
                >
                    <div
                        v-if="group.senderId !== currentUser.id"
                        :class="[avatarColor(recipient.name), 'w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0 self-end mb-1']"
                    >
                        {{ initials(recipient.name) }}
                    </div>

                    <div
                        :class="[
                            'flex flex-col gap-1 max-w-[72%]',
                            group.senderId === currentUser.id ? 'items-end' : 'items-start',
                        ]"
                    >
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

            <div v-if="isTyping" class="flex items-end gap-2.5">
                <div
                    :class="[avatarColor(recipient.name), 'w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0']"
                >
                    {{ initials(recipient.name) }}
                </div>
                <div class="bg-white border border-gray-100 shadow-sm rounded-t-2xl rounded-br-2xl rounded-bl-md px-4 py-3 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
                </div>
            </div>

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
                        class="w-full resize-none rounded-2xl bg-gray-100 border-0 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all max-h-28 overflow-y-auto"
                    ></textarea>
                </div>
                <button
                    @click="sendMessage"
                    :disabled="!newMessage.trim()"
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
