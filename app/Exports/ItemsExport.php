<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected array $filters = [])
    {
    }

    protected function query()
    {
        $q = Item::with(['categoria', 'unidad']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $q->where(function ($qq) use ($search) {
                $qq->where('nombre', 'like', "%{$search}%")
                   ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['categoria_id'])) {
            $q->where('categoria_id', $this->filters['categoria_id']);
        }

        if (($this->filters['estado'] ?? null) === 'activos') {
            $q->where('activo', true);
        } elseif (($this->filters['estado'] ?? null) === 'inactivos') {
            $q->where('activo', false);
        }

        return $q;
    }

    public function collection()
    {
        return $this->query()->orderBy('nombre')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'SKU',
            'Nombre',
            'Categoría',
            'Unidad',
            'Stock físico',
            'Stock mínimo',
            'Precio renta día',
            'Precio renta fin',
            'Activo',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->sku,
            $item->nombre,
            $item->categoria->nombre ?? '',
            $item->unidad->abreviatura ?? '',
            $item->stock_fisico,
            $item->stock_minimo,
            $item->precio_renta_dia,
            $item->precio_renta_fin,
            $item->activo ? 'Sí' : 'No',
        ];
    }
}
