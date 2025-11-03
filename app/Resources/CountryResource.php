<?php

namespace App\Resources;

use App\Core\Resources\Actions\BulkDeleteAction;
use App\Core\Resources\Actions\BulkUpdateAction;
use App\Core\Resources\Fields\BelongsToMany;
use App\Core\Resources\Fields\Boolean;
use App\Core\Resources\Fields\Image;
use App\Core\Resources\Fields\Number;
use App\Core\Resources\Fields\Section;
use App\Core\Resources\Fields\Select;
use App\Core\Resources\Fields\Text;
use App\Core\Resources\Fields\Textarea;
use App\Core\Resources\Filters\BooleanFilter;
use App\Core\Resources\Filters\SelectFilter;
use App\Core\Resources\Resource;
use App\Models\Country;

class CountryResource extends Resource
{
    public static string $model = Country::class;

    public static string $label = 'Countries';

    public static string $singularLabel = 'Country';

    public static string $title = 'name';

    public static array $search = ['name', 'code', 'code_alpha3'];

    public static int $perPage = 20;

    /**
     * Fields shown in the index/table view.
     * ID and Created At are auto-added.
     */
    public function indexFields(): array
    {
        return [
            Text::make('Name')->sortable()->searchable(),
            Text::make('Code')->sortable()->searchable(),
            Text::make('Code Alpha3', 'code_alpha3')->sortable()->searchable(),
            Text::make('Region')->sortable(),
            Text::make('Flag Emoji', 'flag_emoji')->sortable(),
            Image::make('Flag SVG', 'flag_svg')->asUrl()->width(32)->height(24)->alt('Country flag'),
            Number::make('Display Order', 'display_order')->sortable(),
            Boolean::make('Is Active', 'is_active')->sortable()->toggleable(),
            BelongsToMany::make('Timezones'),
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
            Text::make('Code'),
            Text::make('Code Alpha3', 'code_alpha3'),
            Number::make('Numeric Code', 'numeric_code'),
            Text::make('Native Name', 'native_name'),
            Text::make('Capital'),
            Text::make('Region'),
            Text::make('Subregion'),
            Text::make('Currency Code', 'currency_code'),
            Text::make('Currency Name', 'currency_name'),
            Text::make('Currency Symbol', 'currency_symbol'),
            Text::make('Phone Code', 'phone_code'),
            Text::make('Flag Emoji', 'flag_emoji'),
            Image::make('Flag SVG', 'flag_svg')->asUrl()->width(64)->height(48)->alt('Country flag'),
            Textarea::make('Languages'),
            Text::make('TLD'),
            Number::make('Latitude'),
            Number::make('Longitude'),
            Number::make('Display Order', 'display_order'),
            Boolean::make('Is Active', 'is_active'),
            Boolean::make('Is EU Member', 'is_eu_member'),
            Textarea::make('Metadata'),
            BelongsToMany::make('Timezones'),
        ];
    }

