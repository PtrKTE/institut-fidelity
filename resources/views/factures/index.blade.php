@extends('layouts.app')

@section('title', 'Historique Factures')

@push('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Historique des factures</h4>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn-fid-ghost btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download me-1"></i>Exporter
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" id="exportExcel"><i class="fas fa-file-excel me-2 text-success"></i>Excel</a></li>
                <li><a class="dropdown-item" href="#" id="exportPdf"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF</a></li>
            </ul>
        </div>
        <a href="{{ route('factures.create') }}" class="btn-fid-primary btn-sm">
            <i class="fas fa-cash-register me-1"></i>Caisse
        </a>
    </div>
</div>

{{-- Filtres --}}
<div class="fid-card mb-3">
    <div class="p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Du</label>
                <input type="date" id="filterDateStart" class="fid-input">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Au</label>
                <input type="date" id="filterDateEnd" class="fid-input">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Paiement</label>
                <select id="filterMode" class="fid-input">
                    <option value="">Tous</option>
                    @foreach(\App\Models\ModePaiement::orderBy('nom')->get() as $mp)
                        <option value="{{ $mp->nom }}">{{ $mp->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Lieu</label>
                <select id="filterLieu" class="fid-input">
                    <option value="">Tous</option>
                    <option>Pronails Vallon</option>
                    <option>Pronails Riviera</option>
                    <option>Pronails Zone 4</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Operatrice</label>
                <select id="filterOp" class="fid-input">
                    <option value="">Toutes</option>
                    @foreach($operatrices as $op)
                        <option value="{{ $op->id }}">{{ $op->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn-fid-primary btn-sm" id="btnFilter"><i class="fas fa-search"></i></button>
                <button class="btn-fid-ghost btn-sm" id="btnReset"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>
</div>

{{-- Tableau --}}
<div class="fid-card">
    <div class="fid-table-wrapper">
        <table class="fid-table" id="facturesTable" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Prestations</th>
                    <th>Total</th>
                    <th>Remise</th>
                    <th>Net</th>
                    <th>Paiement</th>
                    <th>Lieu</th>
                    <th>Caissiere</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

{{-- Modal detail --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail facture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="fid-skeleton fid-skeleton-card"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

let table = $('#facturesTable').DataTable({
    ajax: {
        url: "{{ url('/api/factures/list') }}",
        data: function(d) {
            d.date_start = $('#filterDateStart').val();
            d.date_end = $('#filterDateEnd').val();
            d.mode = $('#filterMode').val();
            d.lieu = $('#filterLieu').val();
            d.operatrice = $('#filterOp').val();
        }
    },
    columns: [
        { data: 'id' },
        { data: 'date_heure' },
        { data: 'client' },
        { data: 'prestations', orderable: false },
        { data: 'montant_total' },
        { data: 'remise' },
        { data: 'montant_net' },
        { data: 'mode_paiement' },
        { data: 'lieu' },
        { data: 'caissiere' },
        {
            data: 'id',
            orderable: false,
            render: function(id) {
                return `<div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary btn-view" data-id="${id}"><i class="fas fa-eye"></i></button>
                    <a href="{{ url('/exports/facture') }}/${id}/pdf" class="btn btn-sm btn-outline-secondary" title="PDF"><i class="fas fa-file-pdf"></i></a>
                    <button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fas fa-trash"></i></button>
                </div>`;
            }
        }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    language: {
        search: "Rechercher :",
        lengthMenu: "_MENU_ par page",
        info: "_START_ a _END_ sur _TOTAL_",
        paginate: { previous: "Prec.", next: "Suiv." },
        emptyTable: "Aucune facture",
        zeroRecords: "Aucun resultat"
    }
});

// Filtrer
$('#btnFilter').on('click', () => table.ajax.reload());
$('#btnReset').on('click', function() {
    $('#filterDateStart, #filterDateEnd').val('');
    $('#filterMode, #filterLieu, #filterOp').val('');
    table.ajax.reload();
});

// Voir detail
$(document).on('click', '.btn-view', function() {
    const id = $(this).data('id');
    $('#viewModalBody').html('<div class="text-center py-4"><div class="fid-spinner"></div></div>');
    new bootstrap.Modal('#viewModal').show();

    $.get("{{ url('/api/factures') }}/" + id, function(res) {
        if (res.status === 'success') {
            const f = res.facture;
            let prestasHtml = '';
            res.prestations.forEach(p => {
                prestasHtml += `<tr><td>${p.libelle}</td><td>${Math.round(p.tarif).toLocaleString('fr-FR')} F</td><td>${p.quantite}</td><td>${Math.round(p.montant).toLocaleString('fr-FR')} F</td><td>${p.operatrice?.nom || '—'}</td></tr>`;
            });

            $('#viewModalBody').html(`
                <div class="row mb-3">
                    <div class="col-6"><strong>Client :</strong> ${f.nom_client || '—'}</div>
                    <div class="col-6"><strong>Date :</strong> ${f.date_facture || '—'}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Paiement :</strong> ${f.mode_paiement || '—'}</div>
                    <div class="col-6"><strong>Lieu :</strong> ${f.lieu_prestation || '—'}</div>
                </div>
                <table class="fid-table mb-3">
                    <thead><tr><th>Prestation</th><th>Tarif</th><th>Qte</th><th>Montant</th><th>Operatrice</th></tr></thead>
                    <tbody>${prestasHtml}</tbody>
                </table>
                <div class="fid-card p-3" style="background:var(--color-bg-secondary);">
                    <div class="d-flex justify-content-between"><span>Total</span><span>${Math.round(f.montant_total).toLocaleString('fr-FR')} F</span></div>
                    <div class="d-flex justify-content-between"><span>Remise (${f.taux_remise}%)</span><span>-${Math.round(f.montant_remise).toLocaleString('fr-FR')} F</span></div>
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2" style="color:var(--color-primary);"><span>Net</span><span>${Math.round(f.montant_net).toLocaleString('fr-FR')} F</span></div>
                </div>
            `);
        }
    });
});

// Exports avec filtres
function buildExportUrl(base) {
    const params = new URLSearchParams();
    if ($('#filterDateStart').val()) params.set('date_start', $('#filterDateStart').val());
    if ($('#filterDateEnd').val()) params.set('date_end', $('#filterDateEnd').val());
    if ($('#filterLieu').val()) params.set('lieu', $('#filterLieu').val());
    return base + '?' + params.toString();
}
$('#exportExcel').on('click', function(e) { e.preventDefault(); window.location = buildExportUrl("{{ route('exports.factures.excel') }}"); });
$('#exportPdf').on('click', function(e) { e.preventDefault(); window.location = buildExportUrl("{{ route('exports.factures.pdf') }}"); });

// Supprimer
$(document).on('click', '.btn-del', function() {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Supprimer cette facture ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c4226e',
        confirmButtonText: 'Supprimer',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('/factures') }}/" + id,
                method: 'DELETE',
                success: function(res) {
                    Toast.fire({ icon: 'success', title: 'Facture supprimee' });
                    table.ajax.reload();
                },
                error: function() { Toast.fire({ icon: 'error', title: 'Erreur' }); }
            });
        }
    });
});
</script>
@endpush
