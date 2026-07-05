<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

abstract class AjaxMasterController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function moduleName(): string;

    abstract protected function validationRules(?int $id = null): array;

    protected function searchColumns(): array
    {
        return ['name'];
    }

    protected function orderColumn(): string
    {
        return 'created_at';
    }

    protected function orderDirection(): string
    {
        return 'desc';
    }

    protected function withRelations(): array
    {
        return [];
    }

    protected function transformRecord(Model $record): array
    {
        return $record->toArray();
    }

    protected function beforeStore(array &$data): void {}

    protected function beforeUpdate(array &$data, Model $record): void {}

    /** @return array<string, string> Form file input name => database column */
    protected function fileFields(): array
    {
        return [];
    }

    protected function uploadDirectory(): string
    {
        return str_replace('-', '_', $this->moduleName());
    }

    protected function handleFileUploads(Request $request, array &$data, ?Model $record = null): void
    {
        foreach ($this->fileFields() as $fileKey => $column) {
            if ($request->hasFile($fileKey)) {
                $existing = $record?->{$column};
                if ($existing && ! str_starts_with($existing, 'http')) {
                    Storage::disk('public')->delete($existing);
                }
                $data[$column] = $request->file($fileKey)->store($this->uploadDirectory(), 'public');
            }
            unset($data[$fileKey]);
        }
    }

    public function index(): View
    {
        return view($this->viewName());
    }

    protected function viewName(): string
    {
        return 'admin.masters.'.$this->moduleName().'.index';
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->modelClass()::query()->with($this->withRelations());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                foreach ($this->searchColumns() as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->orderBy($this->orderColumn(), $this->orderDirection())->get();

        return response()->json([
            'success' => true,
            'data' => $records->map(fn ($r) => $this->transformRecord($r)),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->transformRecord($record),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->validationRules());
        $this->handleFileUploads($request, $data);
        $this->beforeStore($data);

        $record = $this->modelClass()::create($data);

        ActivityLogger::log('created', $this->moduleName(), $record, "Created {$this->moduleName()} record");

        return response()->json([
            'success' => true,
            'message' => Str::title(str_replace('-', ' ', $this->moduleName())).' created successfully.',
            'data' => $this->transformRecord($record),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = $this->findOrFail($id);
        $data = $request->validate($this->validationRules($id));
        $this->handleFileUploads($request, $data, $record);
        $this->beforeUpdate($data, $record);

        $record->update($data);

        ActivityLogger::log('updated', $this->moduleName(), $record, "Updated {$this->moduleName()} record");

        return response()->json([
            'success' => true,
            'message' => Str::title(str_replace('-', ' ', $this->moduleName())).' updated successfully.',
            'data' => $this->transformRecord($record->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $record = $this->findOrFail($id);
        $this->deleteUploadedFiles($record);
        $record->delete();

        ActivityLogger::log('deleted', $this->moduleName(), null, "Deleted {$this->moduleName()} #{$id}");

        return response()->json([
            'success' => true,
            'message' => Str::title(str_replace('-', ' ', $this->moduleName())).' deleted successfully.',
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $record = $this->findOrFail($id);
        $record->status = $record->status === 'active' ? 'inactive' : 'active';
        $record->save();

        ActivityLogger::log('status_changed', $this->moduleName(), $record, "Status changed to {$record->status}");

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'data' => $this->transformRecord($record),
        ]);
    }

    protected function findOrFail(int $id): Model
    {
        return $this->modelClass()::with($this->withRelations())->findOrFail($id);
    }

    protected function deleteUploadedFiles(Model $record): void
    {
        foreach (array_values($this->fileFields()) as $column) {
            $path = $record->{$column};
            if ($path && ! str_starts_with($path, 'http')) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
