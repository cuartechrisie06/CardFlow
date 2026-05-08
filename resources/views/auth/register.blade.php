@extends('layouts.app')

@section('title', 'CardFlow | Create account')
@section('layout_mode', 'shellless')
@section('body_class', 'cardflow-body')

@section('content')
<main class="cardflow-shell">
    <section class="auth-panel auth-panel--full" aria-label="Create account"><div class="auth-card">
        <h2 class="text-2xl font-bold mb-4">Create account</h2>
        <p class="mb-6 text-gray-600">Join Cardflow and start your collection journey.</p>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full border rounded px-3 py-2">
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Username -->
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required
                       class="w-full border rounded px-3 py-2">
                @error('username')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border rounded px-3 py-2">
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium">Password</label>
                <input id="password" type="password" name="password" required
                       class="w-full border rounded px-3 py-2">
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="w-full border rounded px-3 py-2">
            </div>

            <!-- Terms -->
            <div class="mb-4 flex items-center">
                <input id="terms" type="checkbox" name="terms" required class="mr-2">
                <label for="terms" class="text-sm">
                    I accept the community guidelines and privacy terms
                </label>
                @error('terms')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit -->
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">
                Create account
            </button>
        </form>
    </div>    </section>
</main>
@endsection

