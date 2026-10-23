<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateInvoiceFromQuotation;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(): View
    {
        $invoices = Invoice::query()->with(['customer', 'event'])->latest('issued_at')->paginate(15);

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'event', 'quotation', 'items', 'payments.recorder', 'adjustments']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'event', 'quotation', 'items', 'payments']);

        return Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4')
            ->download($invoice->number.'.pdf');
    }

    public function createFromQuotation(
        StoreInvoiceRequest $request,
        Quotation $quotation,
        CreateInvoiceFromQuotation $createInvoice,
    ): RedirectResponse {
        $invoice = $createInvoice->handle($quotation, $request->validated(), $request->user()->id);

        return to_route('admin.invoices.show', $invoice)->with('success', 'Invoice created from the accepted quotation.');
    }
}
