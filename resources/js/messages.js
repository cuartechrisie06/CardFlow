const formatTime = (isoString) => {
    if (!isoString) {
        return '';
    }

    const date = new Date(isoString);

    return date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
    });
};

export function initRealtimeMessages() {
    const app = document.querySelector('[data-messages-app]');

    if (!app) {
        return;
    }

    const echo = window.Echo ?? null;

    const currentUserId = Number(app.dataset.userId);
    const sendUrl = app.dataset.sendUrl;
    const readUrlTemplate = app.dataset.readUrlTemplate;
    const conversationList = app.querySelector('[data-messages-list]');
    const threadBody = app.querySelector('[data-thread-body]');
    const form = app.querySelector('[data-message-form]');
    const input = app.querySelector('[data-message-input]');
    const typingIndicator = app.querySelector('[data-typing-indicator]');
    const onlineBadge = app.querySelector('[data-online-status]');
    const composeOverlay = app.querySelector('[data-compose-overlay]');
    const composeForm = app.querySelector('[data-compose-form]');
    const composeError = app.querySelector('[data-compose-error]');
    const startUrl = app.dataset.startUrl;
    const shouldOpenCompose = app.dataset.openCompose === '1';
    const presetRecipientId = app.dataset.composeRecipientId;
    const presetListingId = app.dataset.composeListingId;

    let activeConversationId = Number(app.dataset.activeConversationId || 0);
    let presenceChannel = null;
    let typingTimeout = null;

    const inboxChannel = echo
        ? echo.private(`users.${currentUserId}.inbox`)
            .listen('.message.sent', (event) => {
                upsertConversationPreview(event);

                if (Number(event.conversation.id) === activeConversationId && Number(event.message.receiver_id) === currentUserId) {
                    markConversationRead(activeConversationId);
                }
            })
        : null;

    function joinConversation(conversationId) {
        if (!conversationId) {
            return;
        }

        if (!echo) {
            activeConversationId = Number(conversationId);
            return;
        }

        if (presenceChannel) {
            echo.leave(`conversation.${activeConversationId}`);
        }

        activeConversationId = Number(conversationId);

        presenceChannel = echo.join(`conversation.${activeConversationId}`)
            .here((users) => updateOnlineStatus(users))
            .joining((user) => updateOnlineStatus([user], true))
            .leaving((user) => updateOnlineStatus([user], false))
            .listen('.message.sent', (event) => {
                if (Number(event.conversation.id) !== activeConversationId) {
                    return;
                }

                appendMessage(event.message);
            })
            .listenForWhisper('typing', (event) => {
                if (Number(event.userId) === currentUserId) {
                    return;
                }

                if (typingIndicator) {
                    typingIndicator.hidden = false;
                    typingIndicator.textContent = `${event.username} is typing...`;

                    window.clearTimeout(typingTimeout);
                    typingTimeout = window.setTimeout(() => {
                        typingIndicator.hidden = true;
                    }, 1200);
                }
            });

        markConversationRead(activeConversationId);
    }

    function appendMessage(message) {
        if (!threadBody || threadBody.querySelector(`[data-message-id="${message.id}"]`)) {
            return;
        }

        clearEmptyState(threadBody);

        const article = document.createElement('article');
        article.className = `messages-bubble messages-bubble-${Number(message.sender_id) === currentUserId ? 'me' : 'them'}`;
        article.dataset.messageId = message.id;
        article.innerHTML = `<p>${escapeHtml(message.body || 'Shared media')}</p><span>${formatTime(message.created_at)}</span>`;

        threadBody.appendChild(article);
        threadBody.scrollTop = threadBody.scrollHeight;
    }

    function upsertConversationPreview(event) {
        if (!conversationList) {
            return;
        }

        clearEmptyState(conversationList);

        const conversationId = Number(event.conversation.id);
        let link = conversationList.querySelector(`[data-conversation-link-id="${conversationId}"]`);
        const unreadCount = Number(event.unread_counts?.[String(currentUserId)] ?? 0);
        const otherParticipant = (event.participants || []).find((participant) => Number(participant.id) !== currentUserId);

        if (!otherParticipant) {
            return;
        }

        if (!link) {
            link = document.createElement('a');
            link.className = 'messages-list-link';
            link.dataset.conversationLinkId = conversationId;
            link.href = `${window.location.pathname}?conversation=${conversationId}`;
            link.innerHTML = `
                <article class="messages-list-item">
                    <div class="messages-avatar messages-avatar-rose"></div>
                    <div class="messages-list-copy">
                        <strong>@${escapeHtml(otherParticipant.username)}</strong>
                        <p></p>
                    </div>
                    <span class="messages-unread" hidden></span>
                </article>
            `;
            conversationList.prepend(link);
        }

        link.href = `${window.location.pathname}?conversation=${conversationId}`;

        const article = link.querySelector('.messages-list-item');
        const preview = link.querySelector('.messages-list-copy p');
        const title = link.querySelector('.messages-list-copy strong');
        const badge = link.querySelector('.messages-unread');

        if (article) {
            article.classList.toggle('is-active', conversationId === activeConversationId);
        }

        if (title) {
            title.textContent = `@${otherParticipant.username}`;
        }

        if (preview) {
            preview.textContent = event.preview || 'New message';
        }

        if (badge) {
            badge.hidden = unreadCount <= 0;
            badge.textContent = String(unreadCount);
        }

        conversationList.prepend(link);
    }

    function updateOnlineStatus(users, joining = null) {
        if (!onlineBadge) {
            return;
        }

        if (Array.isArray(users) && joining === null) {
            const onlineOthers = users.filter((user) => Number(user.id) !== currentUserId).length;
            onlineBadge.textContent = onlineOthers > 0 ? 'Active now' : 'Offline';

            return;
        }

        onlineBadge.textContent = joining ? 'Active now' : 'Offline';
    }

    async function markConversationRead(conversationId) {
        if (!conversationId || !readUrlTemplate) {
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        await fetch(readUrlTemplate.replace('__CONVERSATION__', conversationId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token ?? '',
                'Accept': 'application/json',
            },
        });

        const badge = conversationList?.querySelector(`[data-conversation-link-id="${conversationId}"] .messages-unread`);

        if (badge) {
            badge.hidden = true;
            badge.textContent = '0';
        }
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!activeConversationId || !input || input.value.trim() === '') {
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await fetch(sendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token ?? '',
            },
            body: JSON.stringify({
                conversation_id: activeConversationId,
                body: input.value.trim(),
            }),
        });

        const result = await response.json().catch(() => ({}));

        if (!response.ok) {
            return;
        }

        if (result.event) {
            appendMessage(result.event.message);
            upsertConversationPreview(result.event);
        }

        if (response.ok) {
            input.value = '';
            typingIndicator && (typingIndicator.hidden = true);
        }
    });

    app.querySelectorAll('[data-compose-open]').forEach((button) => {
        button.addEventListener('click', () => {
            if (composeOverlay) {
                composeOverlay.hidden = false;
            }
        });
    });

    app.querySelectorAll('[data-compose-close]').forEach((button) => {
        button.addEventListener('click', () => {
            if (composeOverlay) {
                composeOverlay.hidden = true;
            }

            if (composeError) {
                composeError.hidden = true;
                composeError.textContent = '';
            }
        });
    });

    composeForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(composeForm);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const payload = {
            recipient_id: formData.get('recipient_id'),
            listing_id: formData.get('listing_id') || null,
            body: formData.get('body'),
        };

        const response = await fetch(startUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token ?? '',
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (composeError) {
                composeError.hidden = false;
                composeError.textContent = result.message || 'Unable to start conversation.';
            }

            return;
        }

        window.location.assign(`${window.location.pathname}?conversation=${result.conversation_id}`);
    });

    if (shouldOpenCompose && composeOverlay && composeForm) {
        composeOverlay.hidden = false;

        const recipientField = composeForm.querySelector('[data-compose-recipient]');
        const listingField = composeForm.querySelector('select[name="listing_id"]');

        if (recipientField && presetRecipientId) {
            recipientField.value = presetRecipientId;
        }

        if (listingField && presetListingId) {
            listingField.value = presetListingId;
        }
    }

    input?.addEventListener('input', () => {
        if (!presenceChannel || !echo || !input || input.value.trim() === '') {
            return;
        }

        presenceChannel.whisper('typing', {
            userId: currentUserId,
            username: app.dataset.username,
        });
    });

    if (threadBody) {
        threadBody.scrollTop = threadBody.scrollHeight;
    }

    joinConversation(activeConversationId);

    window.addEventListener('beforeunload', () => {
        if (activeConversationId) {
            echo?.leave(`conversation.${activeConversationId}`);
        }

        inboxChannel?.stopListening('.message.sent');
    });
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;

    return div.innerHTML;
}

function clearEmptyState(container) {
    const emptyState = container?.querySelector('.collection-empty');

    if (emptyState) {
        emptyState.remove();
    }
}
