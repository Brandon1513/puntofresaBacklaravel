<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategoria;
use App\Models\Unidad;
use Illuminate\Http\Request;
use App\Models\ItemPhoto;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemController extends Controller
{
     public function index(Request $request)
    {
        $categorias = ItemCategoria::orderBy('nombre')->get();

        $filters = [
            'search'       => $request->input('search'),
            'categoria_id' => $request->input('categoria_id'),
            'estado'       => $request->input('estado'), // '', 'activos', 'inactivos'
        ];

        $itemsQuery = Item::with(['categoria', 'unidad']);

        // Buscar por nombre o SKU
        if ($filters['search']) {
            $search = $filters['search'];
            $itemsQuery->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($filters['categoria_id']) {
            $itemsQuery->where('categoria_id', $filters['categoria_id']);
        }

        // Filtro por activos / inactivos
        if ($filters['estado'] === 'activos') {
            $itemsQuery->where('activo', true);
        } elseif ($filters['estado'] === 'inactivos') {
            $itemsQuery->where('activo', false);
        }

        $items = $itemsQuery
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString(); // conserva filtros en paginación

        return view('items.index', compact('items', 'categorias', 'filters'));
    }
    public function create()
    {
        $categorias = ItemCategoria::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $unidades = Unidad::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('items.create', compact('categorias', 'unidades'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'sku'               => 'required|string|max:255|unique:items,sku',
        'nombre'            => 'required|string|max:255',
        'categoria_id'      => 'required|exists:item_categorias,id',
        'unidad_id'         => 'required|exists:unidades,id',
        'precio_renta_dia'  => 'nullable|numeric',
        'precio_renta_fin'  => 'nullable|numeric',
        'costo_promedio'    => 'nullable|numeric',
        'costo_reposicion'  => 'nullable|numeric',
        'stock_fisico'      => 'nullable|integer|min:0',
        'stock_minimo'      => 'nullable|integer|min:0',
        'ubicacion'         => 'nullable|string|max:255',
        'activo'            => 'boolean',
        'tags'              => 'nullable|string|max:255',
        'descripcion'       => 'nullable|string',
        'photos.*'          => 'image|max:2048',   // fotos múltiples
    ]);

    $data['stock_fisico'] = $data['stock_fisico'] ?? 0;
    $data['stock_minimo'] = $data['stock_minimo'] ?? 0;
    $data['activo']       = $request->has('activo');

    // 1) Crear el ítem
    $item = Item::create($data);

    // 2) Generar QR por ítem (si quieres manejarlo automático)
    $item->qr_code = 'ITM-' . str_pad($item->id, 6, '0', STR_PAD_LEFT);
    $item->save();

    // 3) Guardar fotos
    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $index => $file) {
            $path = $file->store('items', 'public'); // storage/app/public/items

            ItemPhoto::create([
                'item_id'      => $item->id,
                'path'         => $path,
                'es_principal' => $index === 0,
                'orden'        => $index + 1,
            ]);
        }
    }

    return redirect()
        ->route('items.index')
        ->with('ok', 'Ítem creado correctamente.');
}

    public function edit(Item $item)
    {
        $categorias = ItemCategoria::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $unidades = Unidad::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('items.edit', compact('item', 'categorias', 'unidades'));
    }

    public function update(Request $request, Item $item)
{
    $data = $request->validate([
        'sku'               => ['required', 'string', 'max:50', 'unique:items,sku,' . $item->id],
        'nombre'            => ['required', 'string', 'max:200'],
        'categoria_id'      => ['required', 'exists:item_categorias,id'],
        'unidad_id'         => ['required', 'exists:unidades,id'],
        'precio_renta_dia'  => ['nullable', 'numeric', 'min:0'],
        'precio_renta_fin'  => ['nullable', 'numeric', 'min:0'],
        'costo_promedio'    => ['nullable', 'numeric', 'min:0'],
        'costo_reposicion'  => ['nullable', 'numeric', 'min:0'],
        'stock_fisico'      => ['nullable', 'integer', 'min:0'],
        'stock_minimo'      => ['nullable', 'integer', 'min:0'],
        'ubicacion'         => ['nullable', 'string', 'max:200'],
        'tags'              => ['nullable', 'string', 'max:255'],
        'descripcion'       => ['nullable', 'string'],
        'activo'            => ['nullable', 'boolean'],
        'photos.*'          => ['image', 'max:2048'],
    ]);

    $data['stock_fisico'] = $data['stock_fisico'] ?? 0;
    $data['stock_minimo'] = $data['stock_minimo'] ?? 0;
    $data['activo']       = $request->has('activo');

    // 1) Actualizar datos del ítem
    $item->update($data);

    // 2) Si no tenía QR aún, lo generamos
    if (!$item->qr_code) {
        $item->qr_code = 'ITM-' . str_pad($item->id, 6, '0', STR_PAD_LEFT);
        $item->save();
    }

    // 3) Fotos nuevas (no borramos las existentes por ahora)
    if ($request->hasFile('photos')) {
        $ordenBase = $item->photos()->max('orden') ?? 0;

        foreach ($request->file('photos') as $i => $file) {
            $path = $file->store('items', 'public');

            ItemPhoto::create([
                'item_id'      => $item->id,
                'path'         => $path,
                'es_principal' => false,
                'orden'        => $ordenBase + $i + 1,
            ]);
        }
    }

    return redirect()
        ->route('items.index')
        ->with('ok', 'Ítem actualizado correctamente.');
}

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('ok', 'Ítem eliminado correctamente.');
    }
    public function show(Item $item)
{
    // Cargamos relaciones para evitar N+1
    $item->load(['categoria', 'unidad', 'photos']);

    return view('items.show', compact('item'));
}
public function downloadQr(Item $item)
{
    $qrText = $item->qr_code ?: ('ITM-' . str_pad($item->id, 6, '0', STR_PAD_LEFT));

    $png = QrCode::format('png')
        ->size(600)
        ->margin(1)
        ->generate($qrText);

    $fileName = 'qr-item-'.$item->id.'.png';

    return response($png)
        ->header('Content-Type', 'image/png')
        ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
}
public function export(Request $request)
{
    $filters = $request->only(['search', 'categoria_id', 'estado']);

    $fileName = 'items_'.now()->format('Ymd_His').'.xlsx';

    return Excel::download(new ItemsExport($filters), $fileName);
}
public function showImportForm()
{
    return view('items.import');
}

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv',
    ]);

    $file = $request->file('file');

    $spreadsheet = IOFactory::load($file->getRealPath());
    $sheet       = $spreadsheet->getActiveSheet();
    $rows        = $sheet->toArray(null, true, true, true);

    if (count($rows) < 2) {
        return back()
            ->with('error', 'El archivo no contiene filas de datos.')
            ->withInput();
    }

    // ==========================
    // 1) Encabezados normalizados
    // ==========================
    $headerRow = array_shift($rows); // primera fila
    $header    = [];

    foreach ($headerRow as $col => $value) {
        $header[$col] = strtolower(trim((string) $value));
    }

    $expected = [
        'sku',
        'nombre',
        'categoria',
        'unidad',
        'precio_renta_dia',
        'precio_renta_fin',
        'costo_promedio',
        'costo_reposicion',
        'stock_fisico',
        'stock_minimo',
        'ubicacion',
        'activo',
        'tags',
        'descripcion',
    ];

    $present = array_values($header);
    $missing = array_diff($expected, $present);

    if (!empty($missing)) {
        return back()
            ->with('error', 'Faltan las siguientes columnas en el encabezado: ' . implode(', ', $missing))
            ->withInput();
    }

    // Mapeo nombre_columna -> letra de columna
    $map = [];
    foreach ($header as $col => $name) {
        if (in_array($name, $expected, true)) {
            $map[$name] = $col; // ejemplo: 'sku' => 'A'
        }
    }

    DB::beginTransaction();

    try {
        $insertados = 0;

        foreach ($rows as $row) {
            // ==========================
            // 2) Leemos columnas de la fila
            // ==========================
            $sku = trim((string) ($row[$map['sku']] ?? ''));

            // si no hay SKU, saltamos la fila
            if ($sku === '') {
                continue;
            }

            $nombre = trim((string) ($row[$map['nombre']] ?? ''));

            $categoriaNombre = trim((string) ($row[$map['categoria']] ?? ''));
            $unidadNombre    = trim((string) ($row[$map['unidad']] ?? ''));

            // ==========================
            // 3) Resolver IDs de categoría/unidad
            // ==========================
            $categoriaId = null;
            if ($categoriaNombre !== '') {
                $categoria = ItemCategoria::firstOrCreate(
                    ['nombre' => $categoriaNombre],
                    ['activo' => 1]
                );
                $categoriaId = $categoria->id;
            }

            $unidadId = null;
            if ($unidadNombre !== '') {
                $unidad = Unidad::firstOrCreate(
                    ['nombre' => $unidadNombre]
                );
                $unidadId = $unidad->id;
            }

            $precioRentaDia  = (float) ($row[$map['precio_renta_dia']]  ?? 0);
            $precioRentaFin  = (float) ($row[$map['precio_renta_fin']]  ?? 0);
            $costoPromedio   = (float) ($row[$map['costo_promedio']]     ?? 0);
            $costoReposicion = (float) ($row[$map['costo_reposicion']]   ?? 0);
            $stockFisico     = (int)   ($row[$map['stock_fisico']]       ?? 0);
            $stockMinimo     = (int)   ($row[$map['stock_minimo']]       ?? 0);

            $ubicacion   = trim((string) ($row[$map['ubicacion']] ?? ''));
            $activoRaw   = strtolower(trim((string) ($row[$map['activo']] ?? '1')));
            $tags        = trim((string) ($row[$map['tags']] ?? ''));
            $descripcion = trim((string) ($row[$map['descripcion']] ?? ''));

            $activo = in_array($activoRaw, ['1', 'si', 'sí', 'true', 'activo'], true) ? 1 : 0;

            // ==========================
            // 4) Crear / actualizar item por SKU
            // ==========================
            Item::updateOrCreate(
                ['sku' => $sku], // clave para buscar
                [
                    'nombre'           => $nombre,
                    'categoria_id'     => $categoriaId,
                    'unidad_id'        => $unidadId,
                    'precio_renta_dia' => $precioRentaDia,
                    'precio_renta_fin' => $precioRentaFin,
                    'costo_promedio'   => $costoPromedio,
                    'costo_reposicion' => $costoReposicion,
                    'stock_fisico'     => $stockFisico,
                    'stock_minimo'     => $stockMinimo,
                    'ubicacion'        => $ubicacion,
                    'activo'           => $activo,
                    'tags'             => $tags,
                    'descripcion'      => $descripcion,
                ]
            );

            $insertados++;
        }

        DB::commit();

        if ($insertados === 0) {
            return back()
                ->with('error', 'No se insertó ni actualizó ningún ítem. Revisa que las filas tengan SKU y datos válidos.')
                ->withInput();
        }

        return redirect()
            ->route('items.index')
            ->with('ok', "Importación completada. Ítems creados/actualizados: {$insertados}.");
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()
            ->with('error', 'Error al importar: ' . $e->getMessage())
            ->withInput();
    }
}

