<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Conference;
use App\Models\Track;


class ConferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Optional: add pagination support
            $perPage = $request->get('per_page', 10); // default 10 per page

            $conferences = Conference::with('tracks')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'message' => 'Conferences fetched successfully',
                'conferences' => $conferences
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching conferences',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'accronym' => 'required|string',
            'organiser' => 'required|string',
            'location' => 'required|string',
            'abstract_deadline' => 'nullable|date',
            'fullpaper_deadline' => 'nullable|date',
            'review_deadline' => 'nullable|date',
            'camera_ready_deadline' => 'nullable|date',
            'requires_payment_before_submission' => 'boolean',
            'fee' => 'nullable|numeric',
            'rules' => 'nullable|string',
            'tracks' => 'required|array',
            'tracks.*' => 'string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // 1️⃣ Create the conference
            $conference = Conference::create([
                'user_id' => $validated['user_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'abstract_deadline' => $validated['abstract_deadline'] ?? null,
                'fullpaper_deadline' => $validated['fullpaper_deadline'] ?? null,
                'review_deadline' => $validated['review_deadline'] ?? null,
                'camera_ready_deadline' => $validated['camera_ready_deadline'] ?? null,
                'location' => $validated['location'] ?? null,
                'organiser' => $validated['organiser'] ?? null,
                'accronym' => $validated['accronym'] ?? null,
                'requires_payment_before_submission' => $validated['requires_payment_before_submission'] ?? false,
                'fee' => $validated['fee'] ?? 0,
                'rules' => $validated['rules'] ?? null,
            ]);

            // 2️⃣ Add tracks (avoid duplicates)
            foreach ($validated['tracks'] as $trackName) {
                $trackName = trim($trackName);

                // Check if this track already exists for this conference
                $exists = Track::where('conference_id', $conference->id)
                    ->whereRaw('LOWER(name) = ?', [strtolower($trackName)])
                    ->exists();

                if (!$exists) {
                    Track::create([
                        'conference_id' => $conference->id,
                        'name' => $trackName,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Conference created successfully',
                'conference' => $conference->load('tracks'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while creating the conference',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $conference = Conference::with('tracks')->findOrFail($id);

            return response()->json([
                'message' => 'Conference fetched successfully',
                'conference' => $conference
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Conference not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the conference',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'abstract_deadline' => 'nullable|date',
            'fullpaper_deadline' => 'nullable|date',
            'review_deadline' => 'nullable|date',
            'camera_ready_deadline' => 'nullable|date',
            'accronym' => 'required|string',
            'organiser' => 'required|string',
            'location' => 'required|string',
            'fee' => 'nullable|numeric',
            'rules' => 'nullable|string',
            'tracks' => 'nullable|array',
            'tracks.*' => 'string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $conference = Conference::findOrFail($id);

            // 1️⃣ Update the conference itself
            $conference->update([
                'title' => $validated['title'] ?? $conference->title,
                'location' => $validated['location'] ?? $conference->location,
                'organiser' => $validated['organiser'] ?? $conference->organiser,
                'accronym' => $validated['accronym'] ?? $conference->accronym,
                'description' => $validated['description'] ?? $conference->description,
                'abstract_deadline' => $validated['abstract_deadline'] ?? $conference->abstract_deadline,
                'fullpaper_deadline' => $validated['fullpaper_deadline'] ?? $conference->fullpaper_deadline,
                'review_deadline' => $validated['review_deadline'] ?? $conference->review_deadline,
                'camera_ready_deadline' => $validated['camera_ready_deadline'] ?? $conference->camera_ready_deadline,
                'fee' => $validated['fee'] ?? $conference->fee,
                'rules' => $validated['rules'] ?? $conference->rules,
            ]);

            // 2️⃣ Sync the tracks
            if (isset($validated['tracks'])) {
                $trackNames = collect($validated['tracks'])
                    ->map(fn($name) => trim(strtolower($name))) // normalize
                    ->unique()
                    ->values();

                // Get existing track names for this conference
                $existingTracks = Track::where('conference_id', $conference->id)->get();

                $existingNames = $existingTracks->pluck('name')->map(fn($n) => strtolower($n))->toArray();

                // Add new tracks
                foreach ($trackNames as $trackName) {
                    if (!in_array($trackName, $existingNames)) {
                        Track::create([
                            'conference_id' => $conference->id,
                            'name' => ucwords($trackName),
                        ]);
                    }
                }

                // Delete removed tracks
                $tracksToDelete = $existingTracks->filter(function ($track) use ($trackNames) {
                    return !in_array(strtolower($track->name), $trackNames->toArray());
                });

                if ($tracksToDelete->isNotEmpty()) {
                    Track::whereIn('id', $tracksToDelete->pluck('id'))->delete();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Conference updated successfully',
                'conference' => $conference->load('tracks'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the conference',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $conference = Conference::findOrFail($id);

            $conference->delete(); // Tracks will be deleted automatically (cascade)

            return response()->json([
                'message' => 'Conference deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Conference not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the conference',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
