@extends('layouts.app')
@section('title', 'Dashboard Communications')

@section('content')
<h4 class="mb-4"><i class="fas fa-bullhorn me-2"></i>Dashboard Communications</h4>

<div class="row g-3 fid-stagger">
    <div class="col-md-4 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-users"></i></div>
            <div class="fid-stat-value" id="nbClientes">—</div>
            <div class="fid-stat-label">Clientes actives</div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="fid-stat-value" id="rdvAttente">—</div>
            <div class="fid-stat-label">RDV en attente</div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="fid-stat-value">—</div>
            <div class="fid-stat-label">Messages (30j)</div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="fid-card p-3 fid-slide-up">
            <h6 class="fw-bold mb-3">Raccourcis</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('communications.index') }}" class="btn-fid-primary"><i class="fas fa-paper-plane me-1"></i>Campagne</a>
                <a href="{{ route('clientes.index') }}" class="btn-fid-secondary"><i class="fas fa-users me-1"></i>Clientes</a>
                <a href="{{ route('rendezvous.index') }}" class="btn-fid-secondary"><i class="fas fa-calendar-alt me-1"></i>RDV</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$.get("{{ url('/api/stats/main') }}", function(d) {
    $('#nbClientes').text(d.nb_clientes);
    $('#rdvAttente').text(d.rdv_en_attente);
});
</script>
@endpush
