@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<h4 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Dashboard Admin</h4>

<div class="row g-2 fid-stagger">
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="fid-stat-body">
                <div class="fid-stat-value" id="caJour">—</div>
                <div class="fid-stat-label">CA du jour</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="fid-stat-body">
                <div class="fid-stat-value" id="nbFactures">—</div>
                <div class="fid-stat-label">Factures (mois)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-users"></i></div>
            <div class="fid-stat-body">
                <div class="fid-stat-value" id="nbClientes">—</div>
                <div class="fid-stat-label">Clientes actives</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
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
    <div class="col-lg-8">
        <div class="fid-card p-3 fid-slide-up">
            <h6 class="fw-bold mb-3">Evolution du CA</h6>
            <canvas id="chartCaJour" height="250"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="fid-card p-3 fid-slide-up">
            <h6 class="fw-bold mb-3">CA par lieu</h6>
            <canvas id="chartCaLieu" height="250"></canvas>
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
    $('#nbFactures').text(d.periode.nb_factures);
    $('#nbClientes').text(d.nb_clientes);
    $('#rdvAttente').text(d.rdv_en_attente);
});

$.get("{{ url('/api/stats/ca-jour') }}", function(d) {
    new Chart(document.getElementById('chartCaJour'), {
        type: 'line',
        data: { labels: d.labels.slice(-30), datasets: [{ label: 'CA', data: d.data.slice(-30), borderColor: '#c4226e', backgroundColor: 'rgba(196,34,110,.1)', fill: true, tension: .3 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => fmt(v) } }, x: { ticks: { maxTicksLimit: 10 } } } }
    });
});

$.get("{{ url('/api/stats/ca-lieu') }}", function(data) {
    new Chart(document.getElementById('chartCaLieu'), {
        type: 'doughnut',
        data: { labels: data.map(d => d.lieu_prestation || 'Autre'), datasets: [{ data: data.map(d => d.ca_jour), backgroundColor: ['#c4226e','#0d6efd','#cab079','#22c55e'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
@endpush
