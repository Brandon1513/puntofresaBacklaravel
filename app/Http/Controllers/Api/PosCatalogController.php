<?php

// app/Http/Controllers/Api/PosCatalogController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Bundle;
use App\Models\BundleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\BundlePhoto;

class PosCatalogController extends Controller
{
    public function items(Request $request)
    {
        /*
        |--------------------------------------------------------------
        | 1) Artículos individuales
        |--------------------------------------------------------------
        */
        $items = Item::with([
                'categoria',
                'unidad',
                'photos' => function ($q) {
                    $q->orderByDesc('es_principal')
                      ->orderBy('orden');
                },
            ])
            ->activos()
            ->orderBy('nombre')
            ->get()
            ->map(function (Item $item) {
                $photos = $item->photos->map(function ($photo) {
                    return [
                        'id'            => $photo->id,
                        'url'           => Storage::disk('public')->url($photo->path),
                        'es_principal'  => (bool) $photo->es_principal,
                        'orden'         => (int) $photo->orden,
                    ];
                });

                return [
                    'id'                => $item->id,
                    'tipo'              => 'item',
                    'nombre'            => $item->nombre,
                    'sku'               => $item->sku,
                    'categoria'         => optional($item->categoria)->nombre,
                    'descripcion_corta' => $item->descripcion, // usamos la descripción del item
                    'precio_desde'      => (float) $item->precio_renta_dia,
                    'fotos'             => $photos,

                    // Datos extra para el modal
                    'unidad'            => optional($item->unidad)->nombre,
                    'ubicacion'         => $item->ubicacion,
                    'precio_renta_dia'  => (float) $item->precio_renta_dia,
                    'precio_renta_fin'  => (float) $item->precio_renta_fin,
                    'stock_fisico'      => (int) $item->stock_fisico,
                ];
            });

        /*
        |--------------------------------------------------------------
        | 2) Combos / Bundles
        |--------------------------------------------------------------
        */
        $bundles = Bundle::with([
                    'photos' => function ($q) {
                    $q->orderByDesc('es_principal')
                    ->orderBy('orden');
                },
                'lineas.item.photos' => function ($q) {
                    $q->orderByDesc('es_principal')
                      ->orderBy('orden');
                },
            ])
            ->activos()
            ->vigentes()
            ->orderBy('nombre')
            ->get()
            ->map(function (Bundle $bundle) {
                // Fotos del combo
                $photos = $bundle->photos->map(function (BundlePhoto $photo) {
                    return [
                        'id'           => $photo->id,
                        'url'          => Storage::disk('public')->url($photo->path),
                        'es_principal' => (bool) $photo->es_principal,
                        'orden'        => (int) $photo->orden,
                    ];
                });
                // Ítems que incluye el combo
                $bundleItems = $bundle->lineas->map(function (BundleItem $linea) {
                    $item  = $linea->item;
                    if (!$item) {
                        return null;
                    }

                    $photo = $item->photos->first();

                    return [
                        'id'        => $item->id,
                        'nombre'    => $item->nombre,
                        'sku'       => $item->sku,
                        'cantidad'  => (int) $linea->cantidad,
                        'foto_url'  => $photo
                            ? Storage::disk('public')->url($photo->path)
                            : null,
                    ];
                })->filter(); // quitamos nulos

                // Texto de vigencia bonito
                $vigencia = $this->formatBundleVigencia($bundle);

                return [
                    'id'                => $bundle->id,
                    'tipo'              => 'bundle',
                    'nombre'            => $bundle->nombre,
                    'sku'               => $bundle->sku,
                    'categoria'         => 'Combo',
                    'descripcion_corta' => $bundle->descripcion,
                    'precio_desde'      => (float) $bundle->precio_final,
                    'fotos'             => $photos, // por ahora sin fotos propias del bundle

                    'vigencia'          => $vigencia,
                    'bundle_items'      => $bundleItems->values(),
                ];
            });

        /*
        |--------------------------------------------------------------
        | 3) Unimos catálogo
        |--------------------------------------------------------------
        */
        $catalog = $items->merge($bundles)->values();

        return response()->json($catalog);
    }

    protected function formatBundleVigencia(Bundle $bundle): ?string
    {
        if (!$bundle->vigente_desde && !$bundle->vigente_hasta) {
            return null;
        }

        if ($bundle->vigente_desde && $bundle->vigente_hasta) {
            return $bundle->vigente_desde->format('d/m/Y')
                . ' - '
                . $bundle->vigente_hasta->format('d/m/Y');
        }

        if ($bundle->vigente_desde) {
            return 'Desde ' . $bundle->vigente_desde->format('d/m/Y');
        }

        return 'Hasta ' . $bundle->vigente_hasta->format('d/m/Y');
    }
}
