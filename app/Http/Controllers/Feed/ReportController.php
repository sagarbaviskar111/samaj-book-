<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\StoreReportRequest;
use App\Models\Report;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        $report = Report::firstOrCreate(
            [
                'reporter_id' => $user->id,
                'target_type' => $data['target_type'],
                'target_id' => $data['target_id'],
                'status' => 'open',
            ],
            ['reason' => $data['reason'] ?? null]
        );

        $msg = $report->wasRecentlyCreated
            ? __('Thanks, we will review this report.')
            : __('You have already reported this. Our team is reviewing it.');

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'duplicate' => ! $report->wasRecentlyCreated, 'message' => $msg])
            : back()->with('status', $msg);
    }
}
