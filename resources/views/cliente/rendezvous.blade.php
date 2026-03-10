@extends('layouts.cliente')
@section('title', 'Mes RDV — Prestige by ProNails')
@section('show-nav', true)

@section('content')
<div class="cliente-header">
    <p class="welcome">Mes rendez-vous</p>
    <p class="subtitle">Gerez et planifiez vos visites</p>
</div>

{{-- Bouton nouveau RDV --}}
<div class="text-center mb-3">
    <button class="btn-fid-primary" data-bs-toggle="modal" data-bs-target="#modalNewRdv">
        <i class="fas fa-calendar-plus me-1"></i>Nouveau rendez-vous
    </button>
</div>

{{-- Liste RDV --}}
@forelse($rdvs as $rdv)
    <div class="cliente-card" style="padding:12px">
        <div class="rdv-item" style="margin:0;background:transparent">
            <div class="rdv-date-box">
                <div class="rdv-day">{{ \Carbon\Carbon::parse($rdv->date_rdv)->format('d') }}</div>
                <div class="rdv-month">{{ strtoupper(\Carbon\Carbon::parse($rdv->date_rdv)->translatedFormat('M')) }}</div>
            </div>
            <div class="rdv-info">
                <div class="rdv-prestation">{{ $rdv->prestation }}</div>
                <div class="rdv-meta">
                    <i class="fas fa-clock"></i> {{ $rdv->heure_rdv }}
                    &bull; <i class="fas fa-map-marker-alt"></i> {{ $rdv->lieu }}
                </div>
                @if($rdv->notes)
                    <div class="rdv-meta"><i class="fas fa-sticky-note"></i> {{ $rdv->notes }}</div>
                @endif
            </div>
            <div class="text-end">
                <span class="badge-statut badge-{{ $rdv->statut }}">{{ ucfirst(str_replace('_', ' ', $rdv->statut)) }}</span>
                @if(in_array($rdv->statut, ['en_attente', 'valide']))
                    <button class="btn btn-sm text-danger p-0 mt-1 d-block btn-cancel" data-id="{{ $rdv->id }}" title="Annuler">
                        <i class="fas fa-times-circle"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="cliente-empty">
        <i class="fas fa-calendar-xmark"></i>
        <p>Aucun rendez-vous pour le moment</p>
        <button class="btn-fid-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNewRdv">Prendre un RDV</button>
    </div>
@endforelse

{{ $rdvs->links('pagination::simple-bootstrap-5') }}

{{-- Modal nouveau RDV --}}
<div class="modal fade" id="modalNewRdv" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-md)">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-1"></i>Nouveau rendez-vous</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRdv">
                    <div class="mb-2">
                        <label class="form-label small">Prestation</label>
                        <select name="prestation" class="fid-input" required>
                            <option value="">— Choisir —</option>
                            @foreach($prestations as $p)
                                <option value="{{ $p->libelle }}">{{ $p->libelle }} ({{ number_format($p->tarif, 0, ',', ' ') }} F)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Date</label>
                            <input type="date" name="date_rdv" class="fid-input" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Heure</label>
                            <select name="heure_rdv" class="fid-input" required>
                                <option value="">—</option>
                                @for($h = 8; $h <= 19; $h++)
                                    <option value="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</option>
                                    <option value="{{ sprintf('%02d:30', $h) }}">{{ sprintf('%02d:30', $h) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Lieu</label>
                        <select name="lieu" class="fid-input" required>
                            @foreach($lieux as $l)
                                <option value="{{ $l }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Notes (optionnel)</label>
                        <textarea name="notes" class="fid-input" rows="2" placeholder="Precision particuliere..."></textarea>
                    </div>
                    <button type="submit" class="btn-fid-primary w-100">
                        <span id="rdvBtnText">Confirmer le RDV</span>
                        <span id="rdvBtnLoader" style="display:none"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Submit new RDV
$('#formRdv').on('submit', function(e) {
    e.preventDefault();
    $('#rdvBtnText').hide(); $('#rdvBtnLoader').show();

    $.post("{{ route('espace-cliente.store-rdv') }}", $(this).serialize(), function(d) {
        if (d.success) {
            Swal.fire({ icon:'success', title:'RDV confirme !', text:'Vous recevrez une confirmation.', timer:1500, showConfirmButton:false });
            setTimeout(() => location.reload(), 1600);
        } else {
            Swal.fire('Creneau indisponible', d.message, 'warning');
        }
    }).fail(function(xhr) {
        Swal.fire('Erreur', xhr.responseJSON?.message || 'Erreur de connexion.', 'error');
    }).always(function() {
        $('#rdvBtnText').show(); $('#rdvBtnLoader').hide();
    });
});

// Cancel RDV
$('.btn-cancel').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Annuler ce RDV ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c4226e',
        confirmButtonText: 'Oui, annuler',
        cancelButtonText: 'Non'
    }).then(r => {
        if (r.isConfirmed) {
            $.post("{{ url('/espace-cliente/rendezvous') }}/" + id + "/annuler", function(d) {
                if (d.success) {
                    Swal.fire({ icon:'success', title:'RDV annule', timer:1000, showConfirmButton:false });
                    setTimeout(() => location.reload(), 1100);
                } else {
                    Swal.fire('Erreur', d.message, 'error');
                }
            });
        }
    });
});
</script>
@endpush
