<?php

namespace App\Core\Repositories;

use App\Core\Models\Frame;
use Illuminate\Database\Eloquent\Collection;

class FrameRepository
{
    /**
     * Get active, valid frames ordered by sort_order.
     *
     * @return Collection
     */
    public function getActiveFrames(): Collection
    {
        return Frame::valid()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Find frame by ID.
     *
     * @param int $id
     * @return Frame|null
     */
    public function findById(int $id): ?Frame
    {
        return Frame::find($id);
    }

    /**
     * Create a new frame.
     *
     * @param array $data
     * @return Frame
     */
    public function create(array $data): Frame
    {
        return Frame::create($data);
    }

    /**
     * Update frame.
     *
     * @param Frame $frame
     * @param array $data
     * @return bool
     */
    public function update(Frame $frame, array $data): bool
    {
        return $frame->update($data);
    }

    /**
     * Delete frame.
     *
     * @param Frame $frame
     * @return bool
     */
    public function delete(Frame $frame): bool
    {
        return $frame->delete();
    }
}

