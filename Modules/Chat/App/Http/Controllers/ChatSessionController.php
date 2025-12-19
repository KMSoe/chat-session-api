<?php
namespace Modules\Chat\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Chat\App\Http\Requests\CreateChatSessionRequest;
use Modules\Chat\App\Http\Requests\UpdateChatSessionRequest;
use Modules\Chat\App\resources\ChatSessionResource;
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
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'data'    => [
                'chat_sessions' => $this->service->findAll($request),
            ],
            'message' => '',
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateChatSessionRequest $request): JsonResponse
    {
        $chat_session = $this->service->create($request->validated());

        return response()->json([
            'status'  => true,
            'data'    => [
                'chat_session' => new ChatSessionResource($chat_session),
            ],
            'message' => 'Successfully Saved',
        ], Response::HTTP_CREATED);
    }

    /**
     * Show the specified resource.
     */
    public function show($session_uuid): JsonResponse
    {
        $chat_session = $this->service->findBySessionUuid($session_uuid);

        return response()->json([
            'status'  => true,
            'data'    => [
                'chat_session' => new ChatSessionResource($chat_session),
            ],
            'message' => '',
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatSessionRequest $request, $session_uuid): JsonResponse
    {
        $this->service->update($session_uuid, $request->validated());

        $chat_session = $this->service->findBySessionUuid($session_uuid);

        return response()->json([
            'status'  => true,
            'data'    => [
                'chat_session' => new ChatSessionResource($chat_session),
            ],
            'message' => 'Successfully Updated',
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($session_uuid): JsonResponse
    {
        $this->service->delete($session_uuid);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'session_uuids'   => 'array|min:1',
            'session_uuids.*' => 'exists:chat_sessions,session_uuid',
        ]);

        $this->service->bulkDelete($request->session_uuids);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
