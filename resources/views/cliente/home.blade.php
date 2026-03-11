@extends('layouts.cliente')
@section('title', 'Accueil')
@section('show-nav', true)

@section('content')
{{-- Header --}}
<div class="cliente-header">
    <img src="{{ asset('img/lgp.png') }}" class="logo" alt="ProNails">
    <p class="welcome">Bonjour {{ $cliente->prenom }} !</p>
    <p class="subtitle">Bienvenue dans votre espace Prestige</p>
</div>

{{-- Mini carte fidélité --}}
<div class="mini-carte">
    <div class="carte-label">Carte Fidelite</div>
    <div class="carte-nom">{{ strtoupper($cliente->nom) }} {{ mb_substr($cliente->prenom, 0, 1) }}.</div>
    <div class="carte-num">{{ $cliente->numero_carte }}</div>
    @if($cliente->taux_reduction > 0)
        <div class="carte-taux">-{{ intval($cliente->taux_reduction) }}%</div>
    @endif
</div>

{{-- Stats rapides --}}
<div class="cliente-stats" id="statsContainer">
    <div class="cliente-stat-item fid-skeleton" style="height:70px">
        <div class="stat-value" id="statVisites">—</div>
        <div class="stat-label">Visites</div>
    </div>
    <div class="cliente-stat-item fid-skeleton" style="height:70px">
        <div class="stat-value" id="statDepense">—</div>
        <div class="stat-label">Total (F)</div>
    </div>
    <div class="cliente-stat-item fid-skeleton" style="height:70px">
        <div class="stat-value" id="statMembre">—</div>
        <div class="stat-label">Membre depuis</div>
    </div>
</div>

{{-- Prochain RDV --}}
@if($prochainRdv)
<div class="cliente-card">
    <h6><i class="fas fa-calendar-check me-1 text-primary"></i>Prochain rendez-vous</h6>
    <div class="rdv-item">
        <div class="rdv-date-box">
            <div class="rdv-day">{{ \Carbon\Carbon::parse($prochainRdv->date_rdv)->format('d') }}</div>
            <div class="rdv-month">{{ strtoupper(\Carbon\Carbon::parse($prochainRdv->date_rdv)->translatedFormat('M')) }}</div>
        </div>
        <div class="rdv-info">
            <div class="rdv-prestation">{{ $prochainRdv->prestation }}</div>
            <div class="rdv-meta">
                <i class="fas fa-clock"></i> {{ $prochainRdv->heure_rdv }}
                &bull; <i class="fas fa-map-marker-alt"></i> {{ $prochainRdv->lieu }}
            </div>
        </div>
        <span class="badge-statut badge-{{ $prochainRdv->status }}">{{ ucfirst(str_replace('_', ' ', $prochainRdv->status)) }}</span>
    </div>
</div>
@endif

{{-- Top prestations --}}
<div class="cliente-card" id="topPrestationsCard" style="display:none">
    <h6><i class="fas fa-star me-1" style="color:var(--color-accent-gold)"></i>Vos prestations preferees</h6>
    <div id="topPrestations"></div>
</div>

{{-- Raccourcis --}}
<div class="cliente-card">
    <h6><i class="fas fa-bolt me-1"></i>Raccourcis</h6>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('espace-cliente.rendezvous') }}" class="btn-fid-primary btn-sm"><i class="fas fa-calendar-plus me-1"></i>Prendre RDV</a>
        <a href="{{ route('espace-cliente.carte') }}" class="btn-fid-secondary btn-sm"><i class="fas fa-id-card me-1"></i>Ma carte</a>
        <a href="{{ route('espace-cliente.historique') }}" class="btn-fid-ghost btn-sm"><i class="fas fa-receipt me-1"></i>Historique</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fmt(n) { return Math.round(n).toLocaleString('fr-FR').replace(/\u202F/g, ' '); }

// Load stats
$.get("{{ route('espace-cliente.api.stats') }}", function(d) {
    $('#statVisites').text(d.nb_visites);
    $('#statDepense').text(fmt(d.total_depense));
    $('#statMembre').text(d.membre_depuis);
    $('.fid-skeleton').removeClass('fid-skeleton');
});

// Load top prestations
$.get("{{ route('espace-cliente.api.top-prestations') }}", function(data) {
    if (data.length === 0) return;
    $('#topPrestationsCard').show();
    let html = '';
    data.forEach(p => {
        html += `<div class="d-flex justify-content-between align-items-center py-1 border-bottom">
            <span class="small">${p.libelle}</span>
            <span class="small text-muted">${p.nb}x &bull; ${fmt(p.total)} F</span>
        </div>`;
    });
    $('#topPrestations').html(html);
});
</script>
@endpush
