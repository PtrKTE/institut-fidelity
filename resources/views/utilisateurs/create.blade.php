@extends('layouts.app')

@section('title', 'Nouvel utilisateur')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('utilisateurs.index') }}" class="btn-fid-ghost btn-sm me-3"><i class="fas fa-arrow-left"></i></a>
    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Nouvel utilisateur</h4>
</div>

<div class="fid-card fid-slide-up" style="max-width:600px;">
    <div class="p-4">
        <form method="POST" action="{{ route('utilisateurs.store') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom') }}" class="fid-input @error('nom') is-invalid @enderror" required>
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prenom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" class="fid-input @error('prenom') is-invalid @enderror" required>
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="fid-input @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}" class="fid-input @error('username') is-invalid @enderror" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" name="mot_de_passe" class="fid-input @error('mot_de_passe') is-invalid @enderror" required minlength="6">
                    @error('mot_de_passe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                    <select name="role" class="fid-input @error('role') is-invalid @enderror" required id="roleSelect">
                        <option value="">Choisir...</option>
                        @foreach(['admin', 'agent', 'compta', 'comm'] as $r)
                            <option value="{{ $r }}" {{ old('role') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3" id="lieuGroup" style="{{ old('role') === 'agent' ? '' : 'display:none;' }}">
                <label class="form-label fw-semibold">Lieu affecte</label>
                <select name="lieu_affecte" class="fid-input">
                    <option value="">—</option>
                    @foreach(['Pronails Vallon', 'Pronails Riviera', 'Pronails Zone 4'] as $l)
                        <option value="{{ $l }}" {{ old('lieu_affecte') == $l ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('utilisateurs.index') }}" class="btn-fid-ghost">Annuler</a>
                <button type="submit" class="btn-fid-primary"><i class="fas fa-save me-1"></i>Creer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#roleSelect').on('change', function() {
    $('#lieuGroup').toggle($(this).val() === 'agent');
});
</script>
@endpush