    /**
     * Fields shown in create/edit forms.
     */
    public function formFields(): array
    {
        return [
            Section::make('Basic Information')
                ->description('Primary country details')
                ->icon('globe')
                ->fields([
                    Text::make('Name')
                        ->rules('required|string|max:255')
                        ->placeholder('United States')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('Native Name', 'native_name')
                        ->rules('nullable')
                        ->placeholder('Native country name')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('Code')
                        ->rules('required|string|max:2|unique:countries,code')
                        ->placeholder('US')
                        ->cols('col-span-12 md:col-span-4'),

                    Text::make('Code Alpha3', 'code_alpha3')
                        ->rules('nullable|string|max:3')
                        ->placeholder('USA')
                        ->cols('col-span-12 md:col-span-4'),

                    Number::make('Numeric Code', 'numeric_code')
                        ->rules('required|integer|min:1|max:999')
                        ->placeholder('840')
                        ->cols('col-span-12 md:col-span-4'),

                    Text::make('Capital')
                        ->rules('nullable|string|max:255')
                        ->placeholder('Capital city')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('TLD')
                        ->rules('nullable|string|max:10')
                        ->placeholder('.us')
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Geographic Details')
                ->description('Region and location information')
                ->icon('map')
                ->fields([
                    Text::make('Region')
                        ->rules('nullable|string|max:255')
                        ->placeholder('Americas')
                        ->cols('col-span-12 md:col-span-6'),

                    Text::make('Subregion')
                        ->rules('nullable|string|max:255')
                        ->placeholder('Northern America')
                        ->cols('col-span-12 md:col-span-6'),

                    Number::make('Latitude')
                        ->rules('nullable|numeric|min:-90|max:90')
                        ->placeholder('38.8951')
                        ->cols('col-span-12 md:col-span-6'),

                    Number::make('Longitude')
                        ->rules('nullable|numeric|min:-180|max:180')
                        ->placeholder('-77.0364')
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Currency & Contact')
                ->description('Currency and phone information')
                ->icon('currency-dollar')
                ->fields([
                    Text::make('Currency Code', 'currency_code')
                        ->rules('nullable|string|max:3')
                        ->placeholder('USD')
                        ->cols('col-span-12 md:col-span-4'),

                    Text::make('Currency Name', 'currency_name')
                        ->rules('nullable|string|max:255')
                        ->placeholder('US Dollar')
                        ->cols('col-span-12 md:col-span-4'),

                    Text::make('Currency Symbol', 'currency_symbol')
                        ->rules('nullable|string|max:10')
                        ->placeholder('$')
                        ->cols('col-span-12 md:col-span-4'),

                    Text::make('Phone Code', 'phone_code')
                        ->rules('nullable|string|max:10')
                        ->placeholder('+1')
                        ->cols('col-span-12'),
                ]),

            Section::make('Visual & Language')
                ->description('Flag and language settings')
                ->icon('flag')
                ->fields([
                    Text::make('Flag Emoji', 'flag_emoji')
                        ->rules('nullable|string|max:10')
                        ->placeholder('🇺🇸')
                        ->cols('col-span-12 md:col-span-6'),

                    Textarea::make('Languages')
                        ->rules('nullable')
                        ->placeholder('["en", "es"]')
                        ->help('JSON array of language codes')
                        ->rows(3)
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

                    Boolean::make('Is Active', 'is_active')
                        ->rules('nullable|boolean')
                        ->default(true)
                        ->trueLabel('Active')
                        ->falseLabel('Inactive')
                        ->cols('col-span-12 md:col-span-6'),

                    Boolean::make('Is EU Member', 'is_eu_member')
                        ->rules('nullable|boolean')
                        ->default(false)
                        ->trueLabel('Yes')
                        ->falseLabel('No')
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Timezones')
                ->description('Associate timezones with this country')
                ->icon('clock')
                ->collapsible()
                ->fields([
                    Select::make('Timezones')
                        ->multiple()
                        ->searchable()
                        ->serverSide()
                        ->resource(TimezoneResource::class)
                        ->titleAttribute('display_name')
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
                    'Americas' => 'Americas',
                    'Asia' => 'Asia',
                    'Europe' => 'Europe',
                    'Oceania' => 'Oceania',
                    'Antarctica' => 'Antarctica',
                ])
                ->column('region'),

            SelectFilter::make('Subregion')
                ->options([
                    'Northern Africa' => 'Northern Africa',
                    'Eastern Africa' => 'Eastern Africa',
                    'Western Africa' => 'Western Africa',
                    'Middle Africa' => 'Middle Africa',
                    'Southern Africa' => 'Southern Africa',
                    'Northern America' => 'Northern America',
                    'Caribbean' => 'Caribbean',
                    'Central America' => 'Central America',
                    'South America' => 'South America',
                    'Northern Europe' => 'Northern Europe',
                    'Western Europe' => 'Western Europe',
                    'Eastern Europe' => 'Eastern Europe',
                    'Southern Europe' => 'Southern Europe',
                    'Western Asia' => 'Western Asia',
                    'Southern Asia' => 'Southern Asia',
                    'South-Eastern Asia' => 'South-Eastern Asia',
                    'Eastern Asia' => 'Eastern Asia',
                    'Central Asia' => 'Central Asia',
                    'Australia and New Zealand' => 'Australia and New Zealand',
                    'Melanesia' => 'Melanesia',
                    'Micronesia' => 'Micronesia',
                    'Polynesia' => 'Polynesia',
                ])
                ->column('subregion'),

            BooleanFilter::make('Active', 'is_active')
                ->column('is_active')
                ->trueLabel('Active')
                ->falseLabel('Inactive'),

            BooleanFilter::make('EU Member', 'is_eu_member')
                ->column('is_eu_member')
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
                'display_order' => 'integer',
            ]),
        ];
    }

    public function with(): array
    {
        return ['timezones'];
    }
}
