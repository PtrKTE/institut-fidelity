@extends('layouts.app')
@section('title', 'Dashboard Superadmin')

@section('content')
<h2 class="mb-4">Dashboard Superadmin</h2>
<div class="row g-3">
    <div class="col-md-3">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-users"></i></div>
            <div class="fid-stat-value" id="totalClientes">--</div>
            <div class="fid-stat-label">Clientes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="fid-stat-value" id="totalFactures">--</div>
            <div class="fid-stat-label">Factures</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="fid-stat-value" id="caTotal">--</div>
            <div class="fid-stat-label">CA Total (XOF)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fid-card fid-stat">
            <div class="fid-stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="fid-stat-value" id="totalRdv">--</div>
            <div class="fid-stat-label">Rendez-vous</div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="fid-card p-3">
            <h5>Chiffre d'affaires par lieu</h5>
            <canvas id="chartCaLieu" height="250"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="fid-card p-3">
            <h5>Top prestations</h5>
            <canvas id="chartTopPresta" height="250"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// TODO: Charger les stats via API /api/v1/stats/*
</script>
@endpush
