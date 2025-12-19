<?php
namespace Modules\Chat\App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Chat\App\Models\ChatSession;
use Modules\Chat\App\resources\ChatSessionResource;

class ChatSessionRepository
{
    public function findAll($request)
    {
        $per_page = intval($request->per_page) ?? 10;

        $states = collect(explode(",", $request->states))->filter(function ($state) {
            return $state;
        })->values();

        $data = ChatSession::where(function ($query) use ($request, $states) {
            if (count($states) > 0 && strtolower($request->states) != 'all') {
                $query->whereIn('current_state', $states);
            }
        });

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $data->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $data->orderBy('created_at', 'DESC');
        }

        $data = $data->paginate($per_page);

        $items = $data->getCollection()->map(function ($item) {
            return new ChatSessionResource($item);
        });

        return $data->setCollection($items);
    }

    public function findById($id)
    {
        return ChatSession::findOrFail($id);
    }

    public function findBySessionUuid($session_uuid)
    {
        return ChatSession::findBySessionUuid($session_uuid);
    }

    public function create(array $data): ChatSession
    {
        return ChatSession::create($data);
    }

    public function update($session_uuid, array $data): void
    {
        DB::transaction(function () use ($session_uuid, $data) {
            $session = ChatSession::lockForUpdate()
                ->findBySessionUuid($session_uuid)
                ->firstOrFail();

            $session->updateState($data['state'], $data['meta']);

            return $session;
        });
    }

    public function delete($session_uuid)
    {
        $chatSession = ChatSession::findBySessionUuid($session_uuid);
        $chatSession->delete();
    }

    public function bulkDelete(array $session_uuids)
    {
        return ChatSession::whereIn('session_uuid', $session_uuids)->delete();
    }
}
