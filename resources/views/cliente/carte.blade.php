@extends('layouts.cliente')
@section('title', 'Ma carte — Prestige by ProNails')
@section('show-nav', true)

@section('content')
<div class="cliente-header">
    <p class="welcome">Ma carte de fidelite</p>
    <p class="subtitle">Presentez-la lors de vos visites</p>
</div>

{{-- Carte fidélité --}}
<div id="carteCapture">
    <div class="carte-fidelite-full">
        <div class="carte-top">
            <div>
                <div class="carte-brand">Prestige by ProNails</div>
                <div class="carte-gold">CARTE FIDELITE</div>
            </div>
            @if($cliente->taux_reduction > 0)
                <div style="background:var(--color-accent-gold);color:#333;padding:3px 10px;border-radius:12px;font-size:0.75rem;font-weight:700">
                    -{{ intval($cliente->taux_reduction) }}%
                </div>
            @endif
        </div>

        <div class="carte-holder">{{ strtoupper($cliente->nom) }} {{ mb_substr($cliente->prenom, 0, 1) }}.</div>
        <div class="carte-number">{{ $cliente->numero_carte }}</div>

        <div class="carte-bottom">
            <div>
                <div class="carte-expiry-label">Valable jusqu'au</div>
                <div class="carte-expiry">
                    @php
                        $exp = $cliente->date_enregistrement
                            ? \Carbon\Carbon::parse($cliente->date_enregistrement)->addYears(2)->format('m / y')
                            : '--/--';
                    @endphp
                    {{ $exp }}
                </div>
            </div>
            <img src="{{ url('/public/img/lgpf.png') }}" style="height:24px;opacity:0.7" alt="">
        </div>
    </div>
</div>

{{-- Barcode --}}
<div class="carte-barcode">
    <img src="{{ url('/api/barcode/' . ($cliente->numero_carte ?? 'UNKNOWN')) }}" alt="barcode" id="barcodeImg"
         onerror="this.style.display='none'">
    <div class="small text-muted mt-1">{{ $cliente->numero_carte }}</div>
</div>

{{-- Download button --}}
<div class="text-center mt-3 mb-3">
    <button class="btn-fid-primary btn-sm" id="btnDownload">
        <i class="fas fa-download me-1"></i>Telecharger ma carte
    </button>
</div>

{{-- Info carte --}}
<div class="cliente-card">
    <h6>Informations</h6>
    <div class="profil-field">
        <span class="field-label">Titulaire</span>
        <span class="field-value">{{ $cliente->nom }} {{ $cliente->prenom }}</span>
    </div>
    <div class="profil-field">
        <span class="field-label">N° carte</span>
        <span class="field-value" style="font-family:monospace">{{ $cliente->numero_carte }}</span>
    </div>
    <div class="profil-field">
        <span class="field-label">Taux de reduction</span>
        <span class="field-value">{{ intval($cliente->taux_reduction) }}%</span>
    </div>
    <div class="profil-field">
        <span class="field-label">Institut</span>
        <span class="field-value">{{ $cliente->lieu_enregistrement }}</span>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
$('#btnDownload').on('click', function() {
    html2canvas(document.getElementById('carteCapture'), {
        scale: 2,
        backgroundColor: null,
        useCORS: true
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'Carte_Fidelite_ProNails.png';
        link.href = canvas.toDataURL();
        link.click();
    });
});
</script>
@endpush
