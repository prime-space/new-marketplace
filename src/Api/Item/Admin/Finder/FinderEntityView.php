<?php namespace App\Api\Item\Admin\Finder;

class FinderEntityView
{
    private $id;
    private $info;
    private $status;
    private $isBlocked;

    public function __construct(int $id, string $info, string $status = null, bool $isBlocked = false)
    {
        $this->id = $id;
        $this->info = $info;
        $this->status = $status;
        $this->isBlocked = $isBlocked;
    }

    public function toArray()
    {
        $data = ['id' => $this->id, 'info' => $this->info];
        if (null !== $this->status) {
            $data['status'] = $this->status;
            $data['isBlocked'] = $this->isBlocked;
        }

        return $data;
    }
}
