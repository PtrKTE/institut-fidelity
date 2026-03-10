@extends('layouts.cliente')
@section('title', 'Bienvenue — Prestige by ProNails')

@section('full-page', true)

@section('content')
<div class="cliente-auth">
    <div class="cliente-auth-card">
        <div class="auth-logo">
            <img src="{{ url('/public/img/lgp.png') }}" alt="ProNails" style="height:48px">
        </div>
        <h5 class="auth-title">Bienvenue chez ProNails</h5>
        <p class="text-center text-muted small mb-4">Entrez votre email pour commencer</p>

        <div class="mb-3">
            <input type="email" id="emailInput" class="fid-input" placeholder="votre@email.com" required autofocus>
        </div>
        <button class="btn-fid-primary w-100 py-2" id="btnContinue">
            <span id="btnText">Continuer</span>
            <span id="btnLoader" style="display:none"><i class="fas fa-spinner fa-spin"></i></span>
        </button>

        <hr>
        <div class="text-center">
            <a href="{{ route('espace-cliente.login') }}" class="small text-muted">Deja un compte ? Se connecter</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnContinue').on('click', function() {
    const email = $('#emailInput').val().trim();
    if (!email) return;

    $('#btnText').hide(); $('#btnLoader').show();

    $.post("{{ route('espace-cliente.verif-email') }}", { email }, function(d) {
        if (d.status === 'active') {
            Swal.fire({ icon:'info', title:'Compte existant', text:'Vous avez deja un compte actif.', timer:1500, showConfirmButton:false });
            setTimeout(() => location.href = "{{ route('espace-cliente.login') }}", 1600);
        } else if (d.status === 'inactive') {
            Swal.fire({ icon:'info', title:'Activation requise', text:'Un code OTP a ete envoye a votre email.', timer:1500, showConfirmButton:false });
            setTimeout(() => location.href = "{{ route('espace-cliente.activation') }}?email=" + encodeURIComponent(email), 1600);
        } else {
            location.href = "{{ route('espace-cliente.register') }}?email=" + encodeURIComponent(email);
        }
    }).fail(function() {
        Swal.fire('Erreur', 'Verifiez votre connexion.', 'error');
    }).always(function() {
        $('#btnText').show(); $('#btnLoader').hide();
    });
});

$('#emailInput').on('keypress', e => { if (e.which === 13) $('#btnContinue').click(); });
</script>
@endpush
