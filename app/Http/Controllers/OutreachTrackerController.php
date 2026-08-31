<?php

namespace App\Http\Controllers;

use App\Models\OutreachMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OutreachTrackerController extends Controller
{
    /**
     * 1x1 Transparent GIF pixel for tracking email opens.
     */
    public function trackOpen(int $id)
    {
        $message = OutreachMessage::find($id);
        if ($message) {
            $message->increment('open_count');
            if (!$message->opened_at) {
                $message->update([
                    'opened_at' => now(),
                    'status' => 'opened',
                ]);
            }
        }

        // Return 1x1 transparent GIF (43 bytes)
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Redirects clicked link while recording CTR metrics.
     */
    public function trackClick(Request $request, int $id)
    {
        $targetUrl = $request->query('url', 'https://nexidant.com');

        $message = OutreachMessage::find($id);
        if ($message) {
            $message->increment('click_count');
            if (!$message->clicked_at) {
                $message->update(['clicked_at' => now()]);
            }
        }

        return redirect()->away($targetUrl);
    }
}