public function downloadImportTemplate()
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Items');

    // Encabezados (los mismos que usas en el import)
    $headers = [
        'A1' => 'sku',
        'B1' => 'nombre',
        'C1' => 'categoria',
        'D1' => 'unidad',
        'E1' => 'precio_renta_dia',
        'F1' => 'precio_renta_fin',
        'G1' => 'costo_promedio',
        'H1' => 'costo_reposicion',
        'I1' => 'stock_fisico',
        'J1' => 'stock_minimo',
        'K1' => 'ubicacion',
        'L1' => 'activo',
        'M1' => 'tags',
        'N1' => 'descripcion',
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    // Fila de ejemplo opcional
    $sheet->fromArray([
        [
            'S-001',
            'Silla Tiffany blanca',
            'Sillas',
            'Pieza',
            200,        // precio_renta_dia
            250,        // precio_renta_fin
            200,        // costo_promedio
            300,        // costo_reposicion
            100,        // stock_fisico
            20,         // stock_minimo
            'Punto Fresa', // ubicacion
            1,          // activo
            'boda,evento', // tags
            'Ejemplo de descripción', // descripcion
        ]
    ], null, 'A2');

    $writer   = new Xlsx($spreadsheet);
    $fileName = 'plantilla_items.xlsx';

    return new StreamedResponse(function () use ($writer) {
        $writer->save('php://output');
    }, 200, [
        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment;filename="'.$fileName.'"',
        'Cache-Control'       => 'max-age=0',
    ]);
}


}
