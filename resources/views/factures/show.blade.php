@extends('layouts.app')

@section('title', 'Facture #' . $facture->id)

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('factures.index') }}" class="btn-fid-ghost btn-sm me-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Facture #{{ $facture->id }}</h4>
</div>

<div class="row fid-stagger">
    <div class="col-lg-8 mb-4">
        {{-- Detail facture --}}
        <div class="fid-card">
            <div class="p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Client</small>
                        <strong>{{ $facture->nom_client }}</strong>
                        @if($facture->cliente)
                            <a href="{{ route('clientes.show', $facture->cliente) }}" class="ms-2 text-primary"><i class="fas fa-external-link-alt"></i></a>
                        @endif
                        @if($facture->telephone_client)
                            <div class="text-muted small">{{ $facture->telephone_client }}</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Date</small>
                        <strong>{{ $facture->date_facture ? date('d/m/Y H:i', strtotime($facture->date_facture)) : '—' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Caissiere</small>
                        <strong>{{ $facture->caissiere ? $facture->caissiere->prenom . ' ' . $facture->caissiere->nom : '—' }}</strong>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Mode de paiement</small>
                        {{ $facture->mode_paiement }}
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Lieu</small>
                        {{ $facture->lieu_prestation }}
                    </div>
                </div>

                <h6 class="fw-bold mb-3">Prestations</h6>
                <div class="fid-table-wrapper">
                    <table class="fid-table">
                        <thead>
                            <tr><th>Prestation</th><th>Tarif</th><th>Qte</th><th>Montant</th><th>Operatrice</th></tr>
                        </thead>
                        <tbody>
                            @foreach($facture->prestations as $line)
                                <tr>
                                    <td>{{ $line->libelle }}</td>
                                    <td>{{ number_format($line->tarif, 0, ',', ' ') }} F</td>
                                    <td>{{ $line->quantite }}</td>
                                    <td>{{ number_format($line->montant, 0, ',', ' ') }} F</td>
                                    <td>{{ $line->operatrice?->nom ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="fid-card">
            <div class="p-4">
                <h6 class="fw-bold mb-3">Totaux</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total HT</span>
                    <span>{{ number_format($facture->montant_total, 0, ',', ' ') }} F</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Remise ({{ $facture->taux_remise }}%)</span>
                    <span>-{{ number_format($facture->montant_remise, 0, ',', ' ') }} F</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold" style="font-size:1.2rem; color:var(--color-primary);">
                    <span>Net a payer</span>
                    <span>{{ number_format($facture->montant_net, 0, ',', ' ') }} F</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
