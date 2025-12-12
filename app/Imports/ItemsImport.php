<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\ItemCategoria;
use App\Models\Unidad;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Evitar filas vacías
            if (!isset($row['sku']) || trim($row['sku']) === '') {
                continue;
            }

            // Normalizar valores
            $sku        = trim($row['sku']);
            $nombre     = trim($row['nombre'] ?? '');
            if ($nombre === '') {
                // Sin nombre no lo doy de alta
                continue;
            }

            // Categoría (crea si no existe)
            $categoriaId = null;
            if (!empty($row['categoria'])) {
                $catName = trim($row['categoria']);
                $categoria = ItemCategoria::firstOrCreate(
                    ['nombre' => $catName],
                    ['activo' => 1]
                );
                $categoriaId = $categoria->id;
            }

            // Unidad (solo asigna si existe)
            $unidadId = null;
            if (!empty($row['unidad'])) {
                $unidad = Unidad::where('nombre', trim($row['unidad']))->first();
                if ($unidad) {
                    $unidadId = $unidad->id;
                }
            }

            // Activo (1/0, si/no)
            $activoRaw = strtolower((string) ($row['activo'] ?? '1'));
            $activo = in_array($activoRaw, ['1', 'si', 'sí', 'true'], true) ? 1 : 0;

            // Crear o actualizar por SKU
            Item::updateOrCreate(
                ['sku' => $sku],
                [
                    'nombre'          => $nombre,
                    'categoria_id'    => $categoriaId,
                    'unidad_id'       => $unidadId,
                    'precio_renta_dia'=> $row['precio_renta_dia'] ?? 0,
                    'precio_renta_fin'=> $row['precio_renta_fin'] ?? 0,
                    'costo_promedio'  => $row['costo_promedio'] ?? 0,
                    'costo_reposicion'=> $row['costo_reposicion'] ?? 0,
                    'stock_fisico'    => $row['stock_fisico'] ?? 0,
                    'stock_minimo'    => $row['stock_minimo'] ?? 0,
                    'ubicacion'       => $row['ubicacion'] ?? null,
                    'activo'          => $activo,
                    'tags'            => $row['tags'] ?? null,
                    'descripcion'     => $row['descripcion'] ?? null,
                ]
            );
        }
    }
}
