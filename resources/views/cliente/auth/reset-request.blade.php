@extends('layouts.cliente')
@section('title', 'Mot de passe oublie — Prestige by ProNails')

@section('full-page', true)

@section('content')
<div class="cliente-auth">
    <div class="cliente-auth-card">
        <div class="auth-logo">
            <img src="{{ url('/public/img/lgp.png') }}" alt="ProNails" style="height:40px">
        </div>
        <h5 class="auth-title">Mot de passe oublie</h5>
        <p class="text-center text-muted small mb-3">Entrez votre email pour recevoir un code de reinitialisation</p>

        <div class="mb-3">
            <input type="email" id="resetEmail" class="fid-input" placeholder="votre@email.com" required autofocus>
        </div>
        <button class="btn-fid-primary w-100 py-2" id="btnSend">
            <span id="btnText">Envoyer le code</span>
            <span id="btnLoader" style="display:none"><i class="fas fa-spinner fa-spin"></i></span>
        </button>

        <div class="text-center mt-3">
            <a href="{{ route('espace-cliente.login') }}" class="small text-muted"><i class="fas fa-arrow-left me-1"></i>Retour connexion</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnSend').on('click', function() {
    const email = $('#resetEmail').val().trim();
    if (!email) return;
    $('#btnText').hide(); $('#btnLoader').show();

    $.post("{{ route('espace-cliente.reset-send') }}", { email }, function(d) {
        if (d.success) {
            Swal.fire({ icon:'success', title:'Code envoye !', text:'Verifiez votre boite email.', timer:1500, showConfirmButton:false });
            setTimeout(() => location.href = "{{ route('espace-cliente.reset-confirm') }}?email=" + encodeURIComponent(email), 1600);
        } else {
            Swal.fire('Erreur', d.message || 'Email introuvable.', 'error');
        }
    }).fail(function() {
        Swal.fire('Erreur', 'Erreur de connexion.', 'error');
    }).always(function() {
        $('#btnText').show(); $('#btnLoader').hide();
    });
});

$('#resetEmail').on('keypress', e => { if (e.which === 13) $('#btnSend').click(); });
</script>
@endpush
