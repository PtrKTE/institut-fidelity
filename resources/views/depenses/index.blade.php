@extends('layouts.app')

@section('title', 'Depenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Depenses</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('exports.depenses.excel', request()->query()) }}" class="btn-fid-ghost btn-sm">
            <i class="fas fa-file-excel me-1 text-success"></i>Excel
        </a>
        @if(in_array(auth()->user()->role, ['agent', 'superadmin', 'admin']))
            <button class="btn-fid-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-1"></i>Nouvelle depense
            </button>
        @endif
    </div>
</div>

{{-- Filtres --}}
<div class="fid-card mb-3">
    <form method="GET" class="p-3 d-flex gap-2 flex-wrap align-items-end">
        <div>
            <label class="form-label small">Du</label>
            <input type="date" name="date_start" value="{{ request('date_start') }}" class="fid-input">
        </div>
        <div>
            <label class="form-label small">Au</label>
            <input type="date" name="date_end" value="{{ request('date_end') }}" class="fid-input">
        </div>
        <button class="btn-fid-primary btn-sm"><i class="fas fa-search"></i></button>
        <a href="{{ route('depenses.index') }}" class="btn-fid-ghost btn-sm"><i class="fas fa-times"></i></a>
    </form>
</div>

{{-- Tableau --}}
<div class="fid-card">
    <div class="fid-table-wrapper">
        <table class="fid-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libelle</th>
                    <th>Description</th>
                    <th>Qte</th>
                    <th>Montant</th>
                    <th>Total</th>
                    <th>Agent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($depenses as $d)
                    <tr>
                        <td>{{ $d->date_depense }}</td>
                        <td><strong>{{ $d->libelle }}</strong></td>
                        <td><small>{{ Str::limit($d->description, 50) }}</small></td>
                        <td>{{ $d->quantite }}</td>
                        <td>{{ number_format($d->montant, 0, ',', ' ') }} F</td>
                        <td><strong>{{ number_format($d->total, 0, ',', ' ') }} F</strong></td>
                        <td>{{ $d->agent ? $d->agent->prenom : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4"><div class="fid-empty"><p>Aucune depense</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $depenses->withQueryString()->links() }}</div>
</div>

{{-- Modal ajout --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle(s) depense(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="depenseRows">
                    <div class="row mb-2 depense-row">
                        <div class="col-3"><input type="text" class="fid-input" name="items[0][libelle]" placeholder="Libelle" required></div>
                        <div class="col-3"><input type="text" class="fid-input" name="items[0][description]" placeholder="Description"></div>
                        <div class="col-2"><input type="number" class="fid-input" name="items[0][quantite]" value="1" min="1" required></div>
                        <div class="col-3"><input type="number" class="fid-input" name="items[0][montant]" placeholder="Montant" min="1" required></div>
                        <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger remove-dep"><i class="fas fa-times"></i></button></div>
                    </div>
                </div>
                <button type="button" class="btn-fid-ghost btn-sm" id="addDepRow"><i class="fas fa-plus me-1"></i>Ligne</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-fid-ghost" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn-fid-primary" id="btnSaveDepenses">Enregistrer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
let depIdx = 1;

$('#addDepRow').on('click', function() {
    const i = depIdx++;
    $('#depenseRows').append(`
        <div class="row mb-2 depense-row">
            <div class="col-3"><input type="text" class="fid-input" name="items[${i}][libelle]" placeholder="Libelle" required></div>
            <div class="col-3"><input type="text" class="fid-input" name="items[${i}][description]" placeholder="Description"></div>
            <div class="col-2"><input type="number" class="fid-input" name="items[${i}][quantite]" value="1" min="1" required></div>
            <div class="col-3"><input type="number" class="fid-input" name="items[${i}][montant]" placeholder="Montant" min="1" required></div>
            <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger remove-dep"><i class="fas fa-times"></i></button></div>
        </div>
    `);
});

$(document).on('click', '.remove-dep', function() {
    if ($('.depense-row').length > 1) $(this).closest('.depense-row').remove();
});

$('#btnSaveDepenses').on('click', function() {
    const items = [];
    $('.depense-row').each(function() {
        const row = $(this);
        items.push({
            libelle: row.find('[name$="[libelle]"]').val(),
            description: row.find('[name$="[description]"]').val(),
            quantite: row.find('[name$="[quantite]"]').val(),
            montant: row.find('[name$="[montant]"]').val(),
        });
    });

    $.post("{{ route('depenses.store') }}", { items }, function(res) {
        Toast.fire({ icon: 'success', title: res.message });
        setTimeout(() => location.reload(), 1000);
    }).fail(() => Toast.fire({ icon: 'error', title: 'Erreur' }));
});
</script>
@endpush
