@extends('layouts.cliente')
@section('title', 'Connexion — Prestige by ProNails')
@section('full-page', true)

@section('content')
<div class="cliente-auth">
    <div class="cliente-auth-card">
        <div class="auth-logo">
            <img src="{{ asset('img/lgp.png') }}" alt="ProNails">
        </div>
        <h5 class="auth-title">Connexion</h5>

        @if(session('success'))
            <div class="alert alert-success small py-2 text-center">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger small py-2 text-center">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('espace-cliente.login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-medium">Email</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" class="fid-input ps-5" value="{{ old('email') }}" required autofocus placeholder="votre@email.com">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Mot de passe</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" class="fid-input ps-5 pe-5" id="pwd" required placeholder="Votre mot de passe">
                    <button type="button" class="btn-eye" onclick="togglePwd()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-fid-primary w-100 py-2 mb-3">
                <i class="fas fa-sign-in-alt me-1"></i>Se connecter
            </button>
        </form>

        <div class="text-center">
            <a href="{{ route('espace-cliente.reset-request') }}" class="small text-muted text-decoration-none">
                <i class="fas fa-key me-1"></i>Mot de passe oublie ?
            </a>
        </div>
        <hr class="my-3">
        <div class="text-center">
            <span class="small text-muted">Pas encore de compte ?</span><br>
            <a href="{{ route('espace-cliente.verif-email') }}" class="btn-fid-secondary btn-sm mt-2 d-inline-block">
                <i class="fas fa-user-plus me-1"></i>Creer mon espace
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePwd() {
    const p = document.getElementById('pwd');
    const i = document.getElementById('eyeIcon');
    if (p.type === 'password') { p.type = 'text'; i.classList.replace('fa-eye','fa-eye-slash'); }
    else { p.type = 'password'; i.classList.replace('fa-eye-slash','fa-eye'); }
}
</script>
@endpush
