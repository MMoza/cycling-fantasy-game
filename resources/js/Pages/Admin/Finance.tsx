import { useEffect, useMemo, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowDownRight, ArrowUpRight, Pencil, Plus, Trash2, Wallet } from 'lucide-react';

interface Movement {
    id: string;
    type: 'income' | 'expense';
    type_label: string;
    amount: string;
    concept: string;
    category: string;
    category_label: string;
    occurred_on: string;
    source: string;
    source_label: string;
    status: 'confirmed' | 'pending';
    status_label: string;
    notes: string | null;
    created_by: string | null;
    created_at: string;
    updated_at: string;
}

interface CategoryOption {
    value: string;
    label: string;
    type: 'income' | 'expense';
}

interface FinanceProps {
    summary: {
        year: number;
        period: { from: string; to: string };
        income_total: string;
        expense_total: string;
        balance: string;
        by_category: Array<{ type: string; category: string; label: string; total: string; count: number }>;
        top_expenses: Movement[];
        movements_count: number;
    };
    movements: {
        data: Movement[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        year: number;
        type: string | null;
        category: string | null;
        from: string | null;
        to: string | null;
        page: number;
    };
    categories: CategoryOption[];
}

const typeColors: Record<string, string> = {
    income: 'bg-green-100 text-green-800',
    expense: 'bg-red-100 text-red-800',
};

const statusColors: Record<string, string> = {
    confirmed: 'bg-green-100 text-green-800',
    pending: 'bg-amber-100 text-amber-800',
};

const emptyForm = {
    type: 'expense',
    amount: '',
    concept: '',
    category: '',
    occurred_on: '',
    notes: '',
};

/** Formatea un decimal "1234.56" sin aritmética de coma flotante: "1.234,56 €". */
function formatEur(value: string): string {
    const negative = value.startsWith('-');
    const [units, decimals = '00'] = value.replace('-', '').split('.');
    const grouped = units.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return `${negative ? '-' : ''}${grouped},${decimals.padEnd(2, '0')} €`;
}

function formatDate(iso: string): string {
    const [year, month, day] = iso.split('-');

    return `${day}/${month}/${year}`;
}

export default function Index({ summary, movements, filters, categories }: FinanceProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };

    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<Movement | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Movement | null>(null);

    const [year, setYear] = useState(String(filters.year));
    const [type, setType] = useState(filters.type ?? 'all');
    const [category, setCategory] = useState(filters.category ?? 'all');
    const [from, setFrom] = useState(filters.from ?? '');
    const [to, setTo] = useState(filters.to ?? '');

    const { data, setData, post, patch, processing, errors, reset, clearErrors } = useForm({ ...emptyForm });

    // Mantiene los controles de filtro sincronizados con los filtros aplicados.
    useEffect(() => {
        setYear(String(filters.year));
        setType(filters.type ?? 'all');
        setCategory(filters.category ?? 'all');
        setFrom(filters.from ?? '');
        setTo(filters.to ?? '');
    }, [filters.year, filters.type, filters.category, filters.from, filters.to]);

    // Reabre el formulario si la validación del servidor devuelve errores.
    useEffect(() => {
        if (Object.keys(errors).length > 0) {
            setFormOpen(true);
        }
    }, [errors]);

    const yearOptions = useMemo(() => {
        const current = filters.year;

        return Array.from({ length: 6 }, (_, index) => String(current + 1 - index));
    }, [filters.year]);

    const filterCategoryOptions = useMemo(() => {
        const relevant = type === 'all' ? categories : categories.filter((c) => c.type === type);
        const seen = new Map<string, CategoryOption>();

        relevant.forEach((option) => {
            if (!seen.has(option.value)) {
                seen.set(option.value, option);
            }
        });

        return Array.from(seen.values());
    }, [categories, type]);

    const formCategoryOptions = useMemo(
        () => categories.filter((c) => c.type === data.type),
        [categories, data.type],
    );

    const filterParams = () => ({
        year,
        type: type === 'all' ? '' : type,
        category: category === 'all' ? '' : category,
        from,
        to,
    });

    const applyFilters = (event: React.FormEvent) => {
        event.preventDefault();
        router.get(route('admin.finance.index'), filterParams(), { preserveScroll: true });
    };

    const clearFilters = () => {
        router.get(route('admin.finance.index'), {}, { preserveScroll: true });
    };

    const goToPage = (page: number) => {
        router.get(route('admin.finance.index'), { ...filterParams(), page }, { preserveScroll: true });
    };

    const openCreate = () => {
        reset();
        clearErrors();
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (movement: Movement) => {
        clearErrors();
        setEditing(movement);
        setData({
            type: movement.type,
            amount: movement.amount,
            concept: movement.concept,
            category: movement.category,
            occurred_on: movement.occurred_on,
            notes: movement.notes ?? '',
        });
        setFormOpen(true);
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setFormOpen(false);
                reset();
            },
        };

