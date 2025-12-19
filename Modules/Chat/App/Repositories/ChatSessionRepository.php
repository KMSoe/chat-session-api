<?php

namespace Modules\Chat\App\Repositories;

use Modules\Chat\App\Models\ChatSession;



class ChatSessionRepository
{
    public function findAll($request)
    {
        
    }

    public function findById($id)
    {
        
    }

   
    public function create(array $data)
    {
        
    }

    public function update($id, array $data)
    {
        
    }
    
    public function delete($id)
    {
        $chatSession = ChatSession::findOrFail($id);
        $chatSession->delete();
    }

    public function bulkDelete(array $ids) {
        return ChatSession::whereIn('id', $ids)->delete();
    }
}
