@extends('layouts.app')

@section('title', 'Caisse')

@push('styles')
<style>
    .fid-caisse-recap { background: var(--color-bg-secondary, #f8f9fa); border-radius: var(--radius-md); padding: 1rem; }
    .fid-caisse-recap .fid-recap-row { display: flex; justify-content: space-between; padding: 0.3rem 0; }
    .fid-caisse-recap .fid-recap-total { font-size: 1.3rem; font-weight: 700; color: var(--color-primary); border-top: 2px solid var(--color-primary); margin-top: 0.5rem; padding-top: 0.5rem; }
    .fid-service-row { background: var(--color-bg-card, #fff); border-radius: var(--radius-sm); padding: 0.75rem; margin-bottom: 0.5rem; border: 1px solid var(--color-border, #e5e7eb); }
    .fid-service-row:hover { border-color: var(--color-primary-light); }
    .btn-remove-row { color: var(--color-danger, #ef4444); background: none; border: none; cursor: pointer; font-size: 1.1rem; }
    .btn-remove-row:hover { color: #dc2626; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-cash-register me-2"></i>Caisse</h4>
    <a href="{{ route('factures.index') }}" class="btn-fid-ghost btn-sm">
        <i class="fas fa-history me-1"></i>Historique
    </a>
</div>

<form id="formCaisse">
    <div class="row">
        {{-- Colonne gauche : Client + Prestations --}}
        <div class="col-lg-8 mb-4">
            {{-- Recherche cliente --}}
            <div class="fid-card mb-3 fid-slide-up">
                <div class="p-3">
                    <h6 class="fw-bold mb-3"><i class="fas fa-user-search me-2"></i>Cliente</h6>
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-2">
                            <label class="form-label">Recherche (carte, tel, code-barres)</label>
                            <div class="input-group">
                                <input type="text" id="searchCliente" class="fid-input" placeholder="Rechercher...">
                                <button type="button" class="btn btn-outline-secondary" id="btnSearch">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="anonyme" name="anonyme" value="1">
                                <label class="form-check-label" for="anonyme">Cliente anonyme</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted" id="clienteInfo" style="display:none;">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <span id="clienteInfoText"></span>
                            </small>
                        </div>
                    </div>
                    <input type="hidden" name="client_id" id="client_id">
                    <input type="hidden" name="nom_client" id="nom_client">
                    <input type="hidden" name="telephone_client" id="telephone_client">
                    <input type="hidden" name="taux_remise" id="taux_remise" value="0">
                </div>
            </div>

            {{-- Prestations --}}
            <div class="fid-card fid-slide-up">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-concierge-bell me-2"></i>Prestations</h6>
                        <button type="button" class="btn-fid-secondary btn-sm" id="btnAddRow">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>

                    <div id="serviceRows">
                        <div class="fid-service-row" data-index="0">
                            <div class="row align-items-center">
                                <div class="col-md-4 mb-2">
                                    <select class="fid-input presta-select" name="prestations[0][id]" required>
                                        <option value="">Choisir...</option>
                                        @foreach($prestations as $p)
                                            <option value="{{ $p->id }}" data-tarif="{{ $p->tarif }}">{{ $p->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <input type="number" class="fid-input tarif-input" name="prestations[0][tarif]" placeholder="Tarif" readonly>
                                </div>
                                <div class="col-md-1 mb-2">
                                    <input type="number" class="fid-input qte-input" name="prestations[0][quantite]" value="1" min="1">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <input type="text" class="fid-input montant-display" readonly placeholder="Montant">
                                    <input type="hidden" class="montant-input" name="prestations[0][montant]" value="0">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select class="fid-input op-select" name="prestations[0][operatrice_id]">
                                        <option value="">Operatrice</option>
                                        @foreach($operatrices as $op)
                                            <option value="{{ $op->id }}">{{ $op->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1 mb-2 text-center">
                                    <button type="button" class="btn-remove-row" title="Retirer"><i class="fas fa-times-circle"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonne droite : Recap + Paiement --}}
        <div class="col-lg-4 mb-4">
            <div class="fid-card fid-slide-up" style="position:sticky; top:1rem;">
                <div class="p-3">
                    <h6 class="fw-bold mb-3"><i class="fas fa-receipt me-2"></i>Resume</h6>

                    <div class="mb-3">
                        <label class="form-label">Mode de paiement</label>
                        <select name="mode_paiement" class="fid-input" required>
                            @foreach($modesPaiement as $mp)
                                <option value="{{ $mp->nom }}">{{ $mp->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lieu de prestation</label>
                        <input type="text" name="lieu_prestation" class="fid-input"
                               value="{{ auth()->user()->lieu_affecte ?? '' }}" required>
                    </div>

                    <div class="fid-caisse-recap">
                        <div class="fid-recap-row">
                            <span>Total HT</span>
                            <span id="recapTotal">0 F</span>
                        </div>
                        <div class="fid-recap-row">
                            <span>Remise (<span id="recapTaux">0</span>%)</span>
                            <span id="recapRemise">0 F</span>
                        </div>
                        <div class="fid-recap-row fid-recap-total">
                            <span>Net a payer</span>
                            <span id="recapNet">0 F</span>
                        </div>
                    </div>

                    <input type="hidden" name="montant_total" id="montant_total" value="0">
                    <input type="hidden" name="montant_remise" id="montant_remise" value="0">
                    <input type="hidden" name="montant_net" id="montant_net" value="0">

                    <button type="submit" class="btn-fid-primary w-100 mt-3" id="btnSubmit">
                        <i class="fas fa-check me-1"></i>Enregistrer la facture
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
let rechercheOk = false;
let rowIndex = 1;

function formatMoney(n) {
    return Math.round(n).toLocaleString('fr-FR').replace(/\u202F/g, ' ') + ' F';
}

// Recherche cliente
$('#btnSearch, #searchCliente').on('click keypress', function(e) {
    if (e.type === 'keypress' && e.which !== 13) return;
    e.preventDefault();
    const q = $('#searchCliente').val().trim();
    if (!q) return;

    $.post("{{ url('/api/clientes/recherche') }}", { query: q }, function(res) {
        if (res.status === 'success' && res.cliente) {
            const c = res.cliente;
            $('#client_id').val(c.id);
            $('#nom_client').val(c.prenom + ' ' + c.nom);
            $('#telephone_client').val(c.telephone);
            $('#taux_remise').val(c.taux_reduction || 0);
            $('#recapTaux').text(c.taux_reduction || 0);
            $('#clienteInfoText').text(c.prenom + ' ' + c.nom + ' (' + (c.taux_reduction || 0) + '%)');
            $('#clienteInfo').show();
            rechercheOk = true;
            updateRecap();
        } else {
            Toast.fire({ icon: 'warning', title: res.message || 'Aucune cliente trouvee' });
        }
    }).fail(() => Toast.fire({ icon: 'error', title: 'Erreur de recherche' }));
});

// Anonyme toggle
$('#anonyme').on('change', function() {
    const anon = this.checked;
    $('#searchCliente').prop('disabled', anon);
    if (anon) {
        $('#client_id').val('');
        $('#nom_client').val('Client anonyme');
        $('#telephone_client').val('');
        $('#taux_remise').val(0);
        $('#recapTaux').text(0);
        $('#clienteInfo').hide();
        rechercheOk = true;
        updateRecap();
    } else {
        rechercheOk = false;
    }
});

// Ajouter ligne prestation
$('#btnAddRow').on('click', function() {
    const idx = rowIndex++;
    const row = `
    <div class="fid-service-row" data-index="${idx}">
        <div class="row align-items-center">
            <div class="col-md-4 mb-2">
                <select class="fid-input presta-select" name="prestations[${idx}][id]" required>
                    <option value="">Choisir...</option>
                    @foreach($prestations as $p)
                    <option value="{{ $p->id }}" data-tarif="{{ $p->tarif }}">{{ $p->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <input type="number" class="fid-input tarif-input" name="prestations[${idx}][tarif]" placeholder="Tarif" readonly>
            </div>
            <div class="col-md-1 mb-2">
                <input type="number" class="fid-input qte-input" name="prestations[${idx}][quantite]" value="1" min="1">
            </div>
            <div class="col-md-2 mb-2">
                <input type="text" class="fid-input montant-display" readonly placeholder="Montant">
                <input type="hidden" class="montant-input" name="prestations[${idx}][montant]" value="0">
            </div>
            <div class="col-md-2 mb-2">
                <select class="fid-input op-select" name="prestations[${idx}][operatrice_id]">
                    <option value="">Operatrice</option>
                    @foreach($operatrices as $op)
                    <option value="{{ $op->id }}">{{ $op->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 mb-2 text-center">
                <button type="button" class="btn-remove-row" title="Retirer"><i class="fas fa-times-circle"></i></button>
            </div>
        </div>
    </div>`;
    $('#serviceRows').append(row);
});

// Supprimer ligne
$(document).on('click', '.btn-remove-row', function() {
    if ($('.fid-service-row').length <= 1) return;
    $(this).closest('.fid-service-row').remove();
    updateRecap();
});

// Selection prestation -> remplir tarif
$(document).on('change', '.presta-select', function() {
    const tarif = $(this).find(':selected').data('tarif') || 0;
    const row = $(this).closest('.fid-service-row');
    row.find('.tarif-input').val(tarif);
    calcRow(row);
});

// Changement quantite
$(document).on('input', '.qte-input', function() {
    calcRow($(this).closest('.fid-service-row'));
});

function calcRow(row) {
    const tarif = parseFloat(row.find('.tarif-input').val()) || 0;
    const qte = parseInt(row.find('.qte-input').val()) || 1;
    const montant = tarif * qte;
    row.find('.montant-display').val(formatMoney(montant));
    row.find('.montant-input').val(montant);
    updateRecap();
}

function updateRecap() {
    let total = 0;
    $('.montant-input').each(function() { total += parseFloat($(this).val()) || 0; });

    const taux = parseFloat($('#taux_remise').val()) || 0;
    const remise = total * taux / 100;
    const net = total - remise;

    $('#recapTotal').text(formatMoney(total));
    $('#recapRemise').text(formatMoney(remise));
    $('#recapNet').text(formatMoney(net));

    $('#montant_total').val(total);
    $('#montant_remise').val(remise);
    $('#montant_net').val(net);
}

// Soumission
$('#formCaisse').on('submit', function(e) {
    e.preventDefault();

    if (!rechercheOk) {
        return Toast.fire({ icon: 'warning', title: 'Veuillez rechercher une cliente ou cocher "anonyme"' });
    }

    // Verifier qu'au moins une prestation est selectionnee
    let hasPresta = false;
    $('.presta-select').each(function() { if ($(this).val()) hasPresta = true; });
    if (!hasPresta) {
        return Toast.fire({ icon: 'warning', title: 'Ajoutez au moins une prestation' });
    }

    const net = parseFloat($('#montant_net').val()) || 0;

    Swal.fire({
        title: 'Confirmer la facture ?',
        html: `<strong>${$('#nom_client').val()}</strong><br>Net a payer : <strong>${formatMoney(net)}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#c4226e',
        confirmButtonText: 'Enregistrer',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (!result.isConfirmed) return;

        $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement...');

        $.ajax({
            url: "{{ route('factures.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Facture enregistree !',
                        text: 'Facture #' + res.invoice_id,
                        confirmButtonColor: '#c4226e',
                    }).then(() => location.reload());
                }
            },
            error: function() {
                Toast.fire({ icon: 'error', title: 'Erreur lors de l\'enregistrement' });
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-check me-1"></i>Enregistrer la facture');
            }
        });
    });
});
</script>
@endpush
