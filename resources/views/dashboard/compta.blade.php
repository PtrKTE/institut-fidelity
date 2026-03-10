@extends('layouts.app')
@section('title', 'Dashboard Comptabilite')

@section('content')
<h4 class="mb-4"><i class="fas fa-calculator me-2"></i>Dashboard Comptabilite</h4>

<div class="fid-card mb-3">
    <div class="p-3 d-flex gap-2 align-items-end">
        <div>
            <label class="form-label small">Date</label>
            <input type="date" id="comptaDate" class="fid-input" value="{{ date('Y-m-d') }}">
        </div>
        <button class="btn-fid-primary btn-sm" id="btnLoadCompta"><i class="fas fa-search"></i> Charger</button>
    </div>
</div>

<div class="row g-3 mb-3 fid-stagger">
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="fid-stat-value" id="caTotal">—</div>
            <div class="fid-stat-label">CA Total</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="fid-stat-value" id="nbFactures">—</div>
            <div class="fid-stat-label">Factures</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="fid-stat-value" id="ticketMoyen">—</div>
            <div class="fid-stat-label">Ticket moyen</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-vault"></i></div>
            <div class="fid-stat-value" id="coffre">—</div>
            <div class="fid-stat-label">Coffre</div>
        </div>
    </div>
</div>

<div class="row g-3" id="lieuxCards"></div>
@endsection

@push('scripts')
<script>
function fmt(n) { return Math.round(n).toLocaleString('fr-FR').replace(/\u202F/g, ' '); }

function loadCompta() {
    const date = $('#comptaDate').val();
    $.get("{{ url('/api/stats/compta') }}", { date }, function(d) {
        const t = d.totaux;
        $('#caTotal').text(fmt(t.ca_total) + ' F');
        $('#nbFactures').text(t.nb_factures);
        $('#ticketMoyen').text(fmt(t.ticket_moyen) + ' F');
        $('#coffre').text(fmt(t.coffre) + ' F');

        let html = '';
        const lieux = { vallon: 'Pronails Vallon', riviera: 'Pronails Riviera', zone_4: 'Pronails Zone 4' };
        for (const [key, label] of Object.entries(lieux)) {
            const l = d[key];
            if (!l) continue;
            let modesHtml = '';
            if (l.modes_paiement) {
                for (const [mode, total] of Object.entries(l.modes_paiement)) {
                    modesHtml += `<div class="d-flex justify-content-between"><small>${mode}</small><small>${fmt(total)} F</small></div>`;
                }
            }
            html += `
            <div class="col-md-4 mb-3">
                <div class="fid-card p-3">
                    <h6 class="fw-bold">${label}</h6>
                    <div class="d-flex justify-content-between mb-1"><span>CA Jour</span><strong>${fmt(l.ca_jour)} F</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>CA Mois</span><strong>${fmt(l.ca_mois)} F</strong></div>
                    <hr class="my-2"><small class="text-muted">Modes de paiement :</small>
                    ${modesHtml || '<small class="text-muted">Aucun</small>'}
                </div>
            </div>`;
        }
        $('#lieuxCards').html(html);
    });
}

$('#btnLoadCompta').on('click', loadCompta);
loadCompta();
</script>
@endpush
