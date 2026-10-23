<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\Customer;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $data = $request->validate(['q' => ['required', 'string', 'max:100']]);
        $term = '%'.trim($data['q']).'%';

        return view('admin.search.results', [
            'term' => $data['q'],
            'inquiries' => Inquiry::query()->where('reference', 'like', $term)->orWhere('customer_name', 'like', $term)->limit(8)->get(),
            'customers' => Customer::query()->where('name', 'like', $term)->orWhere('email', 'like', $term)->limit(8)->get(),
            'content' => ContentEntry::query()->where('title', 'like', $term)->limit(8)->get(),
        ]);
    }
}
