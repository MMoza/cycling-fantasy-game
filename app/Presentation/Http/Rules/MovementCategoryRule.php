<?php

declare(strict_types=1);

namespace App\Presentation\Http\Rules;

use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Comprueba que la categoría seleccionada exista y sea compatible con el
 * tipo de movimiento recibido en la misma solicitud.
 */
class MovementCategoryRule implements ValidationRule
{
    public function __construct(private readonly ?string $type) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $category = MovementCategory::tryFrom((string) $value);

        if ($category === null) {
            $fail('La categoría seleccionada no es válida.');

            return;
        }

        $type = MovementType::tryFrom((string) $this->type);

        if ($type !== null && ! $category->supports($type)) {
            $fail('La categoría seleccionada no es compatible con el tipo de movimiento.');
        }
    }
}
