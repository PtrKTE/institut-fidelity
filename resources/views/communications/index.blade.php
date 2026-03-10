@extends('layouts.app')

@section('title', 'Communications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Communications</h4>
</div>

<div class="row">
    {{-- Formulaire d'envoi --}}
    <div class="col-lg-5 mb-4">
        <div class="fid-card">
            <div class="p-4">
                <h6 class="fw-bold mb-3">Nouvelle campagne</h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Type</label>
                    <select id="commType" class="fid-input">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>

                <div class="mb-3" id="objetGroup" style="display:none;">
                    <label class="form-label">Objet (email)</label>
                    <input type="text" id="commObjet" class="fid-input" placeholder="Objet du mail">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Destinataires</label>
                    <select id="commClients" class="fid-input" multiple style="height:120px;">
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->prenom }} {{ $c->nom }} — {{ $c->telephone }}</option>
                        @endforeach
                    </select>
                    <div class="d-flex gap-2 mt-1">
                        <button type="button" class="btn-fid-ghost btn-sm" id="selectAll">Tout</button>
                        <button type="button" class="btn-fid-ghost btn-sm" id="selectNone">Aucun</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Message</label>
                    <textarea id="commMessage" class="fid-input" rows="5" placeholder="Bonjour [Prénom], ..."></textarea>
                    <small class="text-muted">Placeholders : [Prenom], [Nom], [Email]</small>
                </div>

                <button class="btn-fid-primary w-100" id="btnSend">
                    <i class="fas fa-paper-plane me-1"></i>Envoyer
                </button>
            </div>
        </div>
    </div>

    {{-- Historique --}}
    <div class="col-lg-7 mb-4">
        <div class="fid-card">
            <div class="p-4">
                <h6 class="fw-bold mb-3">Historique des envois</h6>
                <div class="fid-table-wrapper">
                    <table class="fid-table">
                        <thead>
                            <tr><th>Date</th><th>Cliente</th><th>Type</th><th>Message</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td><small>{{ $log->date_envoi }}</small></td>
                                    <td>{{ $log->cliente ? $log->cliente->prenom . ' ' . $log->cliente->nom : '—' }}</td>
                                    <td><span class="fid-badge fid-badge-info">{{ $log->type }}</span></td>
                                    <td><small>{{ Str::limit($log->message, 50) }}</small></td>
                                    <td>
                                        <span class="fid-badge {{ $log->status === 'envoye' ? 'fid-badge-success' : 'fid-badge-danger' }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-3">Aucun envoi</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

$('#commType').on('change', function() { $('#objetGroup').toggle($(this).val() === 'email'); });
$('#selectAll').on('click', () => $('#commClients option').prop('selected', true));
$('#selectNone').on('click', () => $('#commClients option').prop('selected', false));

$('#btnSend').on('click', function() {
    const clients = $('#commClients').val();
    const message = $('#commMessage').val().trim();
    const type = $('#commType').val();

    if (!clients || !clients.length) return Toast.fire({ icon: 'warning', title: 'Selectionnez des destinataires' });
    if (!message) return Toast.fire({ icon: 'warning', title: 'Ecrivez un message' });

    Swal.fire({
        title: `Envoyer a ${clients.length} cliente(s) ?`,
        text: `Type: ${type}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#c4226e',
        confirmButtonText: 'Envoyer',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (!result.isConfirmed) return;

        $('#btnSend').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Envoi...');

        $.post("{{ route('communications.send') }}", {
            type, clients, message,
            objet: $('#commObjet').val()
        }, function(res) {
            Toast.fire({ icon: 'success', title: res.message });
            setTimeout(() => location.reload(), 1500);
        }).fail(() => {
            Toast.fire({ icon: 'error', title: 'Erreur d\'envoi' });
        }).always(() => {
            $('#btnSend').prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i>Envoyer');
        });
    });
});
</script>
@endpush
