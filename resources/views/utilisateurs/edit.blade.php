@extends('layouts.app')

@section('title', 'Modifier ' . $utilisateur->prenom)

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('utilisateurs.index') }}" class="btn-fid-ghost btn-sm me-3"><i class="fas fa-arrow-left"></i></a>
    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier : {{ $utilisateur->prenom }} {{ $utilisateur->nom }}</h4>
</div>

<div class="fid-card fid-slide-up" style="max-width:600px;">
    <div class="p-4">
        <form method="POST" action="{{ route('utilisateurs.update', $utilisateur) }}">
            @csrf @method('PUT')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom</label>
                    <input type="text" name="nom" value="{{ old('nom', $utilisateur->nom) }}" class="fid-input @error('nom') is-invalid @enderror" required>
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prenom</label>
                    <input type="text" name="prenom" value="{{ old('prenom', $utilisateur->prenom) }}" class="fid-input @error('prenom') is-invalid @enderror" required>
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email', $utilisateur->email) }}" class="fid-input @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" value="{{ old('username', $utilisateur->username) }}" class="fid-input @error('username') is-invalid @enderror" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nouveau mot de passe <small class="text-muted">(laisser vide si inchange)</small></label>
                    <input type="password" name="mot_de_passe" class="fid-input" minlength="6">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role</label>
                    <select name="role" class="fid-input" required id="roleSelect">
                        @foreach(['admin', 'agent', 'compta', 'comm'] as $r)
                            <option value="{{ $r }}" {{ old('role', $utilisateur->role) == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3" id="lieuGroup" style="{{ old('role', $utilisateur->role) === 'agent' ? '' : 'display:none;' }}">
                <label class="form-label fw-semibold">Lieu affecte</label>
                <select name="lieu_affecte" class="fid-input">
                    <option value="">—</option>
                    @foreach(['Pronails Vallon', 'Pronails Riviera', 'Pronails Zone 4'] as $l)
                        <option value="{{ $l }}" {{ old('lieu_affecte', $utilisateur->lieu_affecte) == $l ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('utilisateurs.index') }}" class="btn-fid-ghost">Annuler</a>
                <button type="submit" class="btn-fid-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>$('#roleSelect').on('change', function() { $('#lieuGroup').toggle($(this).val() === 'agent'); });</script>
@endpush
