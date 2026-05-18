@extends('layouts.admin')
@section('title','Edit Catalog Card')
@section('content')
<header class="dashboard-header"><div><p class="dashboard-kicker">Admin Panel</p><h1>Edit catalog card</h1><p class="dashboard-intro">{{ $card->title }}</p></div></header>
<form method="POST" action="{{ route('admin.catalog.update',$card) }}" enctype="multipart/form-data" style="display:grid;gap:1rem;">@include('admin.catalog._form')</form>
@endsection
