@extends('layouts.cliente')
@section('title', 'Mon profil')
@section('show-nav', true)

@section('content')
<div class="cliente-header">
    <div class="profil-avatar">{{ mb_substr($cliente->prenom, 0, 1) }}{{ mb_substr($cliente->nom, 0, 1) }}</div>
    <p class="welcome">{{ $cliente->prenom }} {{ $cliente->nom }}</p>
    <p class="subtitle">Membre depuis {{ $cliente->cliente_depuis ?? date('Y', strtotime($cliente->date_enregistrement)) }}</p>
</div>

{{-- Infos personnelles --}}
<div class="cliente-card">
    <h6><i class="fas fa-user me-1"></i>Informations personnelles</h6>
    <div class="profil-field">
        <span class="field-label">Nom</span>
        <span class="field-value">{{ $cliente->nom }}</span>
    </div>
    <div class="profil-field">
        <span class="field-label">Prenom</span>
        <span class="field-value">{{ $cliente->prenom }}</span>
    </div>
    <div class="profil-field">
        <span class="field-label">Email</span>
        <span class="field-value">
            {{ $cliente->email }}
            <button class="btn btn-sm text-primary p-0 ms-1" data-bs-toggle="modal" data-bs-target="#modalEmail"><i class="fas fa-pen"></i></button>
        </span>
    </div>
    <div class="profil-field">
        <span class="field-label">Telephone</span>
        <span class="field-value">
            {{ $cliente->telephone }}
            <button class="btn btn-sm text-primary p-0 ms-1" data-bs-toggle="modal" data-bs-target="#modalTel"><i class="fas fa-pen"></i></button>
        </span>
    </div>
    @if($cliente->date_anniversaire)
    <div class="profil-field">
        <span class="field-label">Anniversaire</span>
        <span class="field-value">{{ \Carbon\Carbon::parse($cliente->date_anniversaire)->format('d/m/Y') }}</span>
    </div>
    @endif
    <div class="profil-field">
        <span class="field-label">Institut</span>
        <span class="field-value">{{ $cliente->lieu_enregistrement }}</span>
    </div>
</div>

{{-- Fidélité --}}
<div class="cliente-card">
    <h6><i class="fas fa-award me-1" style="color:var(--color-accent-gold)"></i>Programme fidelite</h6>
    <div class="profil-field">
        <span class="field-label">Taux de reduction</span>
        <span class="field-value" style="color:var(--color-primary);font-weight:700">{{ intval($cliente->taux_reduction) }}%</span>
    </div>
    <div class="profil-field">
        <span class="field-label">N° carte</span>
        <span class="field-value" style="font-family:monospace">{{ $cliente->numero_carte }}</span>
    </div>
</div>

{{-- Déconnexion --}}
<div class="text-center mt-3 mb-4">
    <form method="POST" action="{{ route('espace-cliente.logout') }}">
        @csrf
        <button type="submit" class="btn-fid-ghost btn-sm"><i class="fas fa-sign-out-alt me-1"></i>Deconnexion</button>
    </form>
</div>

{{-- Modal Email --}}
<div class="modal fade" id="modalEmail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:var(--radius-md)">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Modifier l'email</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small">Nouvel email</label>
                    <input type="email" id="newEmail" class="fid-input" required>
                </div>
                <div id="emailOtpSection" style="display:none">
                    <div class="mb-2">
                        <label class="form-label small">Code OTP</label>
                        <input type="text" id="emailOtp" class="fid-input text-center" maxlength="6">
                    </div>
                    <button class="btn-fid-primary btn-sm w-100" id="btnConfirmEmail">Confirmer</button>
                </div>
                <button class="btn-fid-secondary btn-sm w-100" id="btnSendEmailOtp">Recevoir un code OTP</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Téléphone --}}
<div class="modal fade" id="modalTel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:var(--radius-md)">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Modifier le telephone</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small">Nouveau telephone</label>
                    <input type="tel" id="newTel" class="fid-input" required>
                </div>
                <div id="telOtpSection" style="display:none">
                    <div class="mb-2">
                        <label class="form-label small">Code OTP</label>
                        <input type="text" id="telOtp" class="fid-input text-center" maxlength="6">
                    </div>
                    <button class="btn-fid-primary btn-sm w-100" id="btnConfirmTel">Confirmer</button>
                </div>
                <button class="btn-fid-secondary btn-sm w-100" id="btnSendTelOtp">Recevoir un code OTP</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Email change
$('#btnSendEmailOtp').on('click', function() {
    $.post("{{ route('espace-cliente.resend-otp') }}", {
        email: "{{ $cliente->email }}",
        context: 'activation'
    }, function(d) {
        if (d.success) {
            $('#emailOtpSection').show();
            $(this).hide();
            Swal.fire({ icon:'success', title:'Code envoye !', timer:1000, showConfirmButton:false });
        }
    }.bind(this));
});

$('#btnConfirmEmail').on('click', function() {
    $.post("{{ route('espace-cliente.update-email') }}", {
        new_email: $('#newEmail').val(),
        otp: $('#emailOtp').val()
    }, function(d) {
        if (d.success) {
            Swal.fire({ icon:'success', title:'Email modifie !', timer:1200, showConfirmButton:false });
            setTimeout(() => location.reload(), 1300);
        } else {
            Swal.fire('Erreur', d.message, 'error');
        }
    });
});

// Phone change
$('#btnSendTelOtp').on('click', function() {
    $.post("{{ route('espace-cliente.resend-otp') }}", {
        email: "{{ $cliente->email }}",
        context: 'activation'
    }, function(d) {
        if (d.success) {
            $('#telOtpSection').show();
            $(this).hide();
            Swal.fire({ icon:'success', title:'Code envoye !', timer:1000, showConfirmButton:false });
        }
    }.bind(this));
});

$('#btnConfirmTel').on('click', function() {
    $.post("{{ route('espace-cliente.update-phone') }}", {
        new_telephone: $('#newTel').val(),
        otp: $('#telOtp').val()
    }, function(d) {
        if (d.success) {
            Swal.fire({ icon:'success', title:'Telephone modifie !', timer:1200, showConfirmButton:false });
            setTimeout(() => location.reload(), 1300);
        } else {
            Swal.fire('Erreur', d.message, 'error');
        }
    });
});
</script>
@endpush
