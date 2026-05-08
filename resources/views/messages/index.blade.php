@extends('layouts.app')

@section('title', 'CardFlow | Messages')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
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
                        <a href="{{ route('messages.create') }}" class="dashboard-add-card">
                            New message
                        </a>
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
                                    $isUnread = ($conversation->unread_count ?? 0) > 0;
                                @endphp
                                @if ($participant)
                                    <a href="{{ route('messages.index', ['conversation' => $conversation->id, 'q' => $search ?: null]) }}" class="messages-list-link" data-conversation-link-id="{{ $conversation->id }}">
                                        <article class="messages-list-item {{ $isActive ? 'is-active' : '' }} {{ $isUnread ? 'is-unread' : '' }}">
                                            <div class="messages-avatar messages-avatar-rose"></div>
                                            <div class="messages-list-copy">
                                                <strong>{{ '@'.$participant->username }}</strong>
                                                <p>{{ $lastMessage?->body ?: 'No messages yet.' }}</p>
                                            </div>
                                            <div class="messages-list-meta">
                                                <span class="messages-list-time">{{ $lastMessage?->created_at?->diffForHumans() ?: 'Just now' }}</span>
                                                <span class="messages-unread-dot" @if (! $isUnread) hidden @endif></span>
                                                <span class="messages-unread" @if (! $isUnread) hidden @endif>{{ $conversation->unread_count ?? 0 }}</span>
                                            </div>
                                        </article>
                                    </a>
                                @endif
                            @empty
                                <div class="collection-empty collection-empty-rich messages-empty-state">
                                    <div class="collection-empty-icon" aria-hidden="true">💌</div>
                                    <h3>No messages yet.</h3>
                                    <p>Find a listing and message the seller.</p>
                                    <a href="{{ route('marketplace.index') }}" class="dashboard-add-card">
                                        Browse Marketplace
                                    </a>
                                </div>
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
                                @php
                                    $listingPhotoUrl = $storagePhotoUrl($activeListing->userCard?->photo_path);
                                    $listingPriceLabel = $activeListing->userCard?->is_for_sale
                                        ? 'PHP '.number_format((float) ($activeListing->userCard?->listing_price ?? 0), 0)
                                        : 'Trade listing';
                                @endphp
                                <div class="messages-listing-context">
                                    <div class="messages-listing-context-card">
                                        <div class="messages-listing-thumb {{ $activeListing->card?->thumbnail_style ?? 'market-thumb-one' }}">
                                            <img
                                                src="{{ $listingPhotoUrl ?: asset('images/placeholder-card.png') }}"
                                                alt="{{ $activeListing->card?->title ?: 'Listing image' }}"
                                                class="card-media-image"
                                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                            >
                                        </div>
                                        <div>
                                            <p class="mini-label">Listing in discussion</p>
                                            <strong>{{ $activeListing->card?->title ?: 'Marketplace listing' }}</strong>
                                            <p>{{ '@'.$activeListing->user->username }} - {{ $activeListing->card?->artist ?: 'Photocard listing' }}</p>
                                        </div>
                                    </div>
                                    <div class="messages-listing-context-actions">
                                        <span class="mini-chip">{{ $listingPriceLabel }}</span>
                                        <a href="{{ route('marketplace.cards.show', $activeListing) }}" class="mini-chip">View listing</a>
                                    </div>
                                </div>
                            @endif

                            <div class="messages-thread-body" data-thread-body>
                                @if (($activeConversation->messages_count ?? $activeConversation->messages->count()) > 0)
                                    @foreach ($activeConversation->messages as $message)
                                        @php
                                            $messageStatus = $message->sender_id === $user->id
                                                ? ($message->read_at ? 'Seen' : 'Sent')
                                                : ($message->read_at ? 'Read' : 'Unread');
                                        @endphp
                                        <article class="messages-bubble messages-bubble-{{ $message->sender_id === $user->id ? 'me' : 'them' }}" data-message-id="{{ $message->id }}">
                                            <p>{{ $message->body ?: 'Shared media' }}</p>
                                            <div class="messages-bubble-meta">
                                                <span>{{ $message->created_at?->format('g:i A') }}</span>
                                                <span class="messages-read-status">{{ $messageStatus }}</span>
                                            </div>
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
@endsection

