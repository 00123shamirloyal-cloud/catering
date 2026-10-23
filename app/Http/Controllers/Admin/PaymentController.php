<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordFinancialAdjustment;
use App\Actions\RecordOfflinePayment;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinancialAdjustmentRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Invoice $invoice, RecordOfflinePayment $recordPayment): RedirectResponse
    {
        $payment = $recordPayment->handle($invoice, $request->validated(), $request->user()->id);

        return to_route('admin.invoices.show', $invoice)->with('success', 'Verified offline payment recorded as '.$payment->currency.' '.$payment->amount.'.');
    }

    public function storeAdjustment(
        StoreFinancialAdjustmentRequest $request,
        Invoice $invoice,
        RecordFinancialAdjustment $recordAdjustment,
    ): RedirectResponse {
        $adjustment = $recordAdjustment->handle($invoice, $request->validated(), $request->user()->id);

        return to_route('admin.invoices.show', $invoice)->with('success', Str::headline(str_replace('_', ' ', $adjustment->type)).' recorded.');
    }
}
