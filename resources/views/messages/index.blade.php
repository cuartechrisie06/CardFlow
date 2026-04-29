<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>CardFlow | Messages</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $user = auth()->user();
            $username = $user->username ?: 'collector';
        @endphp
        <main
            class="dashboard-shell"
            data-messages-app
            data-user-id="{{ $user->id }}"
            data-username="{{ $username }}"
            data-active-conversation-id="{{ $activeConversation?->id }}"
            data-send-url="{{ route('messages.store') }}"
            data-start-url="{{ route('messages.start') }}"
            data-read-url-template="{{ route('messages.read', ['conversation' => '__CONVERSATION__']) }}"
            data-open-compose="{{ $openCompose ? '1' : '0' }}"
            data-compose-recipient-id="{{ $composeRecipientId }}"
            data-compose-listing-id="{{ $composeListingId }}"
        >
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>{{ $user->name }}</p>
                        <span>{{ '@'.$username }}</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link is-active">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $user])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Messages</p>
                        <h1>Trade chat screen</h1>
                        <p class="dashboard-intro">In-app real-time messaging for negotiating trades, sharing card proof, and confirming shipping details.</p>
                    </div>

                    <form method="GET" action="{{ route('messages.index') }}" class="dashboard-actions">
                        <label class="dashboard-search">
                            <span class="sr-only">Search chats</span>
                            <input type="search" name="q" value="{{ $search }}" placeholder="Search chats, users, listings...">
                        </label>
                        <button type="submit" class="dashboard-search-submit">Search</button>
                        <button type="button" class="dashboard-add-card" data-compose-open>New message</button>
                    </form>
                </header>

                <section class="dashboard-card messages-shell">
                    <aside class="messages-sidebar-panel">
                        <div class="messages-panel-top">
                            <div>
                                <p class="mini-label">Messages</p>
                                <h2>Inbox</h2>
                            </div>
                            <span class="mini-chip">{{ $conversations->count() }} chats</span>
                        </div>

                        <label class="messages-filter">
                            <span class="sr-only">Search conversations</span>
                            <input type="search" value="{{ $search }}" placeholder="Search conversations..." readonly>
                        </label>

                        <div class="messages-list" data-messages-list>
                            @forelse ($conversations as $conversation)
                                @php
                                    $participant = $conversation->otherParticipant($user);
                                    $lastMessage = $conversation->latestMessage;
                                    $isActive = $activeConversation && $activeConversation->id === $conversation->id;
                                @endphp
                                @if ($participant)
                                    <a href="{{ route('messages.index', ['conversation' => $conversation->id, 'q' => $search ?: null]) }}" class="messages-list-link" data-conversation-link-id="{{ $conversation->id }}">
                                        <article class="messages-list-item {{ $isActive ? 'is-active' : '' }}">
                                            <div class="messages-avatar messages-avatar-rose"></div>
                                            <div class="messages-list-copy">
                                                <strong>{{ '@'.$participant->username }}</strong>
                                                <p>{{ $lastMessage?->body ?: 'No messages yet.' }}</p>
                                            </div>
                                            <span class="messages-unread" @if (($conversation->unread_count ?? 0) <= 0) hidden @endif>{{ $conversation->unread_count ?? 0 }}</span>
                                        </article>
                                    </a>
                                @endif
                            @empty
                                <div class="collection-empty">No conversations match this search yet.</div>
                            @endforelse
                        </div>
                    </aside>

                    <section class="messages-thread-panel">
                        @if ($activeConversation)
                            @php
                                $participant = $activeConversation->otherParticipant($user);
                            @endphp
                            <div class="messages-thread-header">
                                <div class="messages-thread-profile">
                                    <div class="messages-avatar messages-avatar-rose"></div>
                                    <div>
                                        <strong>{{ '@'.$participant->username }}</strong>
                                        <p>{{ $participant->name }}</p>
                                    </div>
                                </div>
                                <span class="mini-chip" data-online-status>Offline</span>
                            </div>

                            @if ($activeListing)
                                <div class="messages-listing-context">
                                    <div>
                                        <p class="mini-label">Listing in discussion</p>
                                        <strong>{{ $activeListing->card?->title ?: 'Marketplace listing' }}</strong>
                                        <p>{{ '@'.$activeListing->user->username }} - {{ $activeListing->card?->artist ?: 'Photocard listing' }}</p>
                                    </div>
                                    <a href="{{ route('marketplace.cards.show', $activeListing) }}" class="mini-chip">View listing</a>
                                </div>
                            @endif

                            <div class="messages-thread-body" data-thread-body>
                                @if (($activeConversation->messages_count ?? $activeConversation->messages->count()) > 0)
                                    @foreach ($activeConversation->messages as $message)
                                        <article class="messages-bubble messages-bubble-{{ $message->sender_id === $user->id ? 'me' : 'them' }}" data-message-id="{{ $message->id }}">
                                            <p>{{ $message->body ?: 'Shared media' }}</p>
                                            <span>{{ $message->created_at?->format('g:i A') }}</span>
                                        </article>
                                    @endforeach
                                @else
                                    <div class="collection-empty">No messages in this conversation yet.</div>
                                @endif
                            </div>

                            <div class="messages-typing" data-typing-indicator hidden></div>

                            <form class="messages-compose" method="POST" action="{{ route('messages.store') }}" data-message-form>
                                @csrf
                                <input type="hidden" name="conversation_id" value="{{ $activeConversation->id }}">
                                <input type="text" name="body" placeholder="Type your message..." value="{{ $draftMessage }}" data-message-input>
                                <button type="submit" aria-label="Send message">Send</button>
                            </form>
                        @else
                            <div class="collection-empty">No chat selected yet.</div>
                        @endif
                    </section>
                </section>
            </section>

            <div class="messages-compose-overlay" data-compose-overlay hidden>
                <div class="messages-compose-modal">
                    <div class="messages-compose-header">
                        <div>
                            <p class="mini-label">New message</p>
                            <h2>Start a conversation</h2>
                        </div>
                        <button type="button" class="messages-compose-close" data-compose-close aria-label="Close compose">Close</button>
                    </div>

                    <form class="messages-compose-form" data-compose-form>
                        <label class="field-group">
                            <span>Recipient</span>
                            <select name="recipient_id" class="field-select" data-compose-recipient required>
                                <option value="">Select a user</option>
                                @foreach ($composeUsers as $composeUser)
                                    <option value="{{ $composeUser->id }}" @selected($composeRecipientId === $composeUser->id)>{{ $composeUser->name }} ({{ '@'.$composeUser->username }})</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="field-group">
                            <span>Marketplace Listing (Optional)</span>
                            <select name="listing_id" class="field-select">
                                <option value="">No listing attached</option>
                                @foreach ($composeListings as $listing)
                                    <option value="{{ $listing->id }}" @selected($composeListingId === $listing->id)>
                                        {{ $listing->card?->title }} - {{ '@'.$listing->user?->username }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="field-group field-group-wide">
                            <span>First Message</span>
                            <textarea name="body" rows="5" placeholder="Type your first message..." required></textarea>
                        </label>

                        <p class="field-error" data-compose-error hidden></p>

                        <div class="create-form-actions">
                            <button type="button" class="dashboard-add-card dashboard-add-card-secondary" data-compose-close>Cancel</button>
                            <button type="submit" class="dashboard-add-card">Start chat</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </body>
</html>
