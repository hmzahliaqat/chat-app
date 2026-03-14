<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;

class ChatService
{
    public function getUsersWithMeta(int $currentUserId): Collection
    {
        return User::where('id', '!=', $currentUserId)
            ->get()
            ->map(function (User $user) use ($currentUserId) {
                $last = Message::conversation($currentUserId, $user->id)
                    ->latest()
                    ->first();

                return [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'online'      => false,
                    'unread'      => $this->unreadCount($currentUserId, $user->id),
                    'lastMessage' => $last?->body ?? 'No messages yet',
                    'time'        => $last?->created_at->format('g:i A') ?? '',
                ];
            });
    }

    public function getConversation(int $userId1, int $userId2): Collection
    {
        return Message::conversation($userId1, $userId2)
            ->orderBy('created_at')
            ->get()
            ->map(fn(Message $m) => [
                'id'       => $m->id,
                'senderId' => $m->sender_id,
                'text'     => $m->body,
                'time'     => $m->created_at->format('g:i A'),
                'read'     => $m->read_at !== null,
            ]);
    }

    public function send(int $senderId, int $receiverId, string $body): array
    {
        $message = Message::create([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'body'        => $body,
        ]);

        return [
            'id'       => $message->id,
            'senderId' => $message->sender_id,
            'text'     => $message->body,
            'time'     => $message->created_at->format('g:i A'),
            'read'     => false,
        ];
    }

    public function clearConversation(int $userId1, int $userId2): void
    {
        Message::conversation($userId1, $userId2)->delete();
    }

    public function markRead(int $receiverId, int $senderId): void
    {
        Message::where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function unreadCount(int $receiverId, int $senderId): int
    {
        return Message::where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->whereNull('read_at')
            ->count();
    }
}
