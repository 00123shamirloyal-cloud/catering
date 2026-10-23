<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSiteSettingsRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $settings = SiteSetting::values();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(UpdateSiteSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['show_demo_notice'] = (bool) ($data['show_demo_notice'] ?? false);
        $data['service_areas'] = array_values(array_filter($data['service_areas'] ?? [], fn (?string $area): bool => filled($area)));

        foreach ($data as $key => $value) {
            SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return to_route('admin.settings.edit')->with('success', 'Website settings updated.');
    }
}
