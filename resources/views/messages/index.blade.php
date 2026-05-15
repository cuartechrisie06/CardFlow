@extends('layouts.app')

@section('title', 'CardFlow | Messages')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">MESSAGES</p>
                        <h1>Messages</h1>
                        <p class="dashboard-intro">Chat with collectors about listings, trades, and shipping.</p>
                    </div>

                    <form method="GET" action="{{ route('messages.index') }}" class="dashboard-actions">
                        <label class="dashboard-search">
                            <span class="sr-only">Search chats</span>
                            <input type="search" name="q" value="{{ $search }}" placeholder="Search chats, users, listings...">
                        </label>
                        <button type="submit" class="dashboard-search-submit">Search</button>
                        <button type="button" onclick="openNewMessageModal()" class="dashboard-add-card btn-brown messages-new-message-btn" data-compose-open>
                            + NEW MESSAGE
                        </button>
                    </form>
                </header>

                <section class="dashboard-card messages-shell">
                    <aside class="messages-sidebar-panel">
                        <div class="messages-panel-top">
                            <div>
                                <p class="mini-label">MESSAGES</p>
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
                                    $participantName = $participant?->name ?: 'Unknown collector';
                                    $participantUsername = $participant?->username ? '@'.$participant->username : '@collector';
                                @endphp
                                <a
                                    href="{{ route('messages.index', ['conversation' => $conversation->id, 'q' => $search ?: null]) }}"
                                    class="conversation-item {{ $isActive ? 'is-active' : '' }} {{ $isUnread ? 'unread' : '' }}"
                                    data-conversation-link-id="{{ $conversation->id }}"
                                >
                                    <div class="conv-avatar">
                                        @if($participant?->avatar_url)
                                            <img src="{{ $participant->avatar_url }}" alt="{{ $participantName }}">
                                        @else
                                            <div class="conv-avatar-initials">@initials($participantName)</div>
                                        @endif
                                    </div>

                                    <div class="conv-info">
                                        <div class="conv-info-top">
                                            <span class="conv-name">{{ $participantName }}</span>
                                            <span class="conv-time">{{ $lastMessage?->created_at?->diffForHumans() ?: $conversation->updated_at?->diffForHumans() }}</span>
                                        </div>
                                        <div class="conv-username">{{ $participantUsername }}</div>
                                        <div class="conv-preview">
                                            {{ \Illuminate\Support\Str::limit($lastMessage?->body ?? 'No messages yet', 40) }}
                                        </div>
                                    </div>

                                    @if($isUnread)
                                        <div class="conv-unread-dot"></div>
                                    @endif
                                </a>
                            @empty
                                <div class="collection-empty collection-empty-rich messages-empty-state">
                                    <div class="collection-empty-icon" aria-hidden="true">💌</div>
                                    <h3>No messages yet.</h3>
                                    <p>Start from a marketplace listing to keep the card, seller, and trade context attached.</p>
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
                                $participantName = $participant?->name ?: 'Unknown collector';
                                $participantUsername = $participant?->username ? '@'.$participant->username : '@collector';
                            @endphp
                            <div class="thread-header">
                                <div class="conv-avatar">
                                    @if($participant?->avatar_url)
                                        <img src="{{ $participant->avatar_url }}" alt="{{ $participantName }}">
                                    @else
                                        <div class="conv-avatar-initials">@initials($participantName)</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="thread-user-name">{{ $participantName }}</div>
                                    <div class="thread-user-username">{{ $participantUsername }}</div>
                                </div>
                                <span class="offline-badge" data-online-status><span class="offline-badge-dot" aria-hidden="true">&#9679;</span> Offline</span>
                            </div>

                            <div class="messages-thread-content">
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

                                <div id="messages-thread-body" class="messages-thread-body" data-thread-body>
                                    @forelse ($activeConversation->messages as $message)
                                        @php
                                            $isSent = $message->sender_id === $user->id;
                                        @endphp
                                        <div class="message-row {{ $isSent ? 'sent' : 'received' }}" data-message-id="{{ $message->id }}">
                                            <div class="message-bubble {{ $isSent ? 'bubble-sent' : 'bubble-received' }}">
                                                @if($message->body)
                                                    <p>{{ $message->body }}</p>
                                                @endif

                                                @if($message->attachment_path)
                                                    <div class="message-attachment-display">
                                                        @if($message->attachment_type === 'video')
                                                            <video class="message-video" controls>
                                                                <source src="{{ asset('storage/message-attachments/' . $message->attachment_path) }}">
                                                            </video>
                                                        @else
                                                            <img
                                                                src="{{ asset('storage/message-attachments/' . $message->attachment_path) }}"
                                                                alt="{{ $message->attachment_name ?: 'Attached image' }}"
                                                                class="message-img"
                                                                onclick="openLightbox(this.src)"
                                                            >
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="message-meta">
                                                    <span class="message-time">{{ $message->created_at?->format('g:i A') }}</span>
                                                    @if($isSent)
                                                        <span class="message-status">{{ $message->read_at ? 'Read' : 'Sent' }}</span>
                                                    @else
                                                        <span class="message-status">Received</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="messages-empty">
                                            <p>No messages in this conversation yet. Say hello!</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="messages-typing" data-typing-indicator hidden></div>

                            <div id="attachment-preview" class="attachment-preview hidden">
                                <div id="preview-content"></div>
                                <button type="button" onclick="removeAttachment()" class="remove-attachment-btn">
                                    ✕ Remove
                                </button>
                            </div>

                            <form class="message-input-bar messages-compose" method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data" data-message-form>
                                @csrf
                                <input type="hidden" name="conversation_id" value="{{ $activeConversation->id }}">
                                <input
                                    type="file"
                                    name="attachment"
                                    id="message-attachment"
                                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime"
                                    class="hidden"
                                    onchange="handleAttachment(this)"
                                >
                                <label for="message-attachment" class="attach-btn" title="Send photo or video">📎</label>
                                <input type="text" name="body" placeholder="Type your message..." value="" data-message-input>
                                <button type="submit" class="send-btn" aria-label="Send message">Send</button>
                            </form>
                            <div id="lightbox" class="hidden" onclick="closeLightbox()" style="position:fixed;inset:0;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;z-index:9999;cursor:pointer;">
                                <img id="lightbox-img" src="" style="max-width:90vw;max-height:90vh;border-radius:12px;object-fit:contain;">
                            </div>
                        @else
                            <div class="collection-empty collection-empty-rich messages-thread-empty">
                                <div class="collection-empty-icon" aria-hidden="true">@</div>
                                <h3>No chat selected yet.</h3>
                                <p>Choose a chat from the inbox, or browse marketplace listings to start a trade conversation.</p>
                                <a href="{{ route('marketplace.index') }}" class="dashboard-add-card">Browse Marketplace</a>
                            </div>
                        @endif
                    </section>
                </section>

            <div id="new-message-modal" class="modal-overlay hidden" onclick="handleModalBackdrop(event)">
                <div class="modal-box" onclick="event.stopPropagation()">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                        <div>
                            <p style="font-family:'DM Sans',sans-serif; font-size:0.65rem; text-transform:uppercase; letter-spacing:0.08em; color:#8B4513; margin-bottom:4px;">
                                MESSAGES
                            </p>
                            <h2 style="font-family:'Playfair Display',serif; font-size:1.5rem; color:#3d2b1f; margin:0;">
                                New message
                            </h2>
                            <p style="font-family:'DM Sans',sans-serif; font-size:0.85rem; color:#b09070; margin-top:4px;">
                                Start a conversation with another collector.
                            </p>
                        </div>
                        <button type="button" onclick="closeNewMessageModal()" style="background:none; border:none; font-size:1.2rem; color:#b09070; cursor:pointer; padding:4px 8px;">
                            ✕
                        </button>
                    </div>

                    <form method="POST" action="{{ route('messages.start') }}">
                        @csrf

                        <div style="margin-bottom:16px;">
                            <label style="font-family:'DM Sans',sans-serif; font-size:0.65rem; text-transform:uppercase; letter-spacing:0.08em; color:#8B4513; display:block; margin-bottom:6px;">
                                RECIPIENT
                            </label>
                            <select name="recipient_id"
                                    required
                                    style="width:100%; padding:12px 16px; border:1px solid #d4b896; border-radius:10px; background:#ffffff; font-family:'DM Sans',sans-serif; font-size:0.9rem; color:#3d2b1f; appearance:auto;">
                                <option value="">Select a collector...</option>
                                @foreach($composeUsers as $composeUser)
                                    <option value="{{ $composeUser->id }}" @selected(($composeRecipientId ?? null) === $composeUser->id)>
                                        {{ $composeUser->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label style="font-family:'DM Sans',sans-serif; font-size:0.65rem; text-transform:uppercase; letter-spacing:0.08em; color:#8B4513; display:block; margin-bottom:6px;">
                                MESSAGE
                            </label>
                            <textarea name="body"
                                      required
                                      rows="5"
                                      placeholder="Write your first message..."
                                      style="width:100%; padding:12px 16px; border:1px solid #d4b896; border-radius:10px; background:#ffffff; font-family:'DM Sans',sans-serif; font-size:0.9rem; color:#3d2b1f; resize:vertical; box-sizing:border-box;">{{ old('body', $draftMessage ?? '') }}</textarea>
                            @if(($draftMessage ?? '') !== '')
                                <input type="hidden" value="{{ $draftMessage }}">
                            @endif
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn-brown" style="flex:1; padding:14px; font-size:0.9rem;">
                                Send message
                            </button>
                            <button type="button" onclick="closeNewMessageModal()" style="padding:14px 20px; border:1px solid #d4b896; border-radius:30px; background:transparent; color:#8B4513; font-family:'DM Sans',sans-serif; font-size:0.9rem; cursor:pointer;">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
@endsection

@push('scripts')
<script>
function openNewMessageModal() {
    const modal = document.getElementById('new-message-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeNewMessageModal() {
    const modal = document.getElementById('new-message-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function handleModalBackdrop(event) {
    if (event.target === event.currentTarget) {
        closeNewMessageModal();
    }
}

@if($errors->any())
    openNewMessageModal();
@endif

@if($openCompose ?? false)
    openNewMessageModal();
@endif

document.addEventListener('DOMContentLoaded', function() {
    const thread = document.getElementById('messages-thread-body');

    if (thread) {
        thread.scrollTop = thread.scrollHeight;
    }
});
</script>
@endpush


