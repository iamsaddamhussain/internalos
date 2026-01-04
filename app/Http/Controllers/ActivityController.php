<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ActivityController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Record $record)
    {
        $this->authorize('create', [Activity::class, $record]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:open,done,blocked',
        ]);

        $activity = $record->activities()->create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Activity added successfully!');
    }

    public function signOff(Activity $activity)
    {
        $this->authorize('signOff', $activity);

        $activity->signOff(auth()->id());

        return back()->with('success', 'Activity signed off successfully!');
    }

    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return back()->with('success', 'Activity deleted successfully!');
    }
}
