@extends('layouts.app')

@section('title', 'CardFlow | Marketplace')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header marketplace-header">
                <div>
                    <span class="dashboard-eyebrow">Messages</span>
                    <h1>New message</h1>
                    <p>Start a conversation with another collector.</p>
                </div>

                <div class="dashboard-actions">
                    <a href="{{ route('messages.index') }}" class="dashboard-search-submit">
                        Back to Messages
                    </a>
                </div>
            </header>

            <section class="dashboard-card message-create-card">
                @if ($errors->any())
                    <div class="form-error-box">
                        <strong>Please check the form:</strong>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($users->isEmpty())
                    <div class="empty-state">
                        No collectors are available yet. Browse Explorer or Marketplace first, then start a message from a listing.
                    </div>
                @else
                    <form method="POST" action="{{ route('messages.start') }}" class="message-create-form">
                        @csrf

                        <label class="form-field">
                            <span>Recipient</span>

                            <select name="recipient_id" required>
                                <option value="">Choose a user</option>

                                @foreach ($users as $recipient)
                                    <option value="{{ $recipient->id }}" @selected(old('recipient_id', $selectedRecipientId ?? null) == $recipient->id)>
                                        {{ $recipient->name }}
                                        {{ $recipient->username ? '@' . $recipient->username : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-field">
                            <span>Message</span>

                            <textarea
                                name="body"
                                rows="6"
                                required
                                placeholder="Write your first message..."
                            >{{ old('body') }}</textarea>
                        </label>

                        <button type="submit" class="dashboard-add-card">
                            Send message
                        </button>
                    </form>
                @endif
            </section>
@endsection
