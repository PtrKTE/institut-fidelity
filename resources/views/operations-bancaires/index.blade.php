@extends('layouts.app')

@section('title', 'Operations bancaires')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-university me-2"></i>Operations bancaires</h4>
    @if(in_array(auth()->user()->role, ['agent', 'superadmin', 'admin']))
        <button class="btn-fid-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus me-1"></i>Nouvelle operation
        </button>
    @endif
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
        <div>
            <label class="form-label small">Type</label>
            <select name="type" class="fid-input">
                <option value="">Tous</option>
                <option value="versement_especes" {{ request('type') == 'versement_especes' ? 'selected' : '' }}>Versement especes</option>
                <option value="remise_cheque" {{ request('type') == 'remise_cheque' ? 'selected' : '' }}>Remise cheque</option>
            </select>
        </div>
        <div>
            <label class="form-label small">Banque</label>
            <select name="banque" class="fid-input">
                <option value="">Toutes</option>
                @foreach(['NSIA', 'BICICI', 'SIB'] as $b)
                    <option value="{{ $b }}" {{ request('banque') == $b ? 'selected' : '' }}>{{ $b }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-fid-primary btn-sm"><i class="fas fa-search"></i></button>
        <a href="{{ route('operations-bancaires.index') }}" class="btn-fid-ghost btn-sm"><i class="fas fa-times"></i></a>
    </form>
</div>

{{-- Tableau --}}
<div class="fid-card">
    <div class="fid-table-wrapper">
        <table class="fid-table">
            <thead>
                <tr>
                    <th>Date op.</th>
                    <th>Type</th>
                    <th>Banque</th>
                    <th>Operateur</th>
                    <th>Montant</th>
                    <th>Agent</th>
                    <th>Batch</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operations as $op)
                    <tr>
                        <td>{{ $op->date_operation }}</td>
                        <td>
                            <span class="fid-badge {{ $op->type_operation === 'versement_especes' ? 'fid-badge-success' : 'fid-badge-info' }}">
                                {{ $op->type_operation === 'versement_especes' ? 'Versement' : 'Cheque' }}
                            </span>
                        </td>
                        <td>{{ $op->banque }}</td>
                        <td>{{ $op->nom_operateur }}</td>
                        <td><strong>{{ number_format($op->montant_operation, 0, ',', ' ') }} F</strong></td>
                        <td>{{ $op->agent ? $op->agent->prenom : '—' }}</td>
                        <td><small class="text-muted">{{ Str::limit($op->batch_id, 8) }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4"><div class="fid-empty"><p>Aucune operation</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $operations->withQueryString()->links() }}</div>
</div>

{{-- Modal ajout --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle(s) operation(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="opRows">
                    <div class="row mb-2 op-row g-2">
                        <div class="col-2">
                            <select class="fid-input" name="items[0][type_operation]" required>
                                <option value="versement_especes">Versement</option>
                                <option value="remise_cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <select class="fid-input" name="items[0][banque]" required>
                                <option value="NSIA">NSIA</option>
                                <option value="BICICI">BICICI</option>
                                <option value="SIB">SIB</option>
                            </select>
                        </div>
                        <div class="col-2"><input type="text" class="fid-input" name="items[0][nom_operateur]" placeholder="Operateur" value="Henri Joel"></div>
                        <div class="col-2"><input type="number" class="fid-input" name="items[0][montant_operation]" placeholder="Montant" required min="1"></div>
                        <div class="col-3"><input type="date" class="fid-input" name="items[0][date_operation]" value="{{ date('Y-m-d') }}" required></div>
                        <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger remove-op"><i class="fas fa-times"></i></button></div>
                    </div>
                </div>
                <button type="button" class="btn-fid-ghost btn-sm" id="addOpRow"><i class="fas fa-plus me-1"></i>Ligne</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-fid-ghost" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn-fid-primary" id="btnSaveOps">Enregistrer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
let opIdx = 1;

$('#addOpRow').on('click', function() {
    const i = opIdx++;
    $('#opRows').append(`
        <div class="row mb-2 op-row g-2">
            <div class="col-2"><select class="fid-input" name="items[${i}][type_operation]" required><option value="versement_especes">Versement</option><option value="remise_cheque">Cheque</option></select></div>
            <div class="col-2"><select class="fid-input" name="items[${i}][banque]" required><option value="NSIA">NSIA</option><option value="BICICI">BICICI</option><option value="SIB">SIB</option></select></div>
            <div class="col-2"><input type="text" class="fid-input" name="items[${i}][nom_operateur]" placeholder="Operateur" value="Henri Joel"></div>
            <div class="col-2"><input type="number" class="fid-input" name="items[${i}][montant_operation]" placeholder="Montant" required min="1"></div>
            <div class="col-3"><input type="date" class="fid-input" name="items[${i}][date_operation]" value="{{ date('Y-m-d') }}" required></div>
            <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger remove-op"><i class="fas fa-times"></i></button></div>
        </div>
    `);
});

$(document).on('click', '.remove-op', function() {
    if ($('.op-row').length > 1) $(this).closest('.op-row').remove();
});

$('#btnSaveOps').on('click', function() {
    const items = [];
    $('.op-row').each(function() {
        const row = $(this);
        items.push({
            type_operation: row.find('[name$="[type_operation]"]').val(),
            banque: row.find('[name$="[banque]"]').val(),
            nom_operateur: row.find('[name$="[nom_operateur]"]').val(),
            montant_operation: row.find('[name$="[montant_operation]"]').val(),
            date_operation: row.find('[name$="[date_operation]"]').val(),
        });
    });

    $.post("{{ route('operations-bancaires.store') }}", { items }, function(res) {
        Toast.fire({ icon: 'success', title: `${res.inserted} operation(s) enregistree(s)` });
        setTimeout(() => location.reload(), 1000);
    }).fail(() => Toast.fire({ icon: 'error', title: 'Erreur' }));
});
</script>
@endpush
