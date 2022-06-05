<?php

namespace Filament\Tables\Actions;

use Closure;
use Filament\Support\Actions\Action as BaseAction;
use Filament\Support\Actions\Concerns\InteractsWithRecords;
use Filament\Tables\Actions\Modal\Actions\Action as ModalAction;
use Illuminate\Database\Eloquent\Collection;

class BulkAction extends BaseAction
{
    use Concerns\BelongsToTable;
    use Concerns\CanDeselectRecordsAfterCompletion;
    use InteractsWithRecords;

    protected string $view = 'tables::actions.bulk-action';

    public function call(array $data = [])
    {
        try {
            return $this->evaluate($this->getAction());
        } finally {
            if ($this->shouldDeselectRecordsAfterCompletion()) {
                $this->getLivewire()->deselectAllTableRecords();
            }
        }
    }

    public function getAction(): ?Closure
    {
        $action = $this->action;

        if (is_string($action)) {
            $action = Closure::fromCallable([$this->getLivewire(), $action]);
        }

        return $action;
    }

    protected function getLivewireSubmitActionName(): string
    {
        return 'callMountedTableBulkAction';
    }

    protected static function getModalActionClass(): string
    {
        return ModalAction::class;
    }

    public static function makeModalAction(string $name): ModalAction
    {
        /** @var ModalAction $action */
        $action = parent::makeModalAction($name);

        return $action;
    }

    protected function getDefaultEvaluationParameters(): array
    {
        return array_merge(parent::getDefaultEvaluationParameters(), [
            'records' => $this->resolveEvaluationParameter(
                'records',
                fn (): Collection => $this->getRecords(),
            ),
        ]);
    }

    protected function parseAuthorizationArguments(array $arguments): array
    {
        array_unshift($arguments, $this->getLivewire()->getTableModel());

        return $arguments;
    }
}
