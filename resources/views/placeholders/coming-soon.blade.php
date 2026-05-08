@extends('layouts.app')

@section('title', 'CardFlow | ' . $title)
@section('layout_mode', 'shellless')
@section('body_class', 'cardflow-body')

@section('content')
<main class="cardflow-shell">
            <section class="auth-panel auth-panel--full" aria-label="{{ $title }}">
                <div class="auth-card">
                    <p class="auth-kicker">Coming Soon</p>
                    <h1>{{ $title }}</h1>
                    <p class="auth-copy">{{ $message }}</p>
                    <a href="{{ $backUrl }}" class="submit-button submit-button-link">{{ $backLabel }}</a>
                </div>
            </section>
        </main>
@endsection



