<?php

namespace App\Resources;

use App\Models\Timezone;
use App\Resources\Actions\BulkDeleteAction;
use App\Resources\Actions\BulkUpdateAction;
use App\Resources\Fields\BelongsToMany;
use App\Resources\Fields\Boolean;
use App\Resources\Fields\Number;
use App\Resources\Fields\Section;
use App\Resources\Fields\Select;
use App\Resources\Fields\Text;
use App\Resources\Filters\BooleanFilter;
use App\Resources\Filters\SelectFilter;

class TimezoneResource extends Resource
{
    public static string $model = Timezone::class;

    public static string $label = 'Timezones';

    public static string $singularLabel = 'Timezone';

    public static string $title = 'display_name';

    public static array $search = ['name', 'display_name', 'abbreviation', 'city_name'];

    public static int $perPage = 20;

    /**
     * Fields shown in the index/table view.
     * ID and Created At are auto-added.
     */
    public function indexFields(): array
    {
        return [
            Text::make('Name')->sortable()->searchable(),
            Text::make('Display Name', 'display_name')->sortable()->searchable(),
            Text::make('City Name', 'city_name')->sortable()->searchable(),
            Text::make('Abbreviation')->sortable(),
            Number::make('Offset')->sortable(),
            Text::make('Offset Formatted', 'offset_formatted')->sortable(),
            Boolean::make('Uses DST', 'uses_dst')->sortable()->toggleable(),
            Text::make('Region')->sortable(),
            Boolean::make('Is Primary', 'is_primary')->sortable()->toggleable(),
            Boolean::make('Is Active', 'is_active')->sortable()->toggleable(),
            BelongsToMany::make('Countries'),
        ];
    }

    /**
     * Fields shown in the detail/show view.
     * ID, Created At, and Updated At are auto-added.
     */
    public function showFields(): array
    {
        return [
            Text::make('Name'),
            Text::make('Display Name', 'display_name'),
            Text::make('City Name', 'city_name'),
            Text::make('Abbreviation'),
            Text::make('Abbreviation DST', 'abbreviation_dst'),
            Number::make('Offset'),
            Text::make('Offset Formatted', 'offset_formatted'),
            Boolean::make('Uses DST', 'uses_dst'),
            Text::make('Region'),
            Number::make('Display Order', 'display_order'),
            Boolean::make('Is Primary', 'is_primary'),
            Boolean::make('Is Active', 'is_active'),
            BelongsToMany::make('Countries'),
        ];
    }

    /**
     * Fields shown in create/edit forms.
     */
    public function formFields(): array
    {
        return [
            Section::make('Basic Information')
                ->description('Primary timezone details')
                ->icon('globe')
                ->fields([
                    Text::make('Name')
                        ->rules('required|string|max:255|unique:timezones,name')
                        ->placeholder('America/New_York')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('Display Name', 'display_name')
                        ->rules('nullable|string|max:255')
                        ->placeholder('Eastern Time')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('City Name', 'city_name')
                        ->rules('nullable|string|max:255')
                        ->placeholder('New York')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('Region')
                        ->rules('nullable|string|max:255')
                        ->placeholder('America')
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Time Details')
                ->description('Offset and DST information')
                ->icon('clock')
                ->fields([
                    Text::make('Abbreviation')
                        ->rules('nullable|string|max:10')
                        ->placeholder('EST')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('Abbreviation DST', 'abbreviation_dst')
                        ->rules('nullable|string|max:10')
                        ->placeholder('EDT')
                        ->cols('col-span-12 md:col-span-6'),

                    Number::make('Offset')
                        ->rules('nullable|integer')
                        ->placeholder('-18000')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('Offset Formatted', 'offset_formatted')
                        ->rules('nullable|string|max:10')
                        ->placeholder('UTC-05:00')
                        ->cols('col-span-12 md:col-span-6'),

                    Boolean::make('Uses DST', 'uses_dst')
                        ->rules('nullable|boolean')
                        ->default(false)
                        ->trueLabel('Yes')
                        ->falseLabel('No')
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Settings')
                ->description('Display and status settings')
                ->icon('settings')
                ->fields([
                    Number::make('Display Order', 'display_order')
                        ->rules('nullable|integer|min:0')
                        ->default(999)
                        ->cols('col-span-12 md:col-span-6'),

                    Boolean::make('Is Primary', 'is_primary')
                        ->rules('nullable|boolean')
                        ->default(false)
                        ->trueLabel('Yes')
                        ->falseLabel('No')
                        ->cols('col-span-12 md:col-span-6'),

                    Boolean::make('Is Active', 'is_active')
                        ->rules('nullable|boolean')
                        ->default(true)
                        ->trueLabel('Active')
                        ->falseLabel('Inactive')
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Countries')
                ->description('Associate countries with this timezone')
                ->icon('map')
                ->collapsible()
                ->fields([
                    Select::make('Countries')
                        ->multiple()
                        ->searchable()
                        ->serverSide()
                        ->resource(CountryResource::class)
                        ->titleAttribute('name')
                        ->rules('nullable|array')
                        ->cols('col-span-12'),
                ]),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Region')
                ->options([
                    'Africa' => 'Africa',
                    'America' => 'America',
                    'Antarctica' => 'Antarctica',
                    'Asia' => 'Asia',
                    'Atlantic' => 'Atlantic',
                    'Australia' => 'Australia',
                    'Europe' => 'Europe',
                    'Indian' => 'Indian',
                    'Pacific' => 'Pacific',
                ])
                ->column('region'),

            BooleanFilter::make('Uses DST', 'uses_dst')
                ->column('uses_dst')
                ->trueLabel('Yes')
                ->falseLabel('No'),

            BooleanFilter::make('Active', 'is_active')
                ->column('is_active')
                ->trueLabel('Active')
                ->falseLabel('Inactive'),

            BooleanFilter::make('Primary', 'is_primary')
                ->column('is_primary')
                ->trueLabel('Yes')
                ->falseLabel('No'),
        ];
    }

    public function actions(): array
    {
        return [
            BulkDeleteAction::make(),
            BulkUpdateAction::make()->fields([
                'is_active' => 'boolean',
                'is_primary' => 'boolean',
                'display_order' => 'integer',
            ]),
        ];
    }

    public function with(): array
    {
        return ['countries'];
    }
}
