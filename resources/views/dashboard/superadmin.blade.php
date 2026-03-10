@extends('layouts.app')
@section('title', 'Dashboard Superadmin')

@section('content')
<h4 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Dashboard</h4>

{{-- Stats cards --}}
<div class="row g-3 fid-stagger">
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="fid-stat-value fid-skeleton fid-skeleton-text" id="caJour">—</div>
            <div class="fid-stat-label">CA du jour</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="fid-stat-value fid-skeleton fid-skeleton-text" id="nbFactures">—</div>
            <div class="fid-stat-label">Factures (mois)</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-users"></i></div>
            <div class="fid-stat-value fid-skeleton fid-skeleton-text" id="nbClientes">—</div>
            <div class="fid-stat-label">Clientes actives</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="fid-stat-value fid-skeleton fid-skeleton-text" id="rdvAttente">—</div>
            <div class="fid-stat-label">RDV en attente</div>
        </div>
    </div>
</div>

{{-- Row 2: CA mois + ticket moyen + operatrice --}}
<div class="row g-3 mt-1 fid-stagger">
    <div class="col-md-4">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="fid-stat-value fid-skeleton fid-skeleton-text" id="caMois">—</div>
            <div class="fid-stat-label">CA du mois</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="fid-stat-value fid-skeleton fid-skeleton-text" id="ticketMoyen">—</div>
            <div class="fid-stat-label">Ticket moyen</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-trophy"></i></div>
            <div class="fid-stat-value fid-skeleton fid-skeleton-text" id="topOp" style="font-size:1rem;">—</div>
            <div class="fid-stat-label">Meilleure operatrice</div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row g-3 mt-2">
    <div class="col-lg-8">
        <div class="fid-card p-3 fid-slide-up">
            <h6 class="fw-bold mb-3">Evolution du CA</h6>
            <canvas id="chartCaJour" height="280"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="fid-card p-3 fid-slide-up">
            <h6 class="fw-bold mb-3">CA par lieu (aujourd'hui)</h6>
            <canvas id="chartCaLieu" height="280"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
function fmt(n) { return Math.round(n).toLocaleString('fr-FR').replace(/\u202F/g, ' '); }

// Load main stats
$.get("{{ url('/api/stats/main') }}", function(d) {
    $('#caJour').removeClass('fid-skeleton fid-skeleton-text').text(fmt(d.jour.ca_total) + ' F');
    $('#nbFactures').removeClass('fid-skeleton fid-skeleton-text').text(d.periode.nb_factures);
    $('#caMois').removeClass('fid-skeleton fid-skeleton-text').text(fmt(d.periode.ca_total) + ' F');
    $('#ticketMoyen').removeClass('fid-skeleton fid-skeleton-text').text(fmt(d.periode.ticket_moyen) + ' F');
    $('#nbClientes').removeClass('fid-skeleton fid-skeleton-text').text(d.nb_clientes);
    $('#rdvAttente').removeClass('fid-skeleton fid-skeleton-text').text(d.rdv_en_attente);
    $('#topOp').removeClass('fid-skeleton fid-skeleton-text').text(d.meilleure_operatrice);
});

// CA par jour chart
$.get("{{ url('/api/stats/ca-jour') }}", function(d) {
    new Chart(document.getElementById('chartCaJour'), {
        type: 'line',
        data: {
            labels: d.labels.slice(-30),
            datasets: [{
                label: 'CA (F)',
                data: d.data.slice(-30),
                borderColor: '#c4226e',
                backgroundColor: 'rgba(196, 34, 110, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: v => fmt(v) } },
                x: { ticks: { maxTicksLimit: 10 } }
            }
        }
    });
});

// CA par lieu chart
$.get("{{ url('/api/stats/ca-lieu') }}", function(data) {
    const colors = ['#c4226e', '#0d6efd', '#cab079', '#22c55e'];
    new Chart(document.getElementById('chartCaLieu'), {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.lieu_prestation || 'Autre'),
            datasets: [{
                data: data.map(d => d.ca_jour),
                backgroundColor: colors
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>
@endpush
