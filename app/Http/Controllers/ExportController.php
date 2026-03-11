<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Facture;
use App\Models\FacturePrestation;
use App\Models\Depense;
use App\Models\OperationBancaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportController extends Controller
{
    // ─── CLIENTES EXCEL ──────────────────────────────────────
    public function clientesExcel(Request $request)
    {
        $query = Cliente::query()->orderBy('nom');

        if ($request->lieu) {
            $query->where('lieu_enregistrement', $request->lieu);
        }
        if ($request->statut === 'actif') {
            $query->where('active', 1);
        } elseif ($request->statut === 'inactif') {
            $query->where('active', 0);
        }

        $clientes = $query->get();

        return Excel::download(new class($clientes) implements FromCollection, WithHeadings, WithMapping, WithStyles {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
            public function headings(): array {
                return ['Nom', 'Prenom', 'Telephone', 'Email', 'Lieu', 'Taux (%)', 'N° Carte', 'Statut', 'Date inscription'];
            }
            public function map($c): array {
                return [
                    $c->nom, $c->prenom, $c->telephone, $c->email ?? '',
                    $c->lieu_enregistrement, intval($c->taux_reduction),
                    $c->numero_carte, $c->active ? 'Active' : 'Inactive',
                    $c->date_enregistrement,
                ];
            }
            public function styles(Worksheet $sheet) {
                return [1 => ['font' => ['bold' => true]]];
            }
        }, 'Clientes_Pronails_' . date('Y-m-d') . '.xlsx');
    }

    // ─── CLIENTES PDF ────────────────────────────────────────
    public function clientesPdf(Request $request)
    {
        $query = Cliente::query()->orderBy('nom');

        if ($request->lieu) {
            $query->where('lieu_enregistrement', $request->lieu);
        }

        $clientes = $query->get();

        $pdf = Pdf::loadView('exports.clientes-pdf', compact('clientes'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Clientes_Pronails_' . date('Y-m-d') . '.pdf');
    }

    // ─── FACTURES EXCEL ──────────────────────────────────────
    public function facturesExcel(Request $request)
    {
        $query = Facture::query()->orderByDesc('date_facture');

        if ($request->date_start) {
            $query->where('date_facture', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->where('date_facture', '<=', $request->date_end);
        }
        if ($request->lieu) {
            $query->where('lieu_prestation', $request->lieu);
        }

        $factures = $query->get();

        return Excel::download(new class($factures) implements FromCollection, WithHeadings, WithMapping, WithStyles {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
            public function headings(): array {
                return ['#', 'Date', 'Client', 'Total', 'Remise (%)', 'Net', 'Paiement', 'Lieu'];
            }
            public function map($f): array {
                return [
                    $f->id, $f->date_facture, $f->nom_client ?? '',
                    round($f->montant_total), $f->taux_remise ?? 0,
                    round($f->montant_net ?? $f->montant_total),
                    $f->mode_paiement, $f->lieu_prestation ?? '',
                ];
            }
            public function styles(Worksheet $sheet) {
                return [1 => ['font' => ['bold' => true]]];
            }
        }, 'Factures_Pronails_' . date('Y-m-d') . '.xlsx');
    }

    // ─── FACTURES PDF ────────────────────────────────────────
    public function facturesPdf(Request $request)
    {
        $query = Facture::query()->orderByDesc('date_facture');

        if ($request->date_start) {
            $query->where('date_facture', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->where('date_facture', '<=', $request->date_end);
        }

        $factures = $query->limit(200)->get();

        $pdf = Pdf::loadView('exports.factures-pdf', compact('factures'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Factures_Pronails_' . date('Y-m-d') . '.pdf');
    }

    // ─── FACTURE UNIQUE PDF ──────────────────────────────────
    public function facturePdf(Facture $facture)
    {
        $prestations = FacturePrestation::where('facture_id', $facture->id)->get();
        $cliente = Cliente::find($facture->client_id);

        $pdf = Pdf::loadView('exports.facture-single-pdf', compact('facture', 'prestations', 'cliente'))
            ->setPaper('a4');

        return $pdf->download('Facture_' . $facture->id . '_ProNails.pdf');
    }

    // ─── DEPENSES EXCEL ──────────────────────────────────────
    public function depensesExcel(Request $request)
    {
        $query = DB::connection('mysql_latin1')
            ->table('depenses')
            ->orderByDesc('date_depense');

        if ($request->date_start) {
            $query->where('date_depense', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->where('date_depense', '<=', $request->date_end);
        }

        $depenses = $query->get();

        return Excel::download(new class($depenses) implements FromCollection, WithHeadings, WithMapping, WithStyles {
            private $data;
            public function __construct($data) { $this->data = collect($data); }
            public function collection() { return $this->data; }
            public function headings(): array {
                return ['Date', 'Libelle', 'Description', 'Quantite', 'Montant', 'Total'];
            }
            public function map($d): array {
                return [
                    $d->date_depense, $d->libelle ?? '',
                    $d->description ?? '', $d->quantite ?? 1,
                    round($d->montant), round($d->total ?? $d->montant),
                ];
            }
            public function styles(Worksheet $sheet) {
                return [1 => ['font' => ['bold' => true]]];
            }
        }, 'Depenses_Pronails_' . date('Y-m-d') . '.xlsx');
    }

    // ─── OPERATIONS BANCAIRES EXCEL ──────────────────────────
    public function operationsExcel(Request $request)
    {
        $query = OperationBancaire::query()->orderByDesc('date_operation');

        if ($request->date_start) {
            $query->where('date_operation', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->where('date_operation', '<=', $request->date_end);
        }

        $operations = $query->get();

        return Excel::download(new class($operations) implements FromCollection, WithHeadings, WithMapping, WithStyles {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
            public function headings(): array {
                return ['Date', 'Type', 'Banque', 'Operateur', 'Montant', 'Batch'];
            }
            public function map($o): array {
                return [
                    $o->date_operation,
                    $o->type_operation === 'versement_especes' ? 'Versement' : 'Cheque',
                    $o->banque ?? '', $o->nom_operateur ?? '',
                    round($o->montant_operation), $o->batch_id ?? '',
                ];
            }
            public function styles(Worksheet $sheet) {
                return [1 => ['font' => ['bold' => true]]];
            }
        }, 'Operations_Pronails_' . date('Y-m-d') . '.xlsx');
    }
}
