<?php

namespace Modules\Chat\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Chat\App\Http\Requests\CreateChatSessionRequest;
use Modules\Chat\App\Http\Requests\UpdateChatSessionRequest;
use Modules\Chat\App\Services\ChatSessionInterface;
use Modules\Chat\App\Services\Impl\ChatSessionImpl;

class ChatSessionController extends Controller
{
    private ChatSessionInterface $service;

    public function __construct(ChatSessionImpl $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => [

            ],
            'message' => ''
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateChatSessionRequest $request): RedirectResponse
    {
        return response()->json([
            'status' => true,
            'data' => [

            ],
            'message' => ''
        ], Response::HTTP_CREATED);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
       return response()->json([
            'status' => true,
            'data' => [

            ],
            'message' => ''
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatSessionRequest $request, $id): RedirectResponse
    {
        return response()->json([
            'status' => true,
            'data' => [

            ],
            'message' => ''
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
