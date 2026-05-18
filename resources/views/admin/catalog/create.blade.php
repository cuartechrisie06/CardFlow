@extends('layouts.admin')
@section('title','Add Catalog Card')
@section('content')
<header class="dashboard-header"><div><p class="dashboard-kicker">Admin Panel</p><h1>Add catalog card</h1><p class="dashboard-intro">Create a master catalog card.</p></div></header>
<form method="POST" action="{{ route('admin.catalog.store') }}" enctype="multipart/form-data" style="display:grid;gap:1rem;">@include('admin.catalog._form')</form>
@endsection
