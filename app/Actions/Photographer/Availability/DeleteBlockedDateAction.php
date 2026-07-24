<?php

namespace App\Actions\Photographer\Availability;

use App\Models\BlockedDate;

class DeleteBlockedDateAction
{
    public function execute(BlockedDate $block): void
    {
        $block->delete();
    }
}