@extends('layouts.app')

@section('title', $cliente->prenom . ' ' . $cliente->nom)

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('clientes.index') }}" class="btn-fid-ghost btn-sm me-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">
        <i class="fas fa-user me-2"></i>{{ $cliente->prenom }} {{ $cliente->nom }}
    </h4>
    <div class="ms-auto d-flex gap-2">
        @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn-fid-secondary btn-sm">
                <i class="fas fa-edit me-1"></i>Modifier
            </a>
        @endif
    </div>
</div>

<div class="row fid-stagger">
    {{-- Infos cliente --}}
    <div class="col-lg-4 mb-4">
        <div class="fid-card h-100">
            <div class="p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-id-card me-2"></i>Informations</h6>

                <div class="mb-2">
                    <small class="text-muted d-block">Nom complet</small>
                    <strong>{{ $cliente->prenom }} {{ $cliente->nom }}</strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Telephone</small>
                    {{ $cliente->telephone ?: '—' }}
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Email</small>
                    {{ $cliente->email ?: '—' }}
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Date d'anniversaire</small>
                    {{ $cliente->date_anniversaire ? $cliente->date_anniversaire->format('d/m/Y') : '—' }}
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Lieu d'enregistrement</small>
                    {{ $cliente->lieu_enregistrement }}
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Membre depuis</small>
                    {{ $cliente->cliente_depuis ?? '—' }}
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Enregistree par</small>
                    {{ $cliente->enregistrePar ? $cliente->enregistrePar->prenom . ' ' . $cliente->enregistrePar->nom : '—' }}
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Statut</span>
                    <span class="fid-badge {{ $cliente->active ? 'fid-badge-success' : 'fid-badge-danger' }}">
                        {{ $cliente->active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Taux de reduction</span>
                    <span class="fid-badge fid-badge-info">{{ $cliente->taux_reduction }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Carte de fidelite --}}
    <div class="col-lg-4 mb-4">
        <div class="fid-card h-100">
            <div class="p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-credit-card me-2"></i>Carte de fidelite</h6>

                <div class="text-center p-3 rounded mb-3" style="background: var(--gradient-brand);">
                    <div class="text-white fw-bold mb-1" style="font-size:1.1rem;">Prestige by ProNails</div>
                    <div class="text-white" style="font-family:monospace; font-size:1.3rem; letter-spacing:2px;">
                        {{ $cliente->numero_carte }}
                    </div>
                    <div class="text-white mt-2" style="font-size:0.85rem;">
                        {{ $cliente->prenom }} {{ $cliente->nom }}
                    </div>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Numero de carte</small>
                    <code>{{ $cliente->numero_carte }}</code>
                </div>
                <div>
                    <small class="text-muted d-block">Code-barres</small>
                    <code>{{ $cliente->code_barres }}</code>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="col-lg-4 mb-4">
        <div class="fid-card h-100">
            <div class="p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar me-2"></i>Statistiques</h6>

                <div class="fid-stat mb-3">
                    <div class="fid-stat-label">Total factures</div>
                    <div class="fid-stat-value">{{ number_format($stats['total_factures']) }}</div>
                </div>
                <div class="fid-stat mb-3">
                    <div class="fid-stat-label">Total depense</div>
                    <div class="fid-stat-value">{{ number_format($stats['total_depense'], 0, ',', ' ') }} F</div>
                </div>
                <div class="fid-stat mb-3">
                    <div class="fid-stat-label">Moyenne par facture</div>
                    <div class="fid-stat-value">{{ number_format($stats['moyenne_facture'], 0, ',', ' ') }} F</div>
                </div>
                <div class="fid-stat">
                    <div class="fid-stat-label">Derniere visite</div>
                    <div class="fid-stat-value">{{ $stats['derniere_visite'] ?? 'Aucune' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top prestations --}}
@if($topPrestations->count())
<div class="fid-card mb-4 fid-slide-up">
    <div class="p-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-star me-2"></i>Prestations favorites</h6>
        <div class="fid-table-wrapper">
            <table class="fid-table">
                <thead>
                    <tr><th>Prestation</th><th>Nombre</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @foreach($topPrestations as $p)
                        <tr>
                            <td>{{ $p->libelle }}</td>
                            <td>{{ $p->nb }}</td>
                            <td>{{ number_format($p->total, 0, ',', ' ') }} F</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Derniers RDV --}}
@if($rendezvous->count())
<div class="fid-card fid-slide-up">
    <div class="p-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-calendar-alt me-2"></i>Derniers rendez-vous</h6>
        <div class="fid-table-wrapper">
            <table class="fid-table">
                <thead>
                    <tr><th>Date</th><th>Heure</th><th>Lieu</th><th>Prestation</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    @foreach($rendezvous as $rdv)
                        <tr>
                            <td>{{ $rdv->date_rdv }}</td>
                            <td>{{ $rdv->heure_rdv }}</td>
                            <td>{{ $rdv->lieu }}</td>
                            <td>{{ $rdv->prestation }}</td>
                            <td>
                                @php
                                    $badgeClass = match($rdv->status) {
                                        'valide' => 'fid-badge-success',
                                        'rejete' => 'fid-badge-danger',
                                        'annule' => 'fid-badge-neutral',
                                        default => 'fid-badge-warning',
                                    };
                                @endphp
                                <span class="fid-badge {{ $badgeClass }}">{{ ucfirst($rdv->status) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
