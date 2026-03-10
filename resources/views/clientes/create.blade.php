@extends('layouts.app')

@section('title', 'Nouvelle cliente')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('clientes.index') }}" class="btn-fid-ghost btn-sm me-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Nouvelle cliente</h4>
</div>

<div class="fid-card fid-slide-up" style="max-width: 700px;">
    <div class="p-4">
        <form method="POST" action="{{ route('clientes.store') }}" id="formCliente">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom') }}" class="fid-input @error('nom') is-invalid @enderror" required maxlength="100">
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prenom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" class="fid-input @error('prenom') is-invalid @enderror" required maxlength="100">
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Telephone <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select id="indicatif" class="fid-input" style="max-width:100px;">
                            <option value="+225" selected>+225</option>
                            <option value="+33">+33</option>
                            <option value="+1">+1</option>
                        </select>
                        <input type="text" name="telephone" id="telephone" value="{{ old('telephone') }}" class="fid-input @error('telephone') is-invalid @enderror" required maxlength="30" placeholder="0700000000">
                    </div>
                    @error('telephone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="fid-input @error('email') is-invalid @enderror" maxlength="150" placeholder="cliente@email.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date d'anniversaire</label>
                    <input type="date" name="date_anniversaire" value="{{ old('date_anniversaire') }}" class="fid-input">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lieu d'enregistrement <span class="text-danger">*</span></label>
                    @if(auth()->user()->role === 'agent')
                        <input type="text" name="lieu_enregistrement" value="{{ auth()->user()->lieu_affecte }}" class="fid-input" readonly>
                    @else
                        <select name="lieu_enregistrement" class="fid-input @error('lieu_enregistrement') is-invalid @enderror" id="lieuSelect" required>
                            <option value="">Choisir...</option>
                            <option value="Pronails Vallon" {{ old('lieu_enregistrement') == 'Pronails Vallon' ? 'selected' : '' }}>Pronails Vallon</option>
                            <option value="Pronails Riviera" {{ old('lieu_enregistrement') == 'Pronails Riviera' ? 'selected' : '' }}>Pronails Riviera</option>
                            <option value="Pronails Zone 4" {{ old('lieu_enregistrement') == 'Pronails Zone 4' ? 'selected' : '' }}>Pronails Zone 4</option>
                            <option value="__other__" {{ old('lieu_enregistrement') && !in_array(old('lieu_enregistrement'), ['Pronails Vallon', 'Pronails Riviera', 'Pronails Zone 4']) ? 'selected' : '' }}>Autre...</option>
                        </select>
                        <input type="text" id="lieuAutre" name="lieu_enregistrement_autre"
                               value="{{ old('lieu_enregistrement_autre') }}"
                               class="fid-input mt-2" placeholder="Preciser le lieu..."
                               style="display: {{ old('lieu_enregistrement') && !in_array(old('lieu_enregistrement'), ['Pronails Vallon', 'Pronails Riviera', 'Pronails Zone 4', '']) ? 'block' : 'none' }};">
                        @error('lieu_enregistrement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('clientes.index') }}" class="btn-fid-ghost">Annuler</a>
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
// Lieu "Autre" toggle
$('#lieuSelect').on('change', function() {
    const isOther = $(this).val() === '__other__';
    $('#lieuAutre').toggle(isOther);
    if (isOther) $('#lieuAutre').focus();
});

// Si "Autre" est selectionne, remplacer la valeur du select par la valeur saisie
$('#formCliente').on('submit', function() {
    if ($('#lieuSelect').val() === '__other__') {
        const val = $('#lieuAutre').val().trim();
        if (!val) { alert('Veuillez preciser le lieu.'); return false; }
        $('#lieuSelect').append(`<option value="${val}" selected>${val}</option>`);
    }
});

// Phone mask par indicatif
const phoneLengths = { '+225': 10, '+33': 9, '+1': 10 };
$('#indicatif').on('change', function() {
    const max = phoneLengths[$(this).val()] || 15;
    $('#telephone').attr('maxlength', max).attr('placeholder', '0'.repeat(max));
});

// Nettoyer le telephone
$('#telephone').on('input', function() {
    this.value = this.value.replace(/\D/g, '');
});
</script>
@endpush
