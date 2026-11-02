<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\DTOs\Finance\MovementFiltersDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Finance\CreateMovementUseCase;
use App\Application\UseCases\Finance\DeleteMovementUseCase;
use App\Application\UseCases\Finance\ListMovementsUseCase;
use App\Application\UseCases\Finance\UpdateMovementUseCase;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Admin\StoreFinancialMovementRequest;
use App\Presentation\Http\Requests\Admin\UpdateFinancialMovementRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialMovementController extends Controller
{
    public function __construct(
        private readonly ListMovementsUseCase $listMovementsUseCase,
        private readonly CreateMovementUseCase $createMovementUseCase,
        private readonly UpdateMovementUseCase $updateMovementUseCase,
        private readonly DeleteMovementUseCase $deleteMovementUseCase,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $this->filtersFrom($request);

        try {
            $page = $this->listMovementsUseCase->execute($filters);
        } catch (ApplicationException $exception) {
            Inertia::flash('error', $exception->getMessage());
            $page = $this->listMovementsUseCase->execute($filters->withoutRange());
        }

        return Inertia::render('Admin/Finance', $page->toArray());
    }

    public function store(StoreFinancialMovementRequest $request): RedirectResponse
    {
        try {
            $this->createMovementUseCase->execute($request->toDTO());
        } catch (ApplicationException $exception) {
            Inertia::flash('error', $exception->getMessage());

            return redirect()->back();
        }

        Inertia::flash('success', 'Movimiento creado correctamente');

        return redirect()->route('admin.finance.index');
    }

    public function update(UpdateFinancialMovementRequest $request, string $movement): RedirectResponse
    {
        try {
            $this->updateMovementUseCase->execute($request->toDTO());
        } catch (ApplicationException $exception) {
            Inertia::flash('error', $exception->getMessage());

            return redirect()->back();
        }

        Inertia::flash('success', 'Movimiento actualizado correctamente');

        return redirect()->route('admin.finance.index');
    }

    public function destroy(Request $request, string $movement): RedirectResponse
    {
        try {
            $this->deleteMovementUseCase->execute($movement, $request->user());
        } catch (ApplicationException $exception) {
            Inertia::flash('error', $exception->getMessage());

            return redirect()->back();
        }

        Inertia::flash('success', 'Movimiento eliminado correctamente');

        return redirect()->route('admin.finance.index');
    }

    private function filtersFrom(Request $request): MovementFiltersDTO
    {
        $year = filter_var($request->query('year'), FILTER_VALIDATE_INT);

        return new MovementFiltersDTO(
            year: is_int($year) && $year > 0 ? $year : (int) date('Y'),
            type: MovementType::tryFrom((string) $request->query('type', '')),
            category: MovementCategory::tryFrom((string) $request->query('category', '')),
            from: $this->dateParam($request, 'from'),
            to: $this->dateParam($request, 'to'),
            page: max(1, (int) $request->query('page', 1)),
        );
    }

    private function dateParam(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }
}
