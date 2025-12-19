<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Models\FileUpload;
use App\Models\Module;
use App\Trait\ResponseHandlerTrait;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    use ResponseHandlerTrait;

    public const FILE_FOLDER_WITHOUT_MODULE = 'common';

    function upload(FileUploadRequest $request) {
        $data = $request->validated();
        $file = $data['file'];
        
        $module = Module::find($data['module_id']);
        $fileName = $file->getClientOriginalName();
        $fileType = $file->getClientMimeType();
        $fileExtension = $file->getClientOriginalExtension();
        $folderPath = 'storage/uploads/'. ($module ? $module->slug : $this::FILE_FOLDER_WITHOUT_MODULE) . '/';
        $filePath = $folderPath . $fileName;
        $fileUpload = FileUpload::create([
            'name' => $fileName,
            'path' => $filePath,
            'type' => $fileType,
            'extension' => $fileExtension,
            'description' => $request->description,
            'uploaded_by' => $request->user()->id
        ]);
        $file->move(public_path($folderPath), $fileName);
        return $this->successResponse($fileUpload);
    }
}
