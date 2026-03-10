@extends('layouts.auth')

@section('title', 'Connexion')

@push('styles')
<style>
.fid-login-card {
    max-width: 420px;
    margin: 0 auto;
}
.fid-login-logo {
    height: 60px;
    margin-bottom: var(--space-4);
}
.fid-login-title {
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: var(--space-6);
}
</style>
@endpush

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="fid-card fid-card--elevated fid-login-card fid-fade-in">
        <div class="text-center">
            <img src="{{ asset('img/lgp.png') }}" alt="ProNails" class="fid-login-logo">
            <h4 class="fid-login-title">Prestige by ProNails</h4>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="identifiant" class="form-label fw-medium">
                    <i class="fas fa-user me-1"></i> Identifiant
                </label>
                <input type="text" class="form-control fid-input @error('identifiant') is-invalid @enderror"
                       id="identifiant" name="identifiant" value="{{ old('identifiant') }}"
                       placeholder="Nom, email ou username" required autofocus>
                @error('identifiant')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="mot_de_passe" class="form-label fw-medium">
                    <i class="fas fa-lock me-1"></i> Mot de passe
                </label>
                <div class="input-group">
                    <input type="password" class="form-control fid-input @error('mot_de_passe') is-invalid @enderror"
                           id="mot_de_passe" name="mot_de_passe" placeholder="Votre mot de passe" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePwd">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('mot_de_passe')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-fid-primary w-100 py-2">
                <i class="fas fa-sign-in-alt me-1"></i> Se connecter
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('togglePwd').addEventListener('click', function() {
    const pwd = document.getElementById('mot_de_passe');
    const icon = this.querySelector('i');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
});
</script>
@endpush
