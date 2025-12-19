<?php

namespace App\Repositories\Settings;

use App\Models\GeneralSettings;

class UpdateSettingRepo
{
    public function updateGeneralSettings($request, $settings)
    {
        // $data = $request->validated();

        // Update the settings using the validated data
        $settings->site_name = $request['site_name'];
        $settings->site_active = $request['site_active'];   
        $settings->save();

        return response()->json([
            'success' => true,
            'data' => $settings
        ], 200);
    }
   
   
}
