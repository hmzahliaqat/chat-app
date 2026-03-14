<script setup>
import { ref, computed } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import ChatBox from '@/Components/ChatBox.vue';

const profileOpen = ref(false);

const logout = () => {
    router.post('/logout');
};

const toggleProfile = (e) => {
    e.stopPropagation();
    profileOpen.value = !profileOpen.value;
};

const closeProfile = () => { profileOpen.value = false; };

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const users = ref(props.users);
const activeUser = ref(users.value[0] ?? null);
const mobileShowChat = ref(false);
const search = ref('');

const filteredUsers = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return users.value;
    return users.value.filter(u =>
        u.name.toLowerCase().includes(q) ||
        u.lastMessage.toLowerCase().includes(q)
    );
});

const selectUser = (user) => {
    activeUser.value = user;
    mobileShowChat.value = true;
    user.unread = 0;
};

const onMessageSent = (message) => {
    const user = users.value.find(u => u.id === activeUser.value.id);
    if (user) {
        user.lastMessage = message.text;
        user.time = message.time;
    }
};

const onChatCleared = () => {
    const user = users.value.find(u => u.id === activeUser.value.id);
    if (user) {
        user.lastMessage = 'No messages yet';
        user.time = '';
        user.unread = 0;
    }
};

const initials = (name) => name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);

const avatarColor = (name) => {
    const colors = ['bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-teal-500', 'bg-sky-500', 'bg-pink-500', 'bg-indigo-500', 'bg-orange-500'];
    let index = 0;
    for (let i = 0; i < name.length; i++) index += name.charCodeAt(i);
    return colors[index % colors.length];
};
</script>

<template>
    <Head title="Messages" />
    <div class="h-screen bg-slate-100 flex items-center justify-center md:p-4" @click="closeProfile">
        <div class="w-full max-w-[1400px] h-full md:h-[calc(100vh-2rem)] flex md:rounded-2xl shadow-2xl overflow-hidden border-0 md:border border-gray-200 bg-white">

            <div
                :class="[
                    'flex-shrink-0 flex flex-col border-r border-gray-100 bg-white',
                    'w-full md:w-96',
                    mobileShowChat ? 'hidden md:flex' : 'flex',
                ]"
            >
                <div class="px-4 py-3 border-b border-indigo-100 flex items-center justify-between bg-indigo-600">
                    <span class="text-sm font-semibold text-white tracking-wide">ChatApp</span>
                    <div class="relative">
                        <button
                            @click="toggleProfile"
                            class="flex items-center gap-2 rounded-full focus:outline-none focus:ring-2 focus:ring-white/50 hover:opacity-90 transition-opacity"
                        >
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white/20 border-2 border-white/60 text-white text-xs font-semibold">
                                {{ initials(currentUser.name) }}
                            </div>
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
                                v-if="profileOpen"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden"
                                @click.stop
                            >
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ currentUser.name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ currentUser.email }}</p>
                                </div>
                                <button
                                    @click="logout"
                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                    </svg>
                                    Logout
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>

                <div class="px-4 py-4 border-b border-gray-100">
                    <h1 class="text-lg font-bold text-gray-900">Messages</h1>
                    <div class="mt-3 relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search…"
                            class="w-full pl-9 pr-3 py-2 text-sm bg-gray-100 rounded-xl border-0 focus:outline-none focus:ring-2 focus:ring-indigo-300 placeholder-gray-400"
                        />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div v-if="filteredUsers.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 gap-2 px-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        <p class="text-sm">No other users yet</p>
                    </div>

                    <button
                        v-for="user in filteredUsers"
                        :key="user.id"
                        @click="selectUser(user)"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 transition-colors text-left',
                            activeUser?.id === user.id ? 'bg-indigo-50' : 'hover:bg-gray-50',
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
                                <span :class="['text-sm truncate', activeUser?.id === user.id ? 'font-semibold text-indigo-700' : 'font-medium text-gray-800']">
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
                    v-if="activeUser && currentUser"
                    :key="activeUser.id"
                    :current-user="currentUser"
                    :recipient="activeUser"
                    :show-back="mobileShowChat"
                    @back="mobileShowChat = false"
                    @message-sent="onMessageSent"
                    @chat-cleared="onChatCleared"
                />

                <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400 gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                    <p class="text-sm">Select a conversation</p>
                </div>
            </div>

        </div>
    </div>
</template>
