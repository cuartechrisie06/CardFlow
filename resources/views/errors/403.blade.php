@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div style="text-align:center;padding:80px 20px;">
    <div style="font-size:3rem;margin-bottom:16px;">!</div>
    <h1 style="font-family:'Playfair Display',serif;font-size:2rem;color:#3d2b1f;">
        Access Denied
    </h1>
    <p style="font-family:'DM Sans',sans-serif;color:#8B6F5E;margin:12px 0 24px;">
        You do not have permission to view this page.
    </p>
    <a href="{{ route('dashboard') }}"
       style="font-family:'DM Sans',sans-serif;background:#8B4513;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:600;">
        Back to Dashboard
    </a>
</div>
@endsection
