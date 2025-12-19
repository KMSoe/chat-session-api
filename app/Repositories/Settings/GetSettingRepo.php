<?php

namespace App\Repositories\Settings;

use App\Models\GeneralSettings;

class GetSettingRepo
{
    public function getGeneralSettings()
    {
         $datas = \DB::table('settings')
                    ->select('settings.id','settings.group','settings.name','settings.payload','settings.type','settings.option')
                    ->where('group', 'general')->get();
        return response()->json([
            'success' => true,
            'data' => $datas
        ], 200);
    }
   
   
}
