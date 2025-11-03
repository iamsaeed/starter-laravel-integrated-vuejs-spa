<?php

namespace App\Core\Resources\Actions;

use Illuminate\Database\Eloquent\Collection;

class ExportAction extends Action
{
    protected array $formats = ['csv'];

    public function __construct(?string $label = null, ?string $key = null)
    {
        parent::__construct($label ?? 'Export', $key ?? 'export');
    }

    public static function make(?string $label = null, ?string $key = null): static
    {
        return new static($label, $key);
    }

    public function formats(array $formats): static
    {
        $this->formats = $formats;
        $this->meta(['formats' => $formats]);

        return $this;
    }

    protected function actionType(): string
    {
        return 'export';
    }

    public function handle(Collection $models, array $data = []): mixed
    {
        // This will be handled by the controller/service
        return null;
    }
}
