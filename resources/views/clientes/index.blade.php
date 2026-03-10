@extends('layouts.app')

@section('title', 'Clientes')

@push('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .fid-taux-select { width: 80px; font-size: 0.85rem; padding: 2px 4px; }
    .fid-check-all { width: 18px; height: 18px; }
    .fid-cliente-row td { vertical-align: middle; }
    .fid-actions-bar { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
    .fid-filter-bar { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1rem; }
    .badge-active { background: var(--color-success, #22c55e); }
    .badge-inactive { background: var(--color-danger, #ef4444); }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Clientes</h4>
    <div class="fid-actions-bar">
        @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
            <button class="btn-fid-secondary btn-sm" id="btnBulkTaux" style="display:none;">
                <i class="fas fa-percent me-1"></i>Taux multiple
            </button>
            <button class="btn-fid-secondary btn-sm" id="btnBulkDelete" style="display:none;" data-bs-toggle="tooltip" title="Supprimer la selection">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        @endif
        <a href="{{ route('clientes.create') }}" class="btn-fid-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Nouvelle cliente
        </a>
    </div>
</div>

{{-- Filtres --}}
<div class="fid-card mb-3">
    <form method="GET" action="{{ route('clientes.index') }}" class="fid-filter-bar p-3">
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="fid-input" style="width:220px;">
        </div>
        <div>
            <select name="lieu" class="fid-input" style="width:180px;">
                <option value="">Tous les lieux</option>
                <option value="Pronails Vallon" {{ request('lieu') == 'Pronails Vallon' ? 'selected' : '' }}>Pronails Vallon</option>
                <option value="Pronails Riviera" {{ request('lieu') == 'Pronails Riviera' ? 'selected' : '' }}>Pronails Riviera</option>
                <option value="Pronails Zone 4" {{ request('lieu') == 'Pronails Zone 4' ? 'selected' : '' }}>Pronails Zone 4</option>
            </select>
        </div>
        <div>
            <select name="statut" class="fid-input" style="width:140px;">
                <option value="">Tous statuts</option>
                <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actives</option>
                <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactives</option>
            </select>
        </div>
        <button type="submit" class="btn-fid-primary btn-sm"><i class="fas fa-search"></i></button>
        <a href="{{ route('clientes.index') }}" class="btn-fid-ghost btn-sm"><i class="fas fa-times"></i></a>
    </form>
</div>

{{-- Tableau --}}
<div class="fid-card">
    <div class="fid-table-wrapper">
        <table class="fid-table" id="clientesTable">
            <thead>
                <tr>
                    @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
                        <th style="width:40px;"><input type="checkbox" class="fid-check-all" id="checkAll"></th>
                    @endif
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Telephone</th>
                    <th>Email</th>
                    <th>Lieu</th>
                    <th>Taux</th>
                    <th>Statut</th>
                    <th>Carte</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                    <tr class="fid-cliente-row">
                        @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
                            <td><input type="checkbox" class="fid-check-item" value="{{ $cliente->id }}"></td>
                        @endif
                        <td><strong>{{ $cliente->nom }}</strong></td>
                        <td>{{ $cliente->prenom }}</td>
                        <td>{{ $cliente->telephone }}</td>
                        <td>{{ $cliente->email ?? '—' }}</td>
                        <td><small>{{ $cliente->lieu_enregistrement }}</small></td>
                        <td>
                            @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
                                <select class="fid-input fid-taux-select" data-id="{{ $cliente->id }}" onchange="updateTaux(this)">
                                    @foreach([0, 10, 15, 20, 25, 30, 35, 40, 45, 50] as $t)
                                        <option value="{{ $t }}" {{ (float)$cliente->taux_reduction == $t ? 'selected' : '' }}>{{ $t }}%</option>
                                    @endforeach
                                </select>
                            @else
                                {{ $cliente->taux_reduction }}%
                            @endif
                        </td>
                        <td>
                            <span class="fid-badge {{ $cliente->active ? 'fid-badge-success' : 'fid-badge-danger' }}">
                                {{ $cliente->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $cliente->numero_carte }}</small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
                                    <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $cliente->id }}" data-nom="{{ $cliente->prenom }} {{ $cliente->nom }}" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="fid-empty">
                                <i class="fas fa-users fa-3x mb-3" style="color:var(--color-text-muted);"></i>
                                <p>Aucune cliente trouvee</p>
                                <a href="{{ route('clientes.create') }}" class="btn-fid-primary btn-sm">Ajouter une cliente</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center p-3">
        <small class="text-muted">{{ $clientes->total() }} clientes</small>
        {{ $clientes->withQueryString()->links() }}
    </div>
</div>

{{-- Modal Edit Rapide --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" class="fid-input" id="editNom">
                </div>
                <div class="mb-3">
                    <label class="form-label">Prenom</label>
                    <input type="text" class="fid-input" id="editPrenom">
                </div>
                <div class="mb-3">
                    <label class="form-label">Telephone</label>
                    <input type="text" class="fid-input" id="editTel">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-fid-ghost" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn-fid-primary" id="btnSaveEdit">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Taux Multiple --}}
<div class="modal fade" id="tauxModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Taux de reduction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Appliquer un taux a la selection :</label>
                <select class="fid-input" id="bulkTauxValue">
                    @foreach([0, 10, 15, 20, 25, 30, 35, 40, 45, 50] as $t)
                        <option value="{{ $t }}">{{ $t }}%</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-fid-ghost" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn-fid-primary" id="btnApplyBulkTaux">Appliquer</button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Form (hidden) --}}
<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

// Checkbox management
$('#checkAll').on('change', function() {
    $('.fid-check-item').prop('checked', this.checked);
    toggleBulkButtons();
});
$(document).on('change', '.fid-check-item', toggleBulkButtons);

function toggleBulkButtons() {
    const count = $('.fid-check-item:checked').length;
    $('#btnBulkTaux, #btnBulkDelete').toggle(count > 0);
}

function getSelectedIds() {
    return $('.fid-check-item:checked').map(function() { return $(this).val(); }).get();
}

// Taux individuel
function updateTaux(select) {
    const id = $(select).data('id');
    const taux = $(select).val();
    $.post("{{ url('/api/clientes/maj-taux') }}", { id, taux })
        .done(res => Toast.fire({ icon: 'success', title: res.message }))
        .fail(() => Toast.fire({ icon: 'error', title: 'Erreur' }));
}

// Taux multiple
$('#btnBulkTaux').on('click', () => new bootstrap.Modal('#tauxModal').show());
$('#btnApplyBulkTaux').on('click', function() {
    const ids = getSelectedIds();
    const taux = $('#bulkTauxValue').val();
    $.post("{{ url('/api/clientes/maj-taux-multiple') }}", { ids, taux })
        .done(res => { Toast.fire({ icon: 'success', title: res.message }); setTimeout(() => location.reload(), 1000); })
        .fail(() => Toast.fire({ icon: 'error', title: 'Erreur' }));
});

// Suppression individuelle
$(document).on('click', '.btn-delete', function() {
    const id = $(this).data('id');
    const nom = $(this).data('nom');
    Swal.fire({
        title: 'Supprimer cette cliente ?',
        text: nom,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c4226e',
        confirmButtonText: 'Supprimer',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (result.isConfirmed) {
            const form = $('#deleteForm');
            form.attr('action', "{{ url('/clientes') }}/" + id);
            form.submit();
        }
    });
});

// Suppression multiple
$('#btnBulkDelete').on('click', function() {
    const ids = getSelectedIds();
    Swal.fire({
        title: `Supprimer ${ids.length} clientes ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c4226e',
        confirmButtonText: 'Supprimer',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (result.isConfirmed) {
            $.post("{{ url('/api/clientes/supprimer-multiples') }}", { ids })
                .done(res => { Toast.fire({ icon: 'success', title: res.message }); setTimeout(() => location.reload(), 1000); })
                .fail(() => Toast.fire({ icon: 'error', title: 'Erreur' }));
        }
    });
});
</script>
@endpush
