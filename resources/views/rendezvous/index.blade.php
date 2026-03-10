@extends('layouts.app')

@section('title', 'Rendez-vous')

@push('styles')
<style>
    .badge-en_attente { background: #f59e0b; }
    .badge-valide { background: #22c55e; }
    .badge-rejete { background: #ef4444; }
    .badge-annule { background: #6b7280; }
    .badge-termine { background: #3b82f6; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Rendez-vous</h4>
    <a href="{{ route('rendezvous.create') }}" class="btn-fid-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Nouveau RDV
    </a>
</div>

{{-- Filtres --}}
<div class="fid-card mb-3">
    <form method="GET" class="p-3 d-flex gap-2 flex-wrap align-items-end">
        <div>
            <label class="form-label small">Date</label>
            <input type="date" name="date" value="{{ request('date') }}" class="fid-input">
        </div>
        <div>
            <label class="form-label small">Statut</label>
            <select name="status" class="fid-input">
                <option value="">Tous</option>
                @foreach(['en_attente', 'valide', 'rejete', 'annule', 'termine'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label small">Lieu</label>
            <select name="lieu" class="fid-input">
                <option value="">Tous</option>
                @foreach(['Pronails Vallon', 'Pronails Riviera', 'Pronails Zone 4', 'Domicile'] as $l)
                    <option value="{{ $l }}" {{ request('lieu') == $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-fid-primary btn-sm"><i class="fas fa-search"></i></button>
        <a href="{{ route('rendezvous.index') }}" class="btn-fid-ghost btn-sm"><i class="fas fa-times"></i></a>
    </form>
</div>

{{-- Tableau --}}
<div class="fid-card">
    <div class="fid-table-wrapper">
        <table class="fid-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Client</th>
                    <th>Telephone</th>
                    <th>Lieu</th>
                    <th>Prestation</th>
                    <th>Statut</th>
                    <th>Agent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rendezvous as $rdv)
                    <tr>
                        <td>{{ $rdv->date_rdv }}</td>
                        <td>{{ $rdv->heure_rdv }}</td>
                        <td><strong>{{ $rdv->nom_client }}</strong></td>
                        <td>{{ $rdv->telephone }}</td>
                        <td>{{ $rdv->lieu }}</td>
                        <td><small>{{ Str::limit($rdv->prestation, 40) }}</small></td>
                        <td>
                            <span class="fid-badge badge-{{ $rdv->status }}">{{ ucfirst(str_replace('_', ' ', $rdv->status)) }}</span>
                        </td>
                        <td>{{ $rdv->agent ? $rdv->agent->prenom : '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                @if(in_array(auth()->user()->role, ['superadmin', 'admin']) && $rdv->status === 'en_attente')
                                    <button class="btn btn-sm btn-outline-success btn-status" data-id="{{ $rdv->id }}" data-status="valide" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-status" data-id="{{ $rdv->id }}" data-status="rejete" title="Rejeter">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                @if($rdv->status !== 'annule')
                                    <button class="btn btn-sm btn-outline-secondary btn-status" data-id="{{ $rdv->id }}" data-status="annule" title="Annuler">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="fid-empty">
                                <i class="fas fa-calendar-times fa-3x mb-3" style="color:var(--color-text-muted);"></i>
                                <p>Aucun rendez-vous</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $rendezvous->withQueryString()->links() }}</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

$(document).on('click', '.btn-status', function() {
    const id = $(this).data('id');
    const status = $(this).data('status');
    const labels = { valide: 'Valider', rejete: 'Rejeter', annule: 'Annuler' };

    Swal.fire({
        title: `${labels[status]} ce rendez-vous ?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#c4226e',
        confirmButtonText: labels[status],
        cancelButtonText: 'Non'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('/rendezvous') }}/" + id,
                method: 'PUT',
                data: { status },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: () => { Toast.fire({ icon: 'success', title: 'Statut mis a jour' }); setTimeout(() => location.reload(), 1000); },
                error: () => Toast.fire({ icon: 'error', title: 'Erreur' })
            });
        }
    });
});
</script>
@endpush
