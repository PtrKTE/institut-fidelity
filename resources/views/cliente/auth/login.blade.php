@extends('layouts.cliente')
@section('title', 'Connexion — Prestige by ProNails')

@section('content')
<div class="cliente-auth" style="min-height:100vh;padding-bottom:0;">
    <div class="cliente-auth-card">
        <div class="auth-logo">
            <img src="{{ url('/public/img/lgp.png') }}" alt="ProNails" style="height:48px">
        </div>
        <h5 class="auth-title">Connexion</h5>

        @if(session('success'))
            <div class="alert alert-success small py-2">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('espace-cliente.login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small">Email</label>
                <input type="email" name="email" class="fid-input" value="{{ old('email') }}" required autofocus placeholder="votre@email.com">
            </div>
            <div class="mb-3">
                <label class="form-label small">Mot de passe</label>
                <div class="position-relative">
                    <input type="password" name="password" class="fid-input" id="pwd" required placeholder="••••••">
                    <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 text-muted" onclick="togglePwd()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-fid-primary w-100 py-2 mb-3">Se connecter</button>
        </form>

        <div class="text-center">
            <a href="{{ route('espace-cliente.reset-request') }}" class="small text-muted">Mot de passe oublie ?</a>
        </div>
        <hr>
        <div class="text-center">
            <span class="small text-muted">Pas encore de compte ?</span><br>
            <a href="{{ route('espace-cliente.verif-email') }}" class="btn-fid-secondary btn-sm mt-2">Creer mon espace</a>
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
