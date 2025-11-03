<?php

namespace App\Resources\Actions;

use Illuminate\Database\Eloquent\Collection;

class BulkDeleteAction extends Action
{
    public function __construct(?string $label = null, ?string $key = null)
    {
        parent::__construct($label ?? 'Delete Selected', $key ?? 'bulk_delete');
        $this->type = 'danger';
        $this->confirmable = true;
        $this->confirmMessage = 'Are you sure you want to delete the selected items?';
    }

    protected function actionType(): string
    {
        return 'bulk-delete';
    }

    public function handle(Collection $models, array $data = []): mixed
    {
        $count = $models->count();
        foreach ($models as $model) {
            $model->delete();
        }

        return $count;
    }
}
