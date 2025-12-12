<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\BundlePhoto;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BundleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only('search', 'estado');

        $query = Bundle::query()
            ->withCount('lineas');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('sku', 'like', "%{$s}%")
                  ->orWhere('nombre', 'like', "%{$s}%");
            });
        }

        switch ($filters['estado'] ?? '') {
            case 'activos':
                $query->where('activo', true);
                break;
            case 'inactivos':
                $query->where('activo', false);
                break;
            case 'vigentes':
                $query->vigentes();
                break;
        }

        $bundles = $query
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('bundles.index', compact('bundles', 'filters'));
    }

    public function create()
    {
        $items = Item::activos()
            ->orderBy('nombre')
            ->get();

        $bundle = new Bundle();

        return view('bundles.create', compact('bundle', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku'                       => 'required|string|max:50|unique:bundles,sku',
            'nombre'                    => 'required|string|max:200',
            'descripcion'               => 'nullable|string',
            'usar_precio_personalizado' => 'nullable|boolean',
            'precio_personalizado'      => 'nullable|numeric|min:0',
            'descuento_porcentaje'      => 'nullable|integer|min:0|max:100',
            'activo'                    => 'nullable|boolean',
            'vigente_desde'            => 'nullable|date',
            'vigente_hasta'            => 'nullable|date|after_or_equal:vigente_desde',

            'line_items'   => 'required|array|min:1',
            'line_items.*' => 'required|exists:items,id',
            'line_qty'     => 'required|array',
            'line_qty.*'   => 'required|integer|min:1',

            'line_price'   => 'nullable|array',
            'line_price.*' => 'nullable|numeric|min:0',

            // 👇 nuevas fotos del paquete
            'photos'      => 'nullable|array',
            'photos.*'    => 'image|max:4096', // 4MB
        ]);

        $lineItems = $request->input('line_items', []);
        $lineQty   = $request->input('line_qty', []);
        $linePrice = $request->input('line_price', []);

        if (count($lineItems) !== count($lineQty)) {
            return back()
                ->withInput()
                ->withErrors(['line_items' => 'Las líneas del paquete no son válidas.']);
        }

        DB::transaction(function () use ($request, $lineItems, $lineQty, $linePrice) {
            $bundleData = [
                'sku'                       => $request->sku,
                'nombre'                    => $request->nombre,
                'descripcion'               => $request->descripcion,
                'usar_precio_personalizado' => $request->boolean('usar_precio_personalizado'),
                'precio_personalizado'      => $request->precio_personalizado,
                'descuento_porcentaje'      => $request->input('descuento_porcentaje', 0),
                'activo'                    => $request->boolean('activo', true),
                'vigente_desde'            => $request->vigente_desde ?: null,
                'vigente_hasta'            => $request->vigente_hasta ?: null,
            ];

            /** @var \App\Models\Bundle $bundle */
            $bundle = Bundle::create($bundleData);

            foreach ($lineItems as $idx => $itemId) {
                $cantidad = (int) ($lineQty[$idx] ?? 0);
                if ($cantidad <= 0) {
                    continue;
                }

                /** @var \App\Models\Item|null $item */
                $item = Item::find($itemId);
                if (!$item) {
                    continue;
                }

                $precioBase   = $item->precio_renta_dia ?? 0;
                $precioCustom = $linePrice[$idx] ?? null;
                if ($precioCustom === '' || $precioCustom === null) {
                    $precioCustom = null;
                }

                $bundle->lineas()->create([
                    'item_id'                 => $itemId,
                    'cantidad'                => $cantidad,
                    'precio_unitario_paquete' => $precioCustom,
                    'precio_unitario_cache'   => $precioBase,
                ]);
            }

            // 👇 guardar fotos nuevas (si las hay)
            $this->savePhotos($bundle, $request->file('photos', []));
        });

        return redirect()
            ->route('bundles.index')
            ->with('ok', 'Paquete creado correctamente.');
    }

    public function edit(Bundle $bundle)
    {
        $bundle->load(['lineas.item', 'photos']);

        $items = Item::activos()
            ->orderBy('nombre')
            ->get();

        return view('bundles.edit', compact('bundle', 'items'));
    }

    public function update(Request $request, Bundle $bundle)
    {
        $request->validate([
            'sku'                       => 'required|string|max:50|unique:bundles,sku,' . $bundle->id,
            'nombre'                    => 'required|string|max:200',
            'descripcion'               => 'nullable|string',
            'usar_precio_personalizado' => 'nullable|boolean',
            'precio_personalizado'      => 'nullable|numeric|min:0',
            'descuento_porcentaje'      => 'nullable|integer|min:0|max:100',
            'activo'                    => 'nullable|boolean',
            'vigente_desde'            => 'nullable|date',
            'vigente_hasta'            => 'nullable|date|after_or_equal:vigente_desde',

            'line_items'   => 'required|array|min:1',
            'line_items.*' => 'required|exists:items,id',
            'line_qty'     => 'required|array',
            'line_qty.*'   => 'required|integer|min:1',

            'line_price'   => 'nullable|array',
            'line_price.*' => 'nullable|numeric|min:0',

            // nuevas fotos (se añaden a las existentes)
            'photos'      => 'nullable|array',
            'photos.*'    => 'image|max:4096',
        ]);

        $lineItems = $request->input('line_items', []);
        $lineQty   = $request->input('line_qty', []);
        $linePrice = $request->input('line_price', []);

        if (count($lineItems) !== count($lineQty)) {
            return back()
                ->withInput()
                ->withErrors(['line_items' => 'Las líneas del paquete no son válidas.']);
        }

        DB::transaction(function () use ($request, $bundle, $lineItems, $lineQty, $linePrice) {
            $bundleData = [
                'sku'                       => $request->sku,
                'nombre'                    => $request->nombre,
                'descripcion'               => $request->descripcion,
                'usar_precio_personalizado' => $request->boolean('usar_precio_personalizado'),
                'precio_personalizado'      => $request->precio_personalizado,
                'descuento_porcentaje'      => $request->input('descuento_porcentaje', 0),
                'activo'                    => $request->boolean('activo', true),
                'vigente_desde'            => $request->vigente_desde ?: null,
                'vigente_hasta'            => $request->vigente_hasta ?: null,
            ];

            $bundle->update($bundleData);

            // líneas
            $bundle->lineas()->delete();

            foreach ($lineItems as $idx => $itemId) {
                $cantidad = (int) ($lineQty[$idx] ?? 0);
                if ($cantidad <= 0) {
                    continue;
                }

                $item = Item::find($itemId);
                if (!$item) {
                    continue;
                }

                $precioBase   = $item->precio_renta_dia ?? 0;
                $precioCustom = $linePrice[$idx] ?? null;
                if ($precioCustom === '' || $precioCustom === null) {
                    $precioCustom = null;
                }

                $bundle->lineas()->create([
                    'item_id'                 => $itemId,
                    'cantidad'                => $cantidad,
                    'precio_unitario_paquete' => $precioCustom,
                    'precio_unitario_cache'   => $precioBase,
                ]);
            }

            // 👇 añadir nuevas fotos si se suben
            $this->savePhotos($bundle, $request->file('photos', []));
        });

        return redirect()
            ->route('bundles.index')
            ->with('ok', 'Paquete actualizado correctamente.');
    }

    public function toggle(Bundle $bundle)
    {
        $bundle->activo = !$bundle->activo;
        $bundle->save();

        return back()->with('ok', 'Paquete actualizado.');
    }

    /**
     * Guarda un conjunto de archivos como fotos del bundle.
     * La primera foto del bundle será marcada como principal.
     */
    protected function savePhotos(Bundle $bundle, array $files): void
    {
        if (empty($files)) {
            return;
        }

        // orden de inicio después de las fotos existentes
        $lastOrder = (int) ($bundle->photos()->max('orden') ?? 0);
        $hasPrincipal = $bundle->photos()->where('es_principal', true)->exists();

        foreach ($files as $idx => $file) {
            if (!$file) {
                continue;
            }

            $path = $file->store('bundles', 'public');

            $lastOrder++;

            $isPrincipal = false;
            if (!$hasPrincipal && $idx === 0) {
                // si el bundle no tenía principal, la primera nueva foto lo es
                $isPrincipal = true;
                $hasPrincipal = true;
            }

            $bundle->photos()->create([
                'path'         => $path,
                'es_principal' => $isPrincipal,
                'orden'        => $lastOrder,
            ]);
        }
    }
}
