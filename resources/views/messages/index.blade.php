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

                    <div class="dashboard-actions">
                        <button type="button" onclick="openNewMessageModal()" class="dashboard-add-card btn-brown messages-new-message-btn" data-compose-open>
                            + NEW MESSAGE
                        </button>
                    </div>
                </header>

                <section
                    class="dashboard-card messages-shell"
                    data-messages-app
                    data-user-id="{{ $user->id }}"
                    data-username="{{ $user->username }}"
                    data-send-url="{{ route('messages.store') }}"
                    data-read-url-template="{{ route('messages.read', ['conversation' => '__CONVERSATION__']) }}"
                    data-start-url="{{ route('messages.start') }}"
                    data-active-conversation-id="{{ $activeConversation?->id }}"
                    data-open-compose="{{ $openCompose ? '1' : '0' }}"
                    data-compose-recipient-id="{{ $composeRecipientId ?? '' }}"
                    data-compose-listing-id="{{ $composeListingId ?? '' }}"
                >
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
                                    $participantProfileUrl = $participant?->username ? route('profile.showcase', $participant) : null;
                                @endphp
                                <a
                                    href="{{ route('messages.index', ['conversation' => $conversation->id, 'q' => $search ?: null]) }}"
                                    class="conversation-item {{ $isActive ? 'is-active' : '' }} {{ $isUnread ? 'unread' : '' }}"
                                    data-conversation-link-id="{{ $conversation->id }}"
                                >
                                    <div
                                        class="conv-avatar profile-click-target"
                                        role="{{ $participantProfileUrl ? 'link' : 'presentation' }}"
                                        tabindex="{{ $participantProfileUrl ? '0' : '-1' }}"
                                        @if($participantProfileUrl)
                                            onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ $participantProfileUrl }}';"
                                            onkeydown="if(event.key === 'Enter'){ event.preventDefault(); event.stopPropagation(); window.location.href='{{ $participantProfileUrl }}'; }"
                                        @endif
                                    >
                                        @if($participant?->avatar_url)
                                            <img src="{{ $participant->avatar_url }}" alt="{{ $participantName }}">
                                        @else
                                            <div class="conv-avatar-initials">@initials($participantName)</div>
                                        @endif
                                    </div>

                                    <div class="conv-info">
                                        <div class="conv-info-top">
                                            <span
                                                class="conv-name profile-click-target"
                                                role="{{ $participantProfileUrl ? 'link' : 'presentation' }}"
                                                tabindex="{{ $participantProfileUrl ? '0' : '-1' }}"
                                                @if($participantProfileUrl)
                                                    onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ $participantProfileUrl }}';"
                                                    onkeydown="if(event.key === 'Enter'){ event.preventDefault(); event.stopPropagation(); window.location.href='{{ $participantProfileUrl }}'; }"
                                                @endif
                                            >{{ $participantName }}</span>
                                            <span class="conv-time">{{ $lastMessage?->created_at?->diffForHumans() ?: $conversation->updated_at?->diffForHumans() }}</span>
                                        </div>
                                        <div
                                            class="conv-username profile-click-target"
                                            role="{{ $participantProfileUrl ? 'link' : 'presentation' }}"
                                            tabindex="{{ $participantProfileUrl ? '0' : '-1' }}"
                                            @if($participantProfileUrl)
                                                onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ $participantProfileUrl }}';"
                                                onkeydown="if(event.key === 'Enter'){ event.preventDefault(); event.stopPropagation(); window.location.href='{{ $participantProfileUrl }}'; }"
                                            @endif
                                        >{{ $participantUsername }}</div>
                                        <div class="conv-preview">
                                            @if($lastMessage)
                                                <span class="conv-preview-text">
                                                    {{ \Illuminate\Support\Str::limit($lastMessage->body ?: ($lastMessage->attachment_type === 'video' ? 'Sent a video' : 'Sent a photo'), 40) }}
                                                </span>
                                            @else
                                                <span class="conv-preview-empty">No messages yet</span>
                                            @endif
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
                                $participantProfileUrl = $participant?->username ? route('profile.showcase', $participant) : null;
                            @endphp
                            <div class="thread-header">
                                <a href="{{ $participantProfileUrl ?: '#' }}" class="thread-profile-link" aria-label="View {{ $participantName }} profile">
                                    <div class="conv-avatar thread-header-avatar">
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
                                </a>
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
                                    <div id="listing-chip" class="messages-listing-context">
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
                                                <p>
                                                    <a href="{{ route('profile.showcase', $activeListing->user) }}" class="collector-profile-link">
                                                        {{ '@'.$activeListing->user->username }}
                                                    </a>
                                                    - {{ $activeListing->card?->artist ?: 'Photocard listing' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="messages-listing-context-actions">
                                            <span class="mini-chip">{{ $listingPriceLabel }}</span>
                                            <a href="{{ route('marketplace.cards.show', $activeListing) }}" class="mini-chip">View listing</a>
                                            @if(auth()->id() !== $activeListing->user_id)
                                                <a href="{{ route('marketplace.cards.show', $activeListing) }}#trade-request" class="mini-chip">Trade listing</a>
                                            @endif
                                        </div>
                                        <button
                                            type="button"
                                            onclick="dismissListingChip()"
                                            class="listing-chip-dismiss"
                                            title="Hide listing details"
                                            aria-label="Hide listing details"
                                        >&times;</button>
                                    </div>
                                    <div id="listing-chip-collapsed" class="listing-chip-collapsed">
                                        <button type="button" onclick="showListingChip()">
                                            &#128206; Show listing details
                                        </button>
                                    </div>
                                @endif

                                <div id="messages-thread-body" class="messages-thread-body" data-thread-body>
                                    @forelse ($activeConversation->messages as $message)
                                        @php
                                            $isSent = $message->sender_id === $user->id;
                                            $isDeleted = $message->trashed();
                                        @endphp
                                        <div class="message-row {{ $isSent ? 'sent' : 'received' }}" data-message-id="{{ $message->id }}">
                                            @if($isSent && ! $isDeleted)
                                                <div class="message-delete-btn">
                                                    <button
                                                        type="button"
                                                        onclick="openDeleteModal('{{ route('messages.destroy', $message) }}')"
                                                        class="message-delete-trigger"
                                                        title="Delete message"
                                                        aria-label="Delete message"
                                                    >
                                                        &times;
                                                    </button>
                                                </div>
                                            @endif

                                            @if($isDeleted)
                                                <div class="message-deleted-placeholder">
                                                    Message deleted
                                                </div>
                                            @else
                                                <div class="message-bubble {{ $isSent ? 'bubble-sent' : 'bubble-received' }}">
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

                                                    @if($message->body)
                                                        <p class="{{ $message->attachment_path ? 'message-body-with-attachment' : '' }}">{{ $message->body }}</p>
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
                                            @endif
                                        </div>
                                    @empty
                                        <div class="messages-empty">
                                            <p>No messages in this conversation yet. Say hello!</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="messages-typing" data-typing-indicator hidden></div>

                            <div id="attachment-preview"
                                 style="display:none;
                                        padding:8px 16px;
                                        background:#fdf6f0;
                                        border-top:1px solid #e8d5c0;
                                        border-bottom:1px solid #e8d5c0;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="position:relative; flex-shrink:0;">
                                        <img id="preview-thumbnail"
                                             src=""
                                             alt="Attachment preview"
                                             style="width:48px;
                                                    height:64px;
                                                    object-fit:cover;
                                                    border-radius:8px;
                                                    border:1px solid #e8d5c0;
                                                    display:none;">
                                        <video id="preview-video-thumb"
                                               style="width:80px;
                                                      height:56px;
                                                      object-fit:cover;
                                                      border-radius:8px;
                                                      border:1px solid #e8d5c0;
                                                      display:none;"
                                               muted
                                               playsinline>
                                        </video>
                                    </div>

                                    <div style="flex:1; min-width:0;">
                                        <p id="preview-filename"
                                           style="font-family:'DM Sans',sans-serif;
                                                  font-size:0.8rem;
                                                  color:#3d2b1f;
                                                  margin:0;
                                                  white-space:nowrap;
                                                  overflow:hidden;
                                                  text-overflow:ellipsis;"></p>
                                        <p id="preview-filesize"
                                           style="font-family:'DM Sans',sans-serif;
                                                  font-size:0.7rem;
                                                  color:#b09070;
                                                  margin:2px 0 0;"></p>
                                    </div>

                                    <button type="button"
                                            onclick="removeAttachment()"
                                            aria-label="Remove attachment"
                                            style="background:#f5e6d8;
                                                   border:none;
                                                   border-radius:50%;
                                                   width:28px;
                                                   height:28px;
                                                   color:#8B4513;
                                                   font-size:0.9rem;
                                                   cursor:pointer;
                                                   flex-shrink:0;
                                                   display:flex;
                                                   align-items:center;
                                                   justify-content:center;">
                                        &times;
                                    </button>
                                </div>
                            </div>

                            <form class="message-input-bar messages-compose" method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data" data-message-form>
                                @csrf
                                <input type="hidden" name="conversation_id" value="{{ $activeConversation->id }}">
                                <input
                                    type="file"
                                    name="attachment"
                                    id="message-attachment"
                                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime"
                                    style="display:none;"
                                    onchange="handleAttachment(this)"
                                >
                                <label for="message-attachment"
                                       title="Attach photo or video"
                                       style="cursor:pointer;
                                              padding:8px;
                                              border-radius:50%;
                                              background:#f5e6d8;
                                              width:36px;
                                              height:36px;
                                              display:flex;
                                              align-items:center;
                                              justify-content:center;
                                              flex-shrink:0;
                                              font-size:1rem;
                                              transition:background 150ms;">&#128206;</label>
                                <input type="text"
                                       name="body"
                                       id="message-body"
                                       placeholder="Type a message..."
                                       autocomplete="off"
                                       value=""
                                       data-message-input
                                       style="flex:1;
                                              padding:10px 16px;
                                              border:1px solid #d4b896;
                                              border-radius:30px;
                                              background:#fdf6f0;
                                              font-family:'DM Sans',sans-serif;
                                              font-size:0.9rem;
                                              color:#3d2b1f;
                                              outline:none;">
                                <button type="submit"
                                        aria-label="Send message"
                                        style="background:#8B4513;
                                               color:#ffffff;
                                               border:none;
                                               padding:10px 22px;
                                               border-radius:30px;
                                               font-family:'DM Sans',sans-serif;
                                               font-size:0.85rem;
                                               font-weight:600;
                                               cursor:pointer;
                                               flex-shrink:0;
                                               transition:background 150ms;">
                                    Send
                                </button>
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

            <div id="delete-message-modal"
                 class="modal-overlay hidden"
                 onclick="handleDeleteModalBackdrop(event)">
                <div class="modal-box"
                     onclick="event.stopPropagation()"
                     style="max-width:400px;text-align:center;">

                    <div style="width:56px;height:56px;
                                background:#fdf0e8;
                                border-radius:50%;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                margin:0 auto 16px;
                                color:#c0392b;
                                font-size:1.35rem;">
                        &#128465;
                    </div>

                    <h2 style="font-family:'Playfair Display',serif;
                               font-size:1.3rem;
                               font-weight:700;
                               color:#3d2b1f;
                               margin:0 0 8px;">
                        Delete this message?
                    </h2>

                    <p style="font-family:'DM Sans',sans-serif;
                              font-size:0.85rem;
                              color:#8B6F5E;
                              margin:0 0 24px;
                              line-height:1.5;">
                        This message will be deleted and cannot be recovered.
                        Other participants may still see it was deleted.
                    </p>

                    <form id="delete-message-form" method="POST" action="">
                        @csrf
                        @method('DELETE')
                    </form>

                    <div style="display:flex;gap:10px;justify-content:center;">
                        <button type="button"
                                onclick="closeDeleteModal()"
                                style="flex:1;
                                       font-family:'DM Sans',sans-serif;
                                       font-size:0.88rem;
                                       font-weight:500;
                                       padding:12px 20px;
                                       border-radius:30px;
                                       border:1px solid #d4b896;
                                       background:transparent;
                                       color:#8B4513;
                                       cursor:pointer;">
                            Cancel
                        </button>
                        <button type="button"
                                onclick="confirmDeleteMessage()"
                                style="flex:1;
                                       font-family:'DM Sans',sans-serif;
                                       font-size:0.88rem;
                                       font-weight:600;
                                       padding:12px 20px;
                                       border-radius:30px;
                                       border:none;
                                       background:#c0392b;
                                       color:#ffffff;
                                       cursor:pointer;">
                            Yes, delete
                        </button>
                    </div>
                </div>
            </div>

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

function openDeleteModal(actionUrl) {
    const form = document.getElementById('delete-message-form');
    const modal = document.getElementById('delete-message-modal');

    if (form) {
        form.action = actionUrl;
    }

    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeDeleteModal() {
    const form = document.getElementById('delete-message-form');
    const modal = document.getElementById('delete-message-modal');

    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if (form) {
        form.action = '';
    }
}

function confirmDeleteMessage() {
    const form = document.getElementById('delete-message-form');

    if (form && form.action) {
        form.submit();
    }
}

function handleDeleteModalBackdrop(event) {
    if (event.target === event.currentTarget) {
        closeDeleteModal();
    }
}

function handleAttachment(input) {
    if (!input.files || !input.files[0]) {
        return;
    }

    const file = input.files[0];
    const isVideo = file.type.startsWith('video/');
    const isImage = file.type.startsWith('image/');

    if (!isVideo && !isImage) {
        alert('Please choose an image or video file.');
        input.value = '';
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        alert('File too large. Max 10MB.');
        input.value = '';
        return;
    }

    const preview = document.getElementById('attachment-preview');
    const filename = document.getElementById('preview-filename');
    const filesize = document.getElementById('preview-filesize');
    const imgThumb = document.getElementById('preview-thumbnail');
    const vidThumb = document.getElementById('preview-video-thumb');

    if (filename) {
        filename.textContent = file.name;
    }

    if (filesize) {
        filesize.textContent = formatFileSize(file.size);
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        if (isImage && imgThumb && vidThumb) {
            imgThumb.src = e.target.result;
            imgThumb.style.display = 'block';
            vidThumb.removeAttribute('src');
            vidThumb.style.display = 'none';
        } else if (isVideo && vidThumb && imgThumb) {
            vidThumb.src = e.target.result;
            vidThumb.style.display = 'block';
            imgThumb.removeAttribute('src');
            imgThumb.style.display = 'none';
        }
    };
    reader.readAsDataURL(file);

    if (preview) {
        preview.style.display = 'block';
    }
}

function removeAttachment() {
    const input = document.getElementById('message-attachment');
    const preview = document.getElementById('attachment-preview');
    const imgThumb = document.getElementById('preview-thumbnail');
    const vidThumb = document.getElementById('preview-video-thumb');
    const filename = document.getElementById('preview-filename');
    const filesize = document.getElementById('preview-filesize');

    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imgThumb) {
        imgThumb.removeAttribute('src');
        imgThumb.style.display = 'none';
    }
    if (vidThumb) {
        vidThumb.removeAttribute('src');
        vidThumb.style.display = 'none';
    }
    if (filename) filename.textContent = '';
    if (filesize) filesize.textContent = '';
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) {
        return (bytes / 1024).toFixed(1) + ' KB';
    }
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function dismissListingChip() {
    const chip = document.getElementById('listing-chip');
    const collapsed = document.getElementById('listing-chip-collapsed');

    if (chip) {
        chip.style.display = 'none';
    }

    if (collapsed) {
        collapsed.style.display = 'block';
    }
}

function showListingChip() {
    const chip = document.getElementById('listing-chip');
    const collapsed = document.getElementById('listing-chip-collapsed');

    if (chip) {
        chip.style.display = 'flex';
    }

    if (collapsed) {
        collapsed.style.display = 'none';
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



