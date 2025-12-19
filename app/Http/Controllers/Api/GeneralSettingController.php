<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\GeneralSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingFormRequest;
use App\Repositories\Settings\GetSettingRepo;
use App\Repositories\Settings\UpdateSettingRepo;

class GeneralSettingController extends Controller
{
    protected $updateSettingRepo;
    protected $getSettingRepo;  

    public function __construct(UpdateSettingRepo $updateSettingRepo, GetSettingRepo $getSettingRepo) 
    {
        $this->updateSettingRepo = $updateSettingRepo;   
        $this->getSettingRepo = $getSettingRepo;   
    }

    public function show($module, GeneralSettings $settings){
        if ($module === 'general') {
           
            return $this->getSettingRepo->getGeneralSettings();

        } else {

            return response()->json([
                'success' => false,
                'message' => 'Module not found.'
            ], Response::HTTP_NOT_FOUND);
            
        }
    }

    public function update(Request $request, $module){
        if($module == 'general'){

            $settings = app(GeneralSettings::class);
            $generalRequest =app(SettingFormRequest::class);
            return $this->updateSettingRepo->updateGeneralSettings($request, $settings);

        } else {

            return response()->json([
                'success' => false,
                'message' => 'Module not found.'
            ], Response::HTTP_NOT_FOUND);

        }
        
    }

}
