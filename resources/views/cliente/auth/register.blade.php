@extends('layouts.cliente')
@section('title', 'Inscription')

@section('full-page', true)

@section('content')
<div class="cliente-auth">
    <div class="cliente-auth-card" style="max-width:440px">
        <div class="auth-logo">
            <img src="{{ url('/public/img/lgp.png') }}" alt="ProNails" style="height:40px">
        </div>
        <h5 class="auth-title">Inscription</h5>

        <form id="formRegister">
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small">Nom</label>
                    <input type="text" name="nom" class="fid-input" required>
                </div>
                <div class="col-6">
                    <label class="form-label small">Prenom</label>
                    <input type="text" name="prenom" class="fid-input" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small">Email</label>
                <input type="email" name="email" class="fid-input" value="{{ $email }}" required>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label small">Indicatif</label>
                    <select name="indicatif" class="fid-input">
                        <option value="+225" selected>+225</option>
                        <option value="+33">+33</option>
                        <option value="+1">+1</option>
                    </select>
                </div>
                <div class="col-8">
                    <label class="form-label small">Telephone</label>
                    <input type="tel" name="telephone" class="fid-input" required placeholder="07 00 00 00 00">
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small">Institut de rattachement</label>
                <select name="lieu_enregistrement" class="fid-input" required>
                    <option value="">— Choisir —</option>
                    <option value="Pronails Vallon">Pronails Vallon</option>
                    <option value="Pronails Riviera">Pronails Riviera</option>
                    <option value="Pronails Zone 4">Pronails Zone 4</option>
                </select>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label small">Date d'anniversaire</label>
                    <input type="date" name="date_anniversaire" class="fid-input">
                </div>
                <div class="col-6">
                    <label class="form-label small">Cliente depuis</label>
                    <select name="cliente_depuis" class="fid-input">
                        @for($y = date('Y'); $y >= 2009; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-fid-primary w-100 py-2" id="btnSubmit">
                <span id="btnText">S'inscrire</span>
                <span id="btnLoader" style="display:none"><i class="fas fa-spinner fa-spin"></i></span>
            </button>
        </form>

        <hr>
        <div class="text-center">
            <a href="{{ route('espace-cliente.login') }}" class="small text-muted">Deja un compte ? Se connecter</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#formRegister').on('submit', function(e) {
    e.preventDefault();
    $('#btnText').hide(); $('#btnLoader').show();

    $.post("{{ route('espace-cliente.register') }}", $(this).serialize(), function(d) {
        if (d.success) {
            const email = $('[name=email]').val();
            Swal.fire({ icon:'success', title:'Inscription reussie !', text:'Un code OTP a ete envoye a votre email.', timer:2000, showConfirmButton:false });
            setTimeout(() => location.href = "{{ route('espace-cliente.activation') }}?email=" + encodeURIComponent(email), 2100);
        } else {
            Swal.fire('Erreur', d.message || 'Une erreur est survenue.', 'error');
        }
    }).fail(function(xhr) {
        const msg = xhr.responseJSON?.message || 'Erreur de connexion.';
        Swal.fire('Erreur', msg, 'error');
    }).always(function() {
        $('#btnText').show(); $('#btnLoader').hide();
    });
});
</script>
@endpush
