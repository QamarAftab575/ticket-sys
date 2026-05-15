<?php

namespace App\Services;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomFieldService
{
    public const VALID_TYPES = [
        'text', 'number', 'date',
        'single_select', 'multi_select',
        'people', 'dropdown', 'currency',
        // System fields
        'system_assignee', 'system_blocked_by', 'system_blocking',
        'system_completed_on', 'system_last_modified_on',
        'system_created_on', 'system_created_by', 'system_collaborators',
    ];

    public const SELECT_TYPES = ['single_select', 'multi_select', 'dropdown'];

    public function createCustomField(Project $project, array $data): CustomField
    {
        $this->validateName($data['name'] ?? '');
        $this->validateType($data['field_type'] ?? '');
        $options  = $this->normalizeOptions($data['field_type'], $data['options'] ?? null);
        $position = CustomField::where('project_id', $project->id)->max('position') + 1;

        return CustomField::create([
            'project_id' => $project->id,
            'name'       => $data['name'],
            'field_type' => $data['field_type'],
            'options'    => $options,
            'is_global'  => false,
            'is_active'  => $data['is_active'] ?? true,
            'position'   => $position,
        ]);
    }

    public function createGlobalCustomField(array $data): CustomField
    {
        $this->validateName($data['name'] ?? '');
        $this->validateType($data['field_type'] ?? '');
        $options = $this->normalizeOptions($data['field_type'], $data['options'] ?? null);

        return CustomField::create([
            'project_id' => null,
            'name'       => $data['name'],
            'field_type' => $data['field_type'],
            'options'    => $options,
            'is_global'  => true,
            'is_active'  => $data['is_active'] ?? true,
            'position'   => 0,
        ]);
    }

    public function updateCustomField(CustomField $field, array $data): CustomField
    {
        if (isset($data['name']))       $this->validateName($data['name']);
        if (isset($data['field_type'])) $this->validateType($data['field_type']);

        $updateData = array_intersect_key($data, array_flip([
            'name', 'field_type', 'is_active', 'position',
        ]));

        if (array_key_exists('options', $data)) {
            $fieldType = $data['field_type'] ?? $field->field_type;
            $updateData['options'] = $this->normalizeOptions($fieldType, $data['options']);
        }

        $field->update($updateData);
        return $field->fresh();
    }

    public function toggleActive(CustomField $field): CustomField
    {
        $field->update(['is_active' => !$field->is_active]);
        return $field->fresh();
    }

    public function deleteCustomField(CustomField $field): bool
    {
        return (bool) $field->delete();
    }

    public function setFieldValue(Task $task, CustomField $field, mixed $value): ?CustomFieldValue
    {
        $this->validateValue($field, $value);
        $serialized = $this->serialize($field->field_type, $value);

        if ($serialized === null || $serialized === '' || $serialized === '[]') {
            CustomFieldValue::where('task_id', $task->id)
                ->where('custom_field_id', $field->id)
                ->delete();
            return null;
        }

        return CustomFieldValue::updateOrCreate(
            ['task_id' => $task->id, 'custom_field_id' => $field->id],
            ['value'   => $serialized]
        );
    }

    public function normalizeOptions(string $fieldType, ?array $options): ?array
    {
        if (!in_array($fieldType, self::SELECT_TYPES)) {
            return null;
        }
        if (empty($options)) {
            return [];
        }
        return collect($options)->values()->map(function ($opt, $index) {
            return [
                'id'       => $opt['id'] ?? (string) Str::uuid(),
                'name'     => $opt['name'] ?? '',
                'color'    => $opt['color'] ?? '#6366f1',
                'position' => $opt['position'] ?? $index,
            ];
        })->sortBy('position')->values()->toArray();
    }

    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw ValidationException::withMessages(['name' => ['Field name is required.']]);
        }
        if (strlen($name) > 100) {
            throw ValidationException::withMessages(['name' => ['Field name must not exceed 100 characters.']]);
        }
    }

    private function validateType(string $type): void
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw ValidationException::withMessages([
                'field_type' => ['Invalid field type. Must be one of: ' . implode(', ', self::VALID_TYPES)],
            ]);
        }
    }

    private function validateValue(CustomField $field, mixed $value): void
    {
        if ($value === null || $value === '') return;

        switch ($field->field_type) {
            case 'number':
            case 'currency':
                if (!is_numeric($value)) {
                    throw ValidationException::withMessages(['value' => ['Value must be numeric.']]);
                }
                break;
            case 'date':
                $d = \DateTime::createFromFormat('Y-m-d', $value);
                if (!$d || $d->format('Y-m-d') !== $value) {
                    throw ValidationException::withMessages(['value' => ['Value must be a valid date (Y-m-d).']]);
                }
                break;
            case 'single_select':
            case 'dropdown':
                $ids = collect($field->options ?? [])->pluck('id')->toArray();
                if (!in_array($value, $ids)) {
                    throw ValidationException::withMessages(['value' => ['Value must be a valid option ID.']]);
                }
                break;
            case 'multi_select':
                if (!is_array($value)) {
                    throw ValidationException::withMessages(['value' => ['Value must be an array for multi-select.']]);
                }
                $ids = collect($field->options ?? [])->pluck('id')->toArray();
                foreach ($value as $v) {
                    if (!in_array($v, $ids)) {
                        throw ValidationException::withMessages(['value' => ['All values must be valid option IDs.']]);
                    }
                }
                break;
            case 'people':
                if (!is_array($value)) {
                    throw ValidationException::withMessages(['value' => ['Value must be an array of user IDs for people fields.']]);
                }
                break;
        }
    }

    private function serialize(string $fieldType, mixed $value): ?string
    {
        if ($value === null) return null;
        if (in_array($fieldType, ['multi_select', 'people'])) {
            return json_encode(array_values((array) $value));
        }
        return (string) $value;
    }
}
