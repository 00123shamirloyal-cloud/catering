<?php

namespace App\Http\Controllers;

use App\Actions\CreateInquiry;
use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Requests\StoreInquiryRequest;
use App\Models\ContactMessage;
use App\Models\ContentEntry;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'services' => ContentEntry::published()->where('type', 'service')->where('is_featured', true)->orderBy('sort_order')->limit(3)->get(),
            'packages' => ContentEntry::published()->where('type', 'package')->where('is_featured', true)->orderBy('sort_order')->limit(3)->get(),
            'portfolio' => ContentEntry::published()->where('type', 'portfolio')->where('is_featured', true)->orderBy('sort_order')->limit(3)->get(),
            'testimonials' => ContentEntry::published()->where('type', 'testimonial')->orderBy('sort_order')->limit(3)->get(),
            'faqs' => ContentEntry::published()->where('type', 'faq')->orderBy('sort_order')->limit(5)->get(),
        ]);
    }

    public function services(): View
    {
        return view('public.listing', [
            'title' => 'Services',
            'intro' => 'Catering and event-planning enquiries for weddings, family occasions and business gatherings. Share your date and venue so the team can confirm what is possible in Rawalpindi.',
            'items' => ContentEntry::published()->where('type', 'service')->orderBy('sort_order')->orderBy('title')->paginate(9),
            'kind' => 'service',
        ]);
    }

    public function service(string $slug): View
    {
        return $this->showEntry('service', $slug, 'public.detail');
    }

    public function packages(): View
    {
        return view('public.listing', [
            'title' => 'Packages & menus',
            'intro' => 'Use the sample packages as a starting point. Menus, availability and prices are discussed and confirmed for each event.',
            'items' => ContentEntry::published()->where('type', 'package')->orderBy('sort_order')->orderBy('title')->paginate(9),
            'kind' => 'package',
        ]);
    }

    public function package(string $slug): View
    {
        return $this->showEntry('package', $slug, 'public.detail');
    }

    public function menus(): View
    {
        return view('public.listing', [
            'title' => 'Catering menu',
            'intro' => 'Menu examples are added by the team. Ingredients, dietary requirements and availability are confirmed for each event.',
            'items' => ContentEntry::published()->where('type', 'menu_item')->orderBy('category')->orderBy('sort_order')->orderBy('title')->paginate(12),
            'kind' => 'menu_item',
        ]);
    }

    public function gallery(): View
    {
        return view('public.listing', [
            'title' => 'A look at the details',
            'intro' => 'A growing collection of event settings and ideas. Add approved, real event photography through the website editor.',
            'items' => ContentEntry::published()->where('type', 'portfolio')->orderByDesc('is_featured')->orderBy('sort_order')->paginate(9),
            'kind' => 'portfolio',
        ]);
    }

    public function galleryEntry(string $slug): View
    {
        return $this->showEntry('portfolio', $slug, 'public.detail');
    }

    public function about(): View
    {
        $page = ContentEntry::published()->where('type', 'page')->where('slug', 'about')->first();

        return view('public.about', compact('page'));
    }

    public function faqs(): View
    {
        return view('public.faqs', [
            'faqs' => ContentEntry::published()->where('type', 'faq')->orderBy('sort_order')->orderBy('title')->get(),
        ]);
    }

    public function policy(string $slug): View
    {
        $page = ContentEntry::published()->where('type', 'page')->where('slug', $slug)->first();

        return view('public.policy', compact('page'));
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function storeContact(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::query()->create($request->safe()->only(['name', 'email', 'phone', 'subject', 'message']));

        return to_route('contact')->with('success', 'Thank you. Your message has been saved for our team to review.');
    }

    public function createBooking(): View
    {
        $serviceId = request()->integer('service');
        $packageId = request()->integer('package');

        return view('public.booking', [
            'services' => ContentEntry::published()->where('type', 'service')->orderBy('title')->get(),
            'packages' => ContentEntry::published()->where('type', 'package')->orderBy('title')->get(),
            'selectedServiceId' => $serviceId,
            'selectedPackageId' => $packageId,
            'idempotencyToken' => (string) Str::uuid(),
        ]);
    }

    public function storeBooking(StoreInquiryRequest $request, CreateInquiry $createInquiry): RedirectResponse
    {
        $inquiry = $createInquiry->handle($request->validated());

        return to_route('booking.confirmation', $inquiry->reference);
    }

    public function bookingConfirmation(string $reference): View
    {
        $inquiry = Inquiry::query()->where('reference', $reference)->firstOrFail();

        return view('public.booking-confirmation', compact('inquiry'));
    }

    private function showEntry(string $type, string $slug, string $view): View
    {
        $entry = ContentEntry::published()->where('type', $type)->where('slug', $slug)->firstOrFail();

        return view($view, ['entry' => $entry, 'kind' => $type]);
    }
}
