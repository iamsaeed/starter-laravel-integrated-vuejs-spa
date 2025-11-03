<?php

namespace App\Resources;

use App\Core\Resources\Actions\BulkDeleteAction;
use App\Core\Resources\Actions\BulkUpdateAction;
use App\Core\Resources\Fields\BelongsToMany;
use App\Core\Resources\Fields\Date;
use App\Core\Resources\Fields\Email;
use App\Core\Resources\Fields\Media;
use App\Core\Resources\Fields\Password;
use App\Core\Resources\Fields\Section;
use App\Core\Resources\Fields\Select;
use App\Core\Resources\Fields\Text;
use App\Core\Resources\Filters\BelongsToManyFilter;
use App\Core\Resources\Filters\SelectFilter;
use App\Core\Resources\Resource;
use App\Enums\Status;
use App\Models\User;

class UserResource extends Resource
{
    public static string $model = User::class;

    public static string $label = 'Users';

    public static string $singularLabel = 'User';

    public static string $title = 'name';

    public static array $search = ['name', 'email'];

    public static int $perPage = 15;

    /**
     * Fields shown in the index/table view.
     * ID and Created At are auto-added.
     */
    public function indexFields(): array
    {
        return [
            Media::make('Avatar')
                ->collection('avatars')
                ->previewSize(48, 48)
                ->rounded(),
            Text::make('Name')->sortable()->searchable(),
            Email::make('Email')->sortable()->searchable(),
            Select::make('Status')->options(Status::class)->sortable()->toggleable(true, 'active', 'inactive'),
            Date::make('Email Verified At', 'email_verified_at')->sortable(),
            BelongsToMany::make('Roles'),
        ];
    }

    /**
     * Fields shown in the detail/show view.
     * ID, Created At, and Updated At are auto-added.
     */
    public function showFields(): array
    {
        return [
            Media::make('Avatar')
                ->collection('avatars')
                ->previewSize(64, 64)
                ->rounded(),
            Text::make('Name'),
            Email::make('Email'),
            Select::make('Status')->options(Status::class),
            BelongsToMany::make('Roles'),
            Date::make('Email Verified At'),
        ];
    }

    /**
     * Fields shown in create/edit forms.
     */
    public function formFields(): array
    {
        return [
            Section::make('Profile Information')
                ->description('Basic user profile details')
                ->icon('user')
                ->fields([
                    Media::make('Avatar')
                        ->single()
                        ->collection('avatars')
                        ->images()
                        ->maxFileSize(2)
                        ->previewSize(48, 48)
                        ->rounded()
                        ->editable([
                            'aspectRatio' => 1,
                            'minWidth' => 200,
                            'minHeight' => 200,
                        ])
                        ->rules('nullable')
                        ->cols('col-span-12'),

                    Text::make('Name')
                        ->rules('required|string|max:255')
                        ->placeholder('Enter user name')
                        ->cols('col-span-12 md:col-span-6'),

                    Email::make('Email')
                        ->rules('required|email|unique:users,email')
                        ->placeholder('Enter email address')
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Security')
                ->description('Password and account status')
                ->icon('lock')
                ->fields([
                    Password::make('Password')
                        ->rules('required|string|min:8')
                        ->creationRules('required|string|min:8')
                        ->updateRules('nullable|string|min:8')
                        ->requiredOnCreate(true)
                        ->requiredOnUpdate(false)
                        ->creationPlaceholder('Enter password')
                        ->updatePlaceholder('Leave blank to keep current password')
                        ->cols('col-span-12 md:col-span-6'),

                    Select::make('Status')
                        ->options(Status::class)
                        ->rules(['required', 'in:active,inactive'])
                        ->default(Status::Active->value)
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Permissions')
                ->description('User roles and access control')
                ->icon('shield')
                ->collapsible()
                ->fields([
                    BelongsToMany::make('Roles')
                        ->resource(RoleResource::class)
                        ->titleAttribute('name')
                        ->creatable()
                        ->rules('nullable|array')
                        ->cols('col-span-12'),
                ]),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status')
                ->options(Status::class)
                ->column('status'),

            BelongsToManyFilter::make('Role')
                ->options(fn () => \App\Models\Role::pluck('name', 'id')->toArray())
                ->relationship('roles'),
        ];
    }

    public function actions(): array
    {
        return [
            BulkDeleteAction::make(),
            BulkUpdateAction::make()->fields([
                'status' => Status::class,
            ]),
        ];
    }

    public function with(): array
    {
        return ['roles'];
    }

    /**
     * Override rules to handle context-specific password rules.
     */
    public function rules(string $context = 'create'): array
    {
        $rules = [];
        $formFields = $this->flattenFields($this->getFormFields());

        foreach ($formFields as $field) {
            // Check for context-specific rules in Password fields
            if ($field instanceof Password) {
                if ($context === 'create' && isset($field->toArray()['meta']['creationRules'])) {
                    $rules[$field->attribute] = $field->toArray()['meta']['creationRules'];
                } elseif ($context === 'update' && isset($field->toArray()['meta']['updateRules'])) {
                    $rules[$field->attribute] = $field->toArray()['meta']['updateRules'];
                } elseif ($field->rules) {
                    $rules[$field->attribute] = $field->rules;
                }
            } elseif ($field->rules) {
                $rules[$field->attribute] = $field->rules;
            }
        }

        return $rules;
    }
}
