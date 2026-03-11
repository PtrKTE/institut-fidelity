@extends('layouts.cliente')
@section('title', 'Mon historique')
@section('show-nav', true)

@section('content')
<div class="cliente-header">
    <p class="welcome">Mon historique</p>
    <p class="subtitle">Vos prestations et depenses</p>
</div>

{{-- Graphique dépenses mensuelles --}}
<div class="cliente-card">
    <h6><i class="fas fa-chart-area me-1"></i>Depenses mensuelles</h6>
    <canvas id="chartDepenses" height="180"></canvas>
</div>

{{-- Liste factures --}}
<div class="cliente-card">
    <h6><i class="fas fa-receipt me-1"></i>Dernieres prestations</h6>

    @forelse($factures as $f)
        @php
            $prestations = \App\Models\FacturePrestation::where('facture_id', $f->id)
                ->pluck('libelle')->implode(', ');
        @endphp
        <div class="facture-item">
            <div>
                <div class="facture-prestations">{{ $prestations ?: 'Prestation' }}</div>
                <div class="facture-date">
                    {{ \Carbon\Carbon::parse($f->date_facture)->format('d/m/Y') }}
                    @if($f->mode_paiement)
                        &bull; {{ $f->mode_paiement }}
                    @endif
                </div>
            </div>
            <div class="facture-montant">{{ number_format($f->montant_total, 0, ',', ' ') }} F</div>
        </div>
    @empty
        <div class="cliente-empty" style="padding:20px 0">
            <i class="fas fa-receipt" style="font-size:2rem"></i>
            <p class="mb-0">Aucune prestation pour le moment</p>
        </div>
    @endforelse

    {{ $factures->links('pagination::simple-bootstrap-5') }}
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
function fmt(n) { return Math.round(n).toLocaleString('fr-FR').replace(/\u202F/g, ' '); }

$.get("{{ route('espace-cliente.api.depenses-mensuelles') }}", function(d) {
    if (!d.labels.length) return;

    const labels = d.labels.map(m => {
        const [y, mo] = m.split('-');
        const months = ['Jan','Fev','Mar','Avr','Mai','Jun','Jul','Aou','Sep','Oct','Nov','Dec'];
        return months[parseInt(mo)-1] + ' ' + y.slice(-2);
    });

    new Chart(document.getElementById('chartDepenses'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Depenses (F)',
                data: d.data,
                borderColor: '#c4226e',
                backgroundColor: 'rgba(196, 34, 110, 0.08)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: '#c4226e'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: v => fmt(v) }, beginAtZero: true },
                x: { ticks: { font: { size: 10 } } }
            }
        }
    });
});
</script>
@endpush
