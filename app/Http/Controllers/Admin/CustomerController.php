<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->withCount('inquiries')
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->trim()->toString().'%';
                $query->where(function ($nested) use ($term): void {
                    $nested->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer): View
    {
        $customer->load(['inquiries' => fn ($query) => $query->latest(), 'tasks' => fn ($query) => $query->latest()]);

        return view('admin.customers.show', compact('customer'));
    }
}
