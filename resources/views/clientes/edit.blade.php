@extends('layouts.app')

@section('title', 'Modifier ' . $cliente->prenom . ' ' . $cliente->nom)

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('clientes.show', $cliente) }}" class="btn-fid-ghost btn-sm me-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier : {{ $cliente->prenom }} {{ $cliente->nom }}</h4>
</div>

<div class="fid-card fid-slide-up" style="max-width: 700px;">
    <div class="p-4">
        <form method="POST" action="{{ route('clientes.update', $cliente) }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom', $cliente->nom) }}" class="fid-input @error('nom') is-invalid @enderror" required maxlength="100">
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prenom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom', $cliente->prenom) }}" class="fid-input @error('prenom') is-invalid @enderror" required maxlength="100">
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Telephone <span class="text-danger">*</span></label>
                    <input type="text" name="telephone" value="{{ old('telephone', $cliente->telephone) }}" class="fid-input @error('telephone') is-invalid @enderror" required maxlength="30">
                    @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}" class="fid-input @error('email') is-invalid @enderror" maxlength="150">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date d'anniversaire</label>
                    <input type="date" name="date_anniversaire" value="{{ old('date_anniversaire', $cliente->date_anniversaire?->format('Y-m-d')) }}" class="fid-input">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lieu d'enregistrement <span class="text-danger">*</span></label>
                    <select name="lieu_enregistrement" class="fid-input @error('lieu_enregistrement') is-invalid @enderror" required>
                        @foreach(['Pronails Vallon', 'Pronails Riviera', 'Pronails Zone 4'] as $lieu)
                            <option value="{{ $lieu }}" {{ old('lieu_enregistrement', $cliente->lieu_enregistrement) == $lieu ? 'selected' : '' }}>{{ $lieu }}</option>
                        @endforeach
                    </select>
                    @error('lieu_enregistrement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-3">

            <h6 class="fw-bold mb-3"><i class="fas fa-credit-card me-2"></i>Identifiants carte</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Numero de carte</label>
                    <div class="input-group">
                        <input type="text" name="numero_carte" id="numeroCarte" value="{{ old('numero_carte', $cliente->numero_carte) }}" class="fid-input">
                        <button type="button" class="btn btn-outline-secondary" id="btnRegenCarte" title="Regenerer">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Code-barres</label>
                    <div class="input-group">
                        <input type="text" name="code_barres" id="codeBarres" value="{{ old('code_barres', $cliente->code_barres) }}" class="fid-input">
                        <button type="button" class="btn btn-outline-secondary" id="btnRegenBarcode" title="Regenerer">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('clientes.show', $cliente) }}" class="btn-fid-ghost">Annuler</a>
                <button type="submit" class="btn-fid-primary">
                    <i class="fas fa-save me-1"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Regenerer identifiants via API
$('#btnRegenCarte, #btnRegenBarcode').on('click', function() {
    $.get("{{ url('/api/clientes/generer-identifiants') }}", function(data) {
        $('#numeroCarte').val(data.numero);
        $('#codeBarres').val(data.code);
    });
});
</script>
@endpush
