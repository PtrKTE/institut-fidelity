@extends('layouts.cliente')
@section('title', 'Reinitialiser — Prestige by ProNails')

@section('content')
<div class="cliente-auth" style="min-height:100vh;padding-bottom:0;">
    <div class="cliente-auth-card">
        <div class="auth-logo">
            <img src="{{ url('/public/img/lgp.png') }}" alt="ProNails" style="height:40px">
        </div>
        <h5 class="auth-title">Nouveau mot de passe</h5>
        <p class="text-center text-muted small mb-3">Code envoye a <strong>{{ $email }}</strong></p>

        @if($errors->any())
            <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('espace-cliente.reset-confirm') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <div class="mb-3">
                <label class="form-label small">Code OTP</label>
                <input type="text" name="otp" class="fid-input text-center" maxlength="6" pattern="[0-9]{6}" style="font-size:1.5rem;letter-spacing:8px" required autofocus placeholder="000000">
            </div>
            <div class="mb-2">
                <label class="form-label small">Nouveau mot de passe</label>
                <input type="password" name="password" class="fid-input" minlength="6" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Confirmer</label>
                <input type="password" name="password_confirmation" class="fid-input" minlength="6" required>
            </div>
            <button type="submit" class="btn-fid-primary w-100 py-2">Reinitialiser</button>
        </form>

        <div class="text-center mt-3">
            <button class="btn btn-link btn-sm text-muted" id="btnResend" disabled>
                Renvoyer (<span id="countdown">120</span>s)
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let timer = 120;
const iv = setInterval(() => {
    timer--;
    $('#countdown').text(timer);
    if (timer <= 0) {
        clearInterval(iv);
        $('#btnResend').prop('disabled', false).html('Renvoyer le code');
    }
}, 1000);

$('#btnResend').on('click', function() {
    $(this).prop('disabled', true);
    $.post("{{ route('espace-cliente.resend-otp') }}", {
        email: "{{ $email }}",
        context: 'reset'
    }, function(d) {
        if (d.success) {
            Swal.fire({ icon:'success', title:'Code envoye !', timer:1200, showConfirmButton:false });
            timer = 120;
        }
    });
});
</script>
@endpush
