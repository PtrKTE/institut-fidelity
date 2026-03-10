@extends('layouts.app')

@section('title', 'Nouveau rendez-vous')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('rendezvous.index') }}" class="btn-fid-ghost btn-sm me-3"><i class="fas fa-arrow-left"></i></a>
    <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Nouveau rendez-vous</h4>
</div>

<div class="fid-card fid-slide-up" style="max-width:700px;">
    <div class="p-4">
        <form method="POST" action="{{ route('rendezvous.store') }}">
            @csrf

            {{-- Recherche cliente --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Rechercher une cliente</label>
                <input type="text" id="searchCliente" class="fid-input" placeholder="Nom, telephone...">
                <div id="searchResults" class="list-group mt-1" style="display:none;"></div>
            </div>

            <input type="hidden" name="client_id" id="client_id">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom_client" id="nom_client" value="{{ old('nom_client') }}" class="fid-input" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Telephone <span class="text-danger">*</span></label>
                    <input type="text" name="telephone" id="telephone" value="{{ old('telephone') }}" class="fid-input" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="fid-input">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_rdv" value="{{ old('date_rdv', date('Y-m-d')) }}" class="fid-input" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Heure <span class="text-danger">*</span></label>
                    <input type="time" name="heure_rdv" value="{{ old('heure_rdv', '09:00') }}" class="fid-input" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Lieu <span class="text-danger">*</span></label>
                    <select name="lieu" class="fid-input" required>
                        <option value="Pronails Vallon" {{ old('lieu') == 'Pronails Vallon' ? 'selected' : '' }}>Pronails Vallon</option>
                        <option value="Pronails Riviera" {{ old('lieu') == 'Pronails Riviera' ? 'selected' : '' }}>Pronails Riviera</option>
                        <option value="Pronails Zone 4" {{ old('lieu') == 'Pronails Zone 4' ? 'selected' : '' }}>Pronails Zone 4</option>
                        <option value="Domicile" {{ old('lieu') == 'Domicile' ? 'selected' : '' }}>Domicile</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Prestation(s) <span class="text-danger">*</span></label>
                <select name="prestation" class="fid-input" required>
                    <option value="">Choisir...</option>
                    @foreach($prestations as $p)
                        <option value="{{ $p->libelle }}" {{ old('prestation') == $p->libelle ? 'selected' : '' }}>{{ $p->libelle }}</option>
                    @endforeach
                    <option value="Autre">Autre</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" class="fid-input" rows="2" maxlength="500">{{ old('commentaire') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('rendezvous.index') }}" class="btn-fid-ghost">Annuler</a>
                <button type="submit" class="btn-fid-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchTimeout;
$('#searchCliente').on('input', function() {
    clearTimeout(searchTimeout);
    const q = $(this).val().trim();
    if (q.length < 2) { $('#searchResults').hide(); return; }

    searchTimeout = setTimeout(() => {
        $.get("{{ url('/api/clientes/recherche-rdv') }}", { q }, function(data) {
            if (!data.length) { $('#searchResults').hide(); return; }
            let html = '';
            data.forEach(c => {
                html += `<a href="#" class="list-group-item list-group-item-action search-result"
                    data-id="${c.id}" data-nom="${c.prenom} ${c.nom}" data-tel="${c.telephone || ''}" data-email="${c.email || ''}">
                    ${c.prenom} ${c.nom} — ${c.telephone || ''}
                </a>`;
            });
            $('#searchResults').html(html).show();
        });
    }, 300);
});

$(document).on('click', '.search-result', function(e) {
    e.preventDefault();
    $('#client_id').val($(this).data('id'));
    $('#nom_client').val($(this).data('nom'));
    $('#telephone').val($(this).data('tel'));
    $('#email').val($(this).data('email'));
    $('#searchResults').hide();
    $('#searchCliente').val($(this).data('nom'));
});
</script>
@endpush