        if (editing) {
            patch(route('admin.finance.update', editing.id), options);
        } else {
            post(route('admin.finance.store'), options);
        }
    };

    const confirmDelete = () => {
        if (!deleteTarget) return;

        router.delete(route('admin.finance.destroy', deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => setDeleteTarget(null),
        });
    };

    const balanceClass = summary.balance.startsWith('-') ? 'text-red-600' : 'text-green-600';

    return (
        <AdminLayout>
            <Head title="Finanzas" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Finanzas</h1>
                        <p className="text-sm text-muted-foreground mt-1">
                            Gastos e ingresos de la temporada {summary.year}
                        </p>
                    </div>
                    <Button onClick={openCreate}>
                        <Plus className="h-4 w-4 mr-2" />
                        Nuevo movimiento
                    </Button>
                </div>

                {/* Resumen */}
                <div className="grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <ArrowUpRight className="h-4 w-4 text-green-600" />
                                Ingresos
                            </div>
                            <div className="mt-1 text-2xl font-semibold text-green-600">
                                {formatEur(summary.income_total)}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <ArrowDownRight className="h-4 w-4 text-red-600" />
                                Gastos
                            </div>
                            <div className="mt-1 text-2xl font-semibold text-red-600">
                                {formatEur(summary.expense_total)}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Wallet className="h-4 w-4" />
                                Balance
                            </div>
                            <div className={`mt-1 text-2xl font-semibold ${balanceClass}`}>
                                {formatEur(summary.balance)}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <p className="text-xs text-muted-foreground">
                    Período: {formatDate(summary.period.from)} – {formatDate(summary.period.to)} ·{' '}
                    {summary.movements_count} movimiento{summary.movements_count === 1 ? '' : 's'} confirmado
                    {summary.movements_count === 1 ? '' : 's'}
                </p>

                {/* Filtros */}
                <form onSubmit={applyFilters} className="grid grid-cols-2 gap-3 md:flex md:items-end">
                    <div className="space-y-1">
                        <Label>Temporada</Label>
                        <Select value={year} onValueChange={(v) => v && setYear(v)}>
                            <SelectTrigger className="w-full md:w-32">
                                <SelectValue format={(v) => String(v)} />
                            </SelectTrigger>
                            <SelectContent>
                                {yearOptions.map((option) => (
                                    <SelectItem key={option} value={option}>
                                        {option}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-1">
                        <Label>Tipo</Label>
                        <Select
                            value={type}
                            onValueChange={(v) => {
                                if (!v) return;
                                setType(v);
                                setCategory('all');
                            }}
                        >
                            <SelectTrigger className="w-full md:w-36">
                                <SelectValue
                                    format={(v) =>
                                        v === 'income' ? 'Ingresos' : v === 'expense' ? 'Gastos' : 'Todos'
                                    }
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="income">Ingresos</SelectItem>
                                <SelectItem value="expense">Gastos</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-1">
                        <Label>Categoría</Label>
                        <Select value={category} onValueChange={(v) => v && setCategory(v)}>
                            <SelectTrigger className="w-full md:w-44">
                                <SelectValue
                                    format={(v) =>
                                        filterCategoryOptions.find((o) => o.value === v)?.label ?? 'Todas'
                                    }
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas</SelectItem>
                                {filterCategoryOptions.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="filter-from">Desde</Label>
                        <Input
                            id="filter-from"
                            type="date"
                            value={from}
                            onChange={(e) => setFrom(e.target.value)}
                            className="md:w-40"
                        />
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="filter-to">Hasta</Label>
                        <Input
                            id="filter-to"
                            type="date"
                            value={to}
                            onChange={(e) => setTo(e.target.value)}
                            className="md:w-40"
                        />
                    </div>
                    <div className="flex gap-2">
                        <Button type="submit" variant="outline">
                            Aplicar
                        </Button>
                        <Button type="button" variant="ghost" onClick={clearFilters}>
                            Limpiar
                        </Button>
                    </div>
                </form>

                {/* Mensajes */}
                {flash?.success && (
                    <div className="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {flash.error}
                    </div>
                )}

                {/* Tabla de movimientos */}
                <div className="border border-border rounded-lg overflow-x-auto">
                    <table className="w-full">
                        <thead className="bg-muted/50">
                            <tr>
                                <th className="text-left px-4 py-3 text-sm font-medium">Fecha</th>
                                <th className="text-left px-4 py-3 text-sm font-medium">Concepto</th>
                                <th className="text-left px-4 py-3 text-sm font-medium">Categoría</th>
                                <th className="text-left px-4 py-3 text-sm font-medium">Tipo</th>
                                <th className="text-left px-4 py-3 text-sm font-medium">Estado</th>
                                <th className="text-right px-4 py-3 text-sm font-medium">Importe</th>
                                <th className="text-right px-4 py-3 text-sm font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {movements.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">
                                        <Wallet className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                        <p>No hay movimientos con los filtros seleccionados</p>
                                    </td>
                                </tr>
                            ) : (
                                movements.data.map((movement) => (
                                    <tr key={movement.id} className="hover:bg-muted/30">
                                        <td className="px-4 py-3 text-sm whitespace-nowrap">
                                            {formatDate(movement.occurred_on)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="font-medium">{movement.concept}</div>
                                            {movement.notes && (
                                                <div className="text-xs text-muted-foreground">{movement.notes}</div>
                                            )}
                                            <div className="text-xs text-muted-foreground">
                                                {movement.source_label}
                                                {movement.created_by ? ` · ${movement.created_by}` : ''}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-sm">{movement.category_label}</td>
                                        <td className="px-4 py-3">
                                            <Badge className={typeColors[movement.type]}>{movement.type_label}</Badge>
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge className={statusColors[movement.status]}>
                                                {movement.status_label}
                                            </Badge>
                                        </td>
                                        <td
                                            className={`px-4 py-3 text-sm text-right whitespace-nowrap font-medium ${
                                                movement.type === 'income' ? 'text-green-600' : 'text-red-600'
                                            }`}
                                        >
                                            {movement.type === 'income' ? '+' : '−'}
                                            {formatEur(movement.amount)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-2">
                                                <Button variant="ghost" size="sm" onClick={() => openEdit(movement)}>
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => setDeleteTarget(movement)}
                                                >
                                                    <Trash2 className="h-4 w-4 text-destructive" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Paginación */}
                {movements.last_page > 1 && (
                    <div className="mt-6 flex items-center justify-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={movements.current_page <= 1}
                            onClick={() => goToPage(movements.current_page - 1)}
                        >
                            Anterior
                        </Button>
                        <span className="text-sm text-muted-foreground">
                            Página {movements.current_page} de {movements.last_page}
                            {' · '}
                            {movements.total} movimientos
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={movements.current_page >= movements.last_page}
                            onClick={() => goToPage(movements.current_page + 1)}
                        >
                            Siguiente
                        </Button>
                    </div>
                )}
            </div>

            {/* Alta / edición */}
            <Dialog open={formOpen} onOpenChange={(open: boolean) => setFormOpen(open)}>
                <DialogContent>
                    <form onSubmit={submit} className="space-y-4">
                        <DialogHeader>
                            <DialogTitle>{editing ? 'Editar movimiento' : 'Nuevo movimiento'}</DialogTitle>
                            <DialogDescription>
                                {editing
                                    ? 'Modifica los datos del movimiento financiero.'
                                    : 'Registra un gasto o un ingreso del proyecto.'}
                            </DialogDescription>
                        </DialogHeader>

                        <div className="space-y-2">
                            <Label>Tipo</Label>
                            <Select
                                value={data.type}
                                onValueChange={(v) => {
                                    if (!v) return;
                                    setData('type', v);
                                    setData('category', '');
                                }}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue
                                        format={(v) => (v === 'income' ? 'Ingreso' : 'Gasto')}
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="expense">Gasto</SelectItem>
                                    <SelectItem value="income">Ingreso</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.type && <p className="text-sm text-destructive">{errors.type}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="amount">Importe (€)</Label>
                            <Input
                                id="amount"
                                inputMode="decimal"
                                placeholder="0.00"
                                value={data.amount}
                                onChange={(e) => setData('amount', e.target.value)}
                            />
                            {errors.amount && <p className="text-sm text-destructive">{errors.amount}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="concept">Concepto</Label>
                            <Input
                                id="concept"
                                maxLength={160}
                                value={data.concept}
                                onChange={(e) => setData('concept', e.target.value)}
                            />
                            {errors.concept && <p className="text-sm text-destructive">{errors.concept}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label>Categoría</Label>
                            <Select value={data.category} onValueChange={(v) => v && setData('category', v)}>
                                <SelectTrigger className="w-full">
                                    <SelectValue
                                        format={(v) =>
                                            formCategoryOptions.find((o) => o.value === v)?.label ??
                                            'Selecciona una categoría'
                                        }
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    {formCategoryOptions.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.category && <p className="text-sm text-destructive">{errors.category}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="occurred_on">Fecha</Label>
                            <Input
                                id="occurred_on"
                                type="date"
                                value={data.occurred_on}
                                onChange={(e) => setData('occurred_on', e.target.value)}
                            />
                            {errors.occurred_on && (
                                <p className="text-sm text-destructive">{errors.occurred_on}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="notes">Notas</Label>
                            <textarea
                                id="notes"
                                className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                            />
                            {errors.notes && <p className="text-sm text-destructive">{errors.notes}</p>}
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setFormOpen(false)}>
                                Cancelar
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Guardando…' : editing ? 'Guardar cambios' : 'Crear movimiento'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Confirmación de borrado */}
            <Dialog open={deleteTarget !== null} onOpenChange={(open: boolean) => !open && setDeleteTarget(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Eliminar movimiento</DialogTitle>
                        <DialogDescription>
                            ¿Estás seguro de eliminar <strong>{deleteTarget?.concept}</strong>? Esta acción no se
                            puede deshacer.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteTarget(null)}>
                            Cancelar
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            Eliminar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}
