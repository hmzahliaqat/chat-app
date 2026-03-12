<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ChatBox from '@/Components/ChatBox.vue';

const users = ref([
    { id: 1, name: 'Sarah Mitchell', lastMessage: 'Hey! How are you doing?', time: '10:22 AM', unread: 2, online: true },
    { id: 2, name: 'James Carter', lastMessage: 'See you tomorrow then!', time: '9:58 AM', unread: 0, online: true },
    { id: 3, name: 'Lena Torres', lastMessage: 'Can you send me the file?', time: 'Yesterday', unread: 1, online: false },
    { id: 4, name: 'Noah Bennett', lastMessage: 'Sounds good to me 👍', time: 'Yesterday', unread: 0, online: false },
    { id: 5, name: 'Ava Simmons', lastMessage: 'I\'ll check and get back to you', time: 'Mon', unread: 0, online: true },
    { id: 6, name: 'Ethan Brooks', lastMessage: 'Did you see the game last night?', time: 'Mon', unread: 0, online: false },
    { id: 7, name: 'Mia Nguyen', lastMessage: 'Thanks a lot!', time: 'Sun', unread: 0, online: true },
    { id: 8, name: 'Lucas Rivera', lastMessage: 'Let me know when you\'re free', time: 'Sun', unread: 3, online: false },
    { id: 9, name: 'Olivia Hayes', lastMessage: 'Perfect, see you then!', time: 'Sat', unread: 0, online: false },
    { id: 10, name: 'Mason Clark', lastMessage: 'On my way!', time: 'Sat', unread: 0, online: true },
    { id: 11, name: 'Isabella Young', lastMessage: 'That makes sense now', time: 'Fri', unread: 0, online: false },
    { id: 12, name: 'Logan King', lastMessage: 'hahaha no way 😂', time: 'Fri', unread: 0, online: false },
]);

const activeUser = ref(users.value[0]);
const mobileShowChat = ref(false);

const selectUser = (user) => {
    activeUser.value = user;
    mobileShowChat.value = true;
};

const initials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const avatarColor = (name) => {
    const colors = ['bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-teal-500', 'bg-sky-500', 'bg-pink-500', 'bg-indigo-500', 'bg-orange-500'];
    let index = 0;
    for (let i = 0; i < name.length; i++) index += name.charCodeAt(i);
    return colors[index % colors.length];
};
</script>

<template>
    <Head title="Messages" />
    <div class="h-screen bg-slate-100 flex items-center justify-center md:p-4">
        <div class="w-full max-w-[1400px] h-full md:h-[calc(100vh-2rem)] flex md:rounded-2xl shadow-2xl overflow-hidden border-0 md:border border-gray-200 bg-white">

            <div
                :class="[
                    'flex-shrink-0 flex flex-col border-r border-gray-100 bg-white transition-all duration-300',
                    'w-full md:w-96',
                    mobileShowChat ? 'hidden md:flex' : 'flex',
                ]"
            >
                <div class="px-4 py-4 border-b border-gray-100">
                    <h1 class="text-lg font-bold text-gray-900">Messages</h1>
                    <div class="mt-3 relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" />
                        </svg>
                        <input
                            type="text"
                            placeholder="Search…"
                            class="w-full pl-9 pr-3 py-2 text-sm bg-gray-100 rounded-xl border-0 focus:outline-none focus:ring-2 focus:ring-indigo-300 placeholder-gray-400"
                        />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <button
                        v-for="user in users"
                        :key="user.id"
                        @click="selectUser(user)"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 transition-colors text-left',
                            activeUser.id === user.id ? 'bg-indigo-50' : 'hover:bg-gray-50',
                        ]"
                    >
                        <div class="relative flex-shrink-0">
                            <div :class="[avatarColor(user.name), 'w-11 h-11 rounded-full flex items-center justify-center text-white text-sm font-semibold']">
                                {{ initials(user.name) }}
                            </div>
                            <span v-if="user.online" class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span :class="['text-sm truncate', activeUser.id === user.id ? 'font-semibold text-indigo-700' : 'font-medium text-gray-800']">
                                    {{ user.name }}
                                </span>
                                <span class="text-[11px] text-gray-400 flex-shrink-0 ml-1">{{ user.time }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-0.5">
                                <span class="text-xs text-gray-400 truncate">{{ user.lastMessage }}</span>
                                <span v-if="user.unread > 0" class="ml-1 flex-shrink-0 min-w-[18px] h-[18px] rounded-full bg-indigo-500 text-white text-[10px] flex items-center justify-center px-1">
                                    {{ user.unread }}
                                </span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div
                :class="[
                    'flex-1 flex flex-col min-w-0',
                    mobileShowChat ? 'flex' : 'hidden md:flex',
                ]"
            >
                <ChatBox
                    :key="activeUser.id"
                    :current-user="{ id: 99, name: 'You' }"
                    :recipient="activeUser"
                    :show-back="mobileShowChat"
                    @back="mobileShowChat = false"
                />
            </div>

        </div>
    </div>
</template>
