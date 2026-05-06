<?php

namespace App\Http\Controllers;

use App\Models\PlantModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class PlantController extends Controller
{
    /**
     * Display a listing of the resource with pagination.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 15);
            $search = $request->query('search', '');
            $sortBy = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');

            // Validate pagination parameters
            $perPage = min((int) $perPage, 100); // Cap at 100 per page

            $query = PlantModel::query();

            // Search functionality
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('scientific_name', 'like', "%{$search}%")
                      ->orWhere('variety', 'like', "%{$search}%");
                });
            }

            // Sorting
            $allowedSortFields = ['id', 'name', 'created_at', 'updated_at', 'planting_date'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
            }

            $plants = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $plants->items(),
                'pagination' => [
                    'current_page' => $plants->currentPage(),
                    'last_page' => $plants->lastPage(),
                    'per_page' => $plants->perPage(),
                    'total' => $plants->total(),
                    'from' => $plants->firstItem(),
                    'to' => $plants->lastItem()
                ],
                'message' => 'Plants retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve plants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'scientific_name' => 'nullable|string|max:255',
                'variety' => 'nullable|string|max:255',
                'planting_date' => 'nullable|date',
                'harvest_date' => 'nullable|date|after_or_equal:planting_date',
                'quantity' => 'nullable|integer|min:0',
                'unit' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:255',
                'status' => 'nullable|in:planted,growing,harvested,cancelled',
                'notes' => 'nullable|string',
                'fertilizer_type' => 'nullable|string|max:255',
                'watering_schedule' => 'nullable|string|max:255',
                'growth_stage' => 'nullable|string|max:100',
                'health_status' => 'nullable|in:healthy,diseased,pest_infested,nutrient_deficient',
                'yield_estimate' => 'nullable|numeric|min:0',
                'actual_yield' => 'nullable|numeric|min:0',
                'expenses' => 'nullable|numeric|min:0',
                'revenue' => 'nullable|numeric|min:0'
            ]);

            // Set default status if not provided
            if (!isset($validated['status'])) {
                $validated['status'] = 'planted';
            }

            // Calculate growing days if planting date is provided
            if (isset($validated['planting_date'])) {
                $validated['growing_days'] = Carbon::parse($validated['planting_date'])->diffInDays(now());
            }

            $plant = PlantModel::create($validated);

            return response()->json([
                'success' => true,
                'data' => $plant,
                'message' => 'Plant created successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create plant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $plant = PlantModel::findOrFail($id);

            // Calculate current growing days dynamically
            if ($plant->planting_date) {
                $plant->current_growing_days = Carbon::parse($plant->planting_date)->diffInDays(now());
            }

            return response()->json([
                'success' => true,
                'data' => $plant,
                'message' => 'Plant retrieved successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Plant not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve plant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $plant = PlantModel::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'scientific_name' => 'nullable|string|max:255',
                'variety' => 'nullable|string|max:255',
                'planting_date' => 'nullable|date',
                'harvest_date' => 'nullable|date|after_or_equal:planting_date',
                'quantity' => 'nullable|integer|min:0',
                'unit' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:255',
                'status' => 'nullable|in:planted,growing,harvested,cancelled',
                'notes' => 'nullable|string',
                'fertilizer_type' => 'nullable|string|max:255',
                'watering_schedule' => 'nullable|string|max:255',
                'growth_stage' => 'nullable|string|max:100',
                'health_status' => 'nullable|in:healthy,diseased,pest_infested,nutrient_deficient',
                'yield_estimate' => 'nullable|numeric|min:0',
                'actual_yield' => 'nullable|numeric|min:0',
                'expenses' => 'nullable|numeric|min:0',
                'revenue' => 'nullable|numeric|min:0'
            ]);

            // Recalculate growing days if planting date changed
            if (isset($validated['planting_date'])) {
                $validated['growing_days'] = Carbon::parse($validated['planting_date'])->diffInDays(now());
            }

            // Auto-update status based on harvest date
            if (isset($validated['harvest_date']) && !isset($validated['status'])) {
                $validated['status'] = 'harvested';
            }

            $plant->update($validated);

            return response()->json([
                'success' => true,
                'data' => $plant->fresh(),
                'message' => 'Plant updated successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Plant not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $plant = PlantModel::findOrFail($id);
            
            // Optional: Check for related records before deletion
            // if ($plant->harvests()->count() > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Cannot delete plant with existing harvest records'
            //     ], 409);
            // }

            $plant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plant deleted successfully',
                'data' => ['id' => $id]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Plant not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete plant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plants by status.
     */
    public function getByStatus(Request $request, string $status)
    {
        try {
            $validStatuses = ['planted', 'growing', 'harvested', 'cancelled'];
            
            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status. Valid statuses: ' . implode(', ', $validStatuses)
                ], 400);
            }

            $perPage = $request->query('per_page', 15);
            $plants = PlantModel::where('status', $status)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $plants,
                'message' => "Plants with status '{$status}' retrieved successfully"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve plants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get planting statistics.
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_plants' => PlantModel::count(),
                'by_status' => PlantModel::selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'by_health' => PlantModel::selectRaw('health_status, count(*) as count')
                    ->groupBy('health_status')
                    ->pluck('count', 'health_status'),
                'total_estimated_yield' => PlantModel::sum('yield_estimate'),
                'total_actual_yield' => PlantModel::sum('actual_yield'),
                'total_expenses' => PlantModel::sum('expenses'),
                'total_revenue' => PlantModel::sum('revenue'),
                'recently_planted' => PlantModel::where('planting_date', '>=', Carbon::now()->subDays(30))
                    ->count(),
                'ready_for_harvest' => PlantModel::where('status', 'growing')
                    ->where('harvest_date', '<=', Carbon::now()->addDays(7))
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Plant statistics retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}