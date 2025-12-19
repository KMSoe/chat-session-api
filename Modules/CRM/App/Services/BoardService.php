<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\BoardRepo;

class BoardService
{
    private $repo;

    public function __construct(BoardRepo $repo)
    {
        $this->repo = $repo;
    }

    public function boardStatusChange(array $data)
    {
        return $this->repo->boardStatusChange($data);
    }
}