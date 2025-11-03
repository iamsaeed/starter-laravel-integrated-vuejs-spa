<?php

namespace App\Core\Resources\Actions;

use Illuminate\Database\Eloquent\Collection;

class BulkUpdateAction extends Action
{
    protected array $fields = [];

    protected array $updateFields = [];

    public function __construct(?string $label = null, ?string $key = null)
    {
        parent::__construct($label ?? 'Update Selected', $key ?? 'bulk_update');
        $this->confirmable = true;
        $this->confirmMessage = 'Are you sure you want to update the selected items?';
    }

    public static function make(?string $label = null, ?string $key = null): static
    {
        return new static($label, $key);
    }

    public function fields(array $fields): static
    {
        $this->updateFields = $fields;
        $this->meta(['fields' => $fields]);

        return $this;
    }

    protected function actionType(): string
    {
        return 'bulk-update';
    }

    public function handle(Collection $models, array $data = []): mixed
    {
        $count = $models->count();
        foreach ($models as $model) {
            $model->update($data);
        }

        return $count;
    }
}
