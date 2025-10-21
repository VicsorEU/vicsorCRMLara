<?php

namespace App\Services\Communications\OnlineChat\Helpers;

use App\Models\OnlineChats\OnlineChat;
use Illuminate\Support\Carbon;

class CheckOnWork
{
    /**
     * @param OnlineChat $onlineChat
     *
     * @return bool
     */
    static function isWork(OnlineChat $onlineChat): bool
    {
        $workDays = $onlineChat->work_days;
        $workFrom = $onlineChat->work_from;
        $workTo = $onlineChat->work_to;

        $now = Carbon::now();

        $currentDay = strtolower($now->format('D'));
        $workDaysArray = explode(',', $workDays);
        $isWorkDay = in_array($currentDay, $workDaysArray);

        $workFromTime = Carbon::createFromFormat('H:i:s', $workFrom);

        $workToTime = Carbon::createFromFormat('H:i:s', $workTo);
        $isWorkTime = $now->between($workFromTime, $workToTime);

        return $isWorkDay && $isWorkTime;
    }
}
