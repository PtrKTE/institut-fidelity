<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CommLog;
use App\Services\AuditService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CommunicationController extends Controller
{
    public function __construct(
        protected AuditService $audit,
        protected TwilioService $twilio
    ) {}

    public function index()
    {
        $logs = CommLog::with('cliente')
            ->orderByDesc('date_envoi')
            ->paginate(30);

        $clientes = Cliente::where('active', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'email', 'telephone']);

        return view('communications.index', compact('logs', 'clientes'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:whatsapp,email,sms',
            'clients' => 'required|array|min:1',
            'clients.*' => 'integer|exists:clientes,id',
            'message' => 'required|string|max:2000',
            'objet' => 'nullable|string|max:255',
        ]);

        $sent = 0;
        $errors = [];

        $clientes = Cliente::whereIn('id', $validated['clients'])->get();

        foreach ($clientes as $cliente) {
            // Remplacement des placeholders
            $msg = str_replace(
                ['[Prénom]', '[Nom]', '[Email]'],
                [$cliente->prenom, $cliente->nom, $cliente->email ?? ''],
                $validated['message']
            );

            $success = false;

            try {
                switch ($validated['type']) {
                    case 'email':
                        if (!$cliente->email) {
                            $errors[] = "{$cliente->prenom} {$cliente->nom}: pas d'email";
                            continue 2;
                        }
                        $objet = $validated['objet'] ?? 'Message de Prestige by ProNails';
                        Mail::raw($msg, function ($m) use ($cliente, $objet) {
                            $m->to($cliente->email)->subject($objet);
                        });
                        $success = true;
                        break;

                    case 'sms':
                        $phone = preg_replace('/\D/', '', $cliente->telephone ?? '');
                        if (!$phone) {
                            $errors[] = "{$cliente->prenom} {$cliente->nom}: pas de telephone";
                            continue 2;
                        }
                        $success = $this->twilio->sendSms('+225' . $phone, $msg);
                        break;

                    case 'whatsapp':
                        $phone = preg_replace('/\D/', '', $cliente->telephone ?? '');
                        if (!$phone) {
                            $errors[] = "{$cliente->prenom} {$cliente->nom}: pas de telephone";
                            continue 2;
                        }
                        $success = $this->twilio->sendWhatsApp('+225' . $phone, $msg);
                        break;
                }
            } catch (\Exception $e) {
                $errors[] = "{$cliente->prenom} {$cliente->nom}: " . $e->getMessage();
                $success = false;
            }

            CommLog::create([
                'cliente_id' => $cliente->id,
                'type' => $validated['type'],
                'message' => $msg,
                'date_envoi' => now(),
                'status' => $success ? 'envoye' : 'echec',
            ]);

            if ($success) $sent++;
        }

        $this->audit->log(
            userId: Auth::id(),
            action: 'SEND_COMM',
            entityType: 'comm_log',
            entityId: 0,
            details: "Campagne {$validated['type']}: {$sent} envoye(s) sur " . count($clientes),
            request: $request
        );

        return response()->json([
            'status' => 'success',
            'sent' => $sent,
            'errors' => $errors,
            'message' => "{$sent} message(s) envoye(s) sur " . count($clientes),
        ]);
    }
}
