@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h2 class="mb-4">Dashboard {{ ucfirst(auth()->user()->role) }}</h2>
<div class="row g-3">
    <div class="col-12">
        <div class="fid-card p-4">
            <p>Bienvenue, <strong>{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</strong></p>
            <p class="text-muted">Module en cours de developpement.</p>
        </div>
    </div>
</div>
@endsection
