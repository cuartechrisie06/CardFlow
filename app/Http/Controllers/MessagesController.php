<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedId = $request->integer('conversation');
        $composeRecipientId = $request->integer('recipient');
        $composeListingId = $request->integer('listing');
        $openCompose = $request->boolean('compose');
        $draftMessage = trim((string) $request->string('draft'));

        $conversationsQuery = Conversation::query()
            ->withValidParticipants()
            ->forUser($user)
            ->withCount([
                'messages as unread_count' => fn ($query) => $query
                    ->withValidRelations()
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at'),
                'messages',
            ])
            ->with([
                'userOne:id,name,username',
                'userTwo:id,name,username',
                'latestMessage' => fn ($query) => $query
                    ->withValidRelations()
                    ->latest('created_at'),
                'marketplaceListing.card:id,title,artist,album',
                'marketplaceListing.user:id,name,username',
            ])
            ->when($search !== '', function ($query) use ($search, $user) {
                $query->where(function ($nested) use ($search, $user) {
                    $nested->whereHas('userOne', function ($userQuery) use ($search, $user) {
                        $userQuery->where('users.id', '!=', $user->id)
                            ->where(function ($searchQuery) use ($search) {
                                $searchQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('username', 'like', "%{$search}%");
                            });
                    })->orWhereHas('userTwo', function ($userQuery) use ($search, $user) {
                        $userQuery->where('users.id', '!=', $user->id)
                            ->where(function ($searchQuery) use ($search) {
                                $searchQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('username', 'like', "%{$search}%");
                            });
                    });
                });
            })
            ->latest('updated_at');

        $conversations = $conversationsQuery->get();

        $activeConversationId = $selectedId ?: $conversations->first()?->id;
        $activeConversation = null;

        if ($activeConversationId) {
            $activeConversation = Conversation::query()
                ->withValidParticipants()
                ->forUser($user)
                ->withCount('messages')
                ->with([
                    'userOne:id,name,username',
                    'userTwo:id,name,username',
                    'latestMessage' => fn ($query) => $query
                        ->withValidRelations()
                        ->latest('created_at'),
                    'marketplaceListing.card:id,title,artist,album',
                    'marketplaceListing.user:id,name,username',
                    'messages' => fn ($query) => $query
                        ->withValidRelations()
                        ->with(['sender:id,name,username', 'receiver:id,name,username'])
                        ->oldest('created_at'),
                ])
                ->find($activeConversationId);
        }

        if ($activeConversation) {
            $activeConversation->messages()
                ->where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->update([
                    'read_at' => now(),
                ]);

            $matchedConversation = $conversations->firstWhere('id', $activeConversation->id);

            if ($matchedConversation) {
                $matchedConversation->unread_count = 0;
                $matchedConversation->messages_count = $activeConversation->messages_count;
                $matchedConversation->setRelation('latestMessage', $activeConversation->latestMessage);
            }
        }

        $activeListing = $activeConversation?->marketplaceListing;

        if (! $activeListing && $composeListingId && $activeConversation) {
            $otherParticipant = $activeConversation->otherParticipant($user);

            $activeListing = MarketplaceListing::query()
                ->activeVisible()
                ->with(['card:id,title,artist,album', 'user:id,name,username'])
                ->whereKey($composeListingId)
                ->when($otherParticipant, fn ($query) => $query->where('user_id', $otherParticipant->id))
                ->first();
        }

        $composeUsers = User::query()
            ->whereKeyNot($user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        $composeListings = MarketplaceListing::query()
            ->activeVisible()
            ->where('user_id', '!=', $user->id)
            ->with(['card:id,title,artist,album', 'user:id,name,username'])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('messages.index', [
            'search' => $search,
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'composeUsers' => $composeUsers,
            'composeListings' => $composeListings,
            'openCompose' => $openCompose,
            'composeRecipientId' => $composeRecipientId,
            'composeListingId' => $composeListingId,
            'activeListing' => $activeListing,
            'draftMessage' => $draftMessage,
        ]);
    }
}
