<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\SendMessageRequest;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(): Response
    {
        $users = $this->chat->getUsersWithMeta(auth()->id());

        return Inertia::render('Welcome', [
            'users' => $users,
        ]);
    }

    public function messages(User $user): JsonResponse
    {
        $currentId = auth()->id();

        $messages = $this->chat->getConversation($currentId, $user->id);

        $this->chat->markRead($currentId, $user->id);

        return response()->json($messages);
    }

    public function clear(User $user): JsonResponse
    {
        $this->chat->clearConversation(auth()->id(), $user->id);

        return response()->json(null, 204);
    }

    public function send(SendMessageRequest $request): JsonResponse
    {
        $receiverId = $request->integer('receiver_id');

        $message = $this->chat->send(
            auth()->id(),
            $receiverId,
            $request->string('body'),
        );

        broadcast(new MessageSent([...$message, 'receiverId' => $receiverId]));

        return response()->json($message, 201);
    }
}
