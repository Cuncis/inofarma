<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendChatMessageRequest;
use App\Http\Requests\StartChatRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The storefront chat widget's endpoints. A visitor has no account: starting a
 * chat hands back a `token`, which the browser keeps and sends in the
 * `X-Chat-Token` header (never in the URL, so it stays out of access logs), and
 * which is also the link in the reply email.
 */
class ChatController extends Controller
{
    /**
     * Starts a conversation with the visitor's name, email and first message.
     */
    public function start(StartChatRequest $request): JsonResponse
    {
        // A filled honeypot is a bot. Answer as if it worked, store nothing.
        if ($request->filled('website')) {
            return response()->json(['token' => ChatConversation::newToken(), 'messages' => []], 201);
        }

        $conversation = ChatConversation::create([
            'token' => ChatConversation::newToken(),
            'name' => trim($request->validated('name')),
            'email' => mb_strtolower(trim($request->validated('email'))),
            'last_message_at' => now(),
        ]);

        $this->visitorMessage($conversation, $request->validated('message'));

        if ($request->boolean('subscribe')) {
            Subscriber::subscribeEmail($conversation->email);
        }

        return response()->json([
            'token' => $conversation->token,
            'messages' => $this->present($conversation->messages()->get()),
        ], 201);
    }

    /**
     * New messages since `after`. With `open=1` the visitor is looking at the
     * chat window, so the admin's replies count as seen and are not emailed.
     * Without it (the closed launcher's background check) they stay unseen.
     */
    public function show(Request $request): JsonResponse
    {
        $conversation = $this->conversationFor($request);
        $after = max((int) $request->query('after', 0), 0);

        $messages = $conversation->messages()->where('id', '>', $after)->get();

        if ($request->boolean('open')) {
            $conversation->messages()
                ->where('sender', ChatMessage::FROM_ADMIN)
                ->whereNull('visitor_seen_at')
                ->update(['visitor_seen_at' => now()]);
        }

        return response()->json([
            'messages' => $this->present($messages),
            'unread' => $conversation->messages()
                ->where('sender', ChatMessage::FROM_ADMIN)
                ->whereNull('visitor_seen_at')
                ->count(),
        ]);
    }

    /**
     * A further message from the visitor. Writing to a closed chat reopens it.
     */
    public function send(SendChatMessageRequest $request): JsonResponse
    {
        $conversation = $this->conversationFor($request);

        $message = $this->visitorMessage($conversation, $request->validated('message'));

        return response()->json(['message' => $this->present(collect([$message]))[0]], 201);
    }

    private function visitorMessage(ChatConversation $conversation, string $body): ChatMessage
    {
        return $conversation->messages()->create([
            'sender' => ChatMessage::FROM_VISITOR,
            'body' => trim($body),
        ]);
    }

    private function conversationFor(Request $request): ChatConversation
    {
        $token = (string) $request->header('X-Chat-Token');

        abort_unless(strlen($token) === 40, 404);

        return ChatConversation::where('token', $token)->firstOrFail();
    }

    /**
     * @param  iterable<ChatMessage>  $messages
     * @return list<array{id: int, sender: string, body: string, createdAt: string}>
     */
    private function present(iterable $messages): array
    {
        return collect($messages)->map(fn (ChatMessage $message) => [
            'id' => $message->id,
            'sender' => $message->sender,
            'body' => $message->body,
            'createdAt' => $message->created_at->toIso8601String(),
        ])->values()->all();
    }
}
