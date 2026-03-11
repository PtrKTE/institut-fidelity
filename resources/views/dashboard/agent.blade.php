@extends('layouts.app')
@section('title', 'Dashboard Agent')

@section('content')
<h4 class="mb-3"><i class="fas fa-chart-line me-2"></i>Dashboard — {{ auth()->user()->lieu_affecte ?? 'Agent' }}</h4>

<div class="row g-2 fid-stagger">
    <div class="col-md-4 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="fid-stat-body">
                <div class="fid-stat-value" id="caJour">—</div>
                <div class="fid-stat-label">CA du jour</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="fid-stat-body">
                <div class="fid-stat-value" id="nbFacturesJour">—</div>
                <div class="fid-stat-label">Factures du jour</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="fid-stat-body">
                <div class="fid-stat-value" id="rdvAttente">—</div>
                <div class="fid-stat-label">RDV en attente</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-md-6">
        <div class="fid-card p-3 fid-slide-up">
            <h6 class="fw-bold mb-3">Raccourcis</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('caisse') }}" class="btn-fid-primary"><i class="fas fa-cash-register me-1"></i>Caisse</a>
                <a href="{{ route('clientes.create') }}" class="btn-fid-secondary"><i class="fas fa-user-plus me-1"></i>Nouvelle cliente</a>
                <a href="{{ route('rendezvous.create') }}" class="btn-fid-secondary"><i class="fas fa-calendar-plus me-1"></i>Nouveau RDV</a>
                <a href="{{ route('depenses.index') }}" class="btn-fid-ghost"><i class="fas fa-receipt me-1"></i>Depenses</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="fid-card p-3 fid-slide-up">
            <h6 class="fw-bold mb-3">CA des 7 derniers jours</h6>
            <canvas id="chartCa7j" height="180"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
function fmt(n) { return Math.round(n).toLocaleString('fr-FR').replace(/\u202F/g, ' '); }

$.get("{{ url('/api/stats/main') }}", function(d) {
    $('#caJour').text(fmt(d.jour.ca_total) + ' F');
    $('#nbFacturesJour').text(d.jour.nb_factures);
    $('#rdvAttente').text(d.rdv_en_attente);
});

$.get("{{ url('/api/stats/ca-jour') }}", function(d) {
    new Chart(document.getElementById('chartCa7j'), {
        type: 'bar',
        data: { labels: d.labels.slice(-7), datasets: [{ label: 'CA', data: d.data.slice(-7), backgroundColor: '#c4226e' }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => fmt(v) } } } }
    });
});
</script>
@endpush
