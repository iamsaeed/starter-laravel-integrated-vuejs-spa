<?php

namespace App\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceStoreRequest;
use App\Http\Requests\ResourceUpdateRequest;
use App\Core\Services\ResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResourceController extends Controller
{
    /**
     * Get resource metadata (fields, filters, actions).
     */
    public function meta(Request $request, string $resource): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        // Determine which fields to return based on context
        $context = $request->query('context', 'index'); // 'index' or 'form'
        $fields = $context === 'form'
            ? $resourceInstance->getFormFields()
            : $resourceInstance->getIndexFields();

        return response()->json([
            'key' => $resourceInstance::key(),
            'model' => $resourceInstance::$model,
            'label' => $resourceInstance::$label,
            'singularLabel' => $resourceInstance::$singularLabel,
            'title' => $resourceInstance::$title,
            'searchable' => $resourceInstance::$searchable,
            'search' => $resourceInstance::$search,
            'perPage' => $resourceInstance::$perPage,
            'fields' => array_map(fn ($f) => $f->toArray(), $fields),
            'filters' => array_map(fn ($f) => $f->toArray(), $resourceInstance->filters()),
            'actions' => array_map(fn ($a) => $a->toArray(), $resourceInstance->actions()),
        ]);
    }

    /**
     * List resources.
     */
    public function index(Request $request, string $resource): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $data = $service->index($request->all());

        return response()->json($data);
    }

    /**
     * Show single resource.
     */
    public function show(string $resource, int|string $id): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $model = $service->show($id);

        // Return full model data with relationships for edit forms
        return response()->json([
            'data' => $model->toArray(),
        ]);
    }

    /**
     * Create resource.
     */
    public function store(Request $request, string $resource): JsonResponse
    {
        $resourceClass = $this->resolveResourceClass($resource);
        $resourceInstance = $this->resolveResource($resource);

        // Create and configure the form request with dynamic validation
        $formRequest = app(ResourceStoreRequest::class);
        $formRequest->setResource($resourceClass);

        // Validate using dynamic rules
        $validator = Validator::make(
            $request->all(),
            $formRequest->rules(),
            $resourceInstance->messages()
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $service = new ResourceService($resourceInstance);
        $model = $service->store($validator->validated());

        return response()->json([
            'message' => $resourceInstance::$singularLabel.' created successfully',
            'data' => $resourceInstance->transform($model),
        ], 201);
    }

    /**
     * Update resource.
     */
    public function update(Request $request, string $resource, int|string $id): JsonResponse
    {
        $resourceClass = $this->resolveResourceClass($resource);
        $resourceInstance = $this->resolveResource($resource);

        // Create and configure the form request with dynamic validation
        $formRequest = app(ResourceUpdateRequest::class);
        $formRequest->setResource($resourceClass);

        // Set route resolver for accessing route parameters
        $formRequest->setRouteResolver(function () use ($request) {
            return $request->route();
        });

        // Validate using dynamic rules (includes unique rule modifications)
        $validator = Validator::make(
            $request->all(),
            $formRequest->rules(),
            $resourceInstance->messages()
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $service = new ResourceService($resourceInstance);
        $model = $service->update($id, $validator->validated());

        return response()->json([
            'message' => $resourceInstance::$singularLabel.' updated successfully',
            'data' => $resourceInstance->transform($model),
        ]);
    }

    /**
     * Partially update resource (for toggles and quick edits).
     * Accepts key-value pairs without requiring all fields.
     * Only validates the fields being updated.
     */
    public function patch(Request $request, string $resource, int|string $id): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $modelClass = $resourceInstance::model();

        // Get only the fields being updated (before filtering)
        $fields = $request->all();

        // Filter to only visible fields
        $service = new ResourceService($resourceInstance);
        $fields = $this->filterVisibleFieldsDataForPatch($resourceInstance, $fields);

        if (empty($fields)) {
            return response()->json([
                'message' => 'No fields to update',
            ], 422);
        }

        // Get all resource rules and filter to only validate fields being updated
        $allRules = $resourceInstance->rules('update');
        $rules = [];

        foreach ($fields as $field => $value) {
            if (isset($allRules[$field])) {
                $rule = $allRules[$field];

                // Handle unique rules for the specific field being updated
                if (is_string($rule) && str_contains($rule, 'unique:')) {
                    if (preg_match('/unique:([^,|]+),/', $rule)) {
                        $rule = preg_replace(
                            '/unique:([^,|]+),([^,|]+)/',
                            "unique:$1,$2,{$id}",
                            $rule
                        );
                    } else {
                        $rule = preg_replace(
                            '/unique:([^,|]+)/',
                            "unique:$1,{$field},{$id}",
                            $rule
                        );
                    }
                }

                // Remove 'required' constraint for partial updates
                if (is_string($rule)) {
                    $rule = str_replace('required|', '', $rule);
                    $rule = str_replace('|required', '', $rule);
                    if ($rule === 'required') {
                        $rule = '';
                    }
                }

                if (! empty($rule)) {
                    $rules[$field] = $rule;
                }
            }
        }

        // Validate only the fields being updated
        $validator = Validator::make(
            $fields,
            $rules,
            $resourceInstance->messages()
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $service = new ResourceService($resourceInstance);
        $model = $service->update($id, $validator->validated());

        return response()->json([
            'message' => $resourceInstance::$singularLabel.' updated successfully',
            'data' => $resourceInstance->transform($model),
        ]);
    }

    /**
     * Delete resource.
     */
    public function destroy(string $resource, int|string $id): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $service->destroy($id);

        return response()->json([
            'message' => $resourceInstance::$singularLabel.' deleted successfully',
        ]);
    }

    /**
     * Bulk delete.
     */
    public function bulkDelete(Request $request, string $resource): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $count = $service->bulkDestroy($request->input('ids'));

        return response()->json([
            'message' => "{$count} items deleted successfully",
            'affected' => $count,
        ]);
    }

    /**
     * Bulk update.
     */
    public function bulkUpdate(Request $request, string $resource): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'data' => 'required|array',
        ]);

        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $count = $service->bulkUpdate(
            $request->input('ids'),
            $request->input('data')
        );

        return response()->json([
            'message' => "{$count} items updated successfully",
            'affected' => $count,
        ]);
    }

    /**
     * Run action.
     */
    public function runAction(Request $request, string $resource, string $action): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $result = $service->runAction(
            $action,
            $request->input('ids'),
            $request->input('data', [])
        );

        return response()->json([
            'message' => 'Action completed successfully',
            'result' => $result,
        ]);
    }

    /**
     * Search related resources (for BelongsTo fields).
     */
    public function searchRelated(Request $request, string $resource): JsonResponse
    {
        $request->validate([
            'query' => 'nullable|string|max:255',
        ]);

        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $data = $service->index([
            'search' => $request->input('query'),
            'perPage' => 20,
        ]);

        return response()->json($data);
    }

    /**
     * Resolve resource class from key.
     */
    protected function resolveResourceClass(string $resourceKey): string
    {
        $resourceClass = config("resources.{$resourceKey}");

        if (! $resourceClass || ! class_exists($resourceClass)) {
            abort(404, "Resource not found: {$resourceKey}");
        }

        return $resourceClass;
    }

    /**
     * Resolve resource instance from key.
     */
    protected function resolveResource(string $resourceKey): object
    {
        $resourceClass = $this->resolveResourceClass($resourceKey);

        return new $resourceClass;
    }

    /**
     * Filter patch data to only visible fields.
     */
    protected function filterVisibleFieldsDataForPatch(object $resourceInstance, array $data): array
    {
        // Get visible fields based on form data
        $visibleFields = $resourceInstance->getVisibleFields($data);

        // Extract allowed attributes from visible fields
        $allowedAttributes = [];
        foreach ($visibleFields as $field) {
            $allowedAttributes[] = $field->attribute;
        }

        // Filter data to only include allowed attributes
        return array_intersect_key($data, array_flip($allowedAttributes));
    }
}
