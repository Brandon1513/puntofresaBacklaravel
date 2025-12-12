<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventOrder extends Model
{
    use HasFactory;

    public const STATUS_BORRADOR    = 'borrador';
    public const STATUS_COTIZACION  = 'cotizacion';
    public const STATUS_CONFIRMADA  = 'confirmada';
    public const STATUS_PREPARACION = 'preparacion';
    public const STATUS_SALIDA      = 'salida';
    public const STATUS_REGRESO     = 'regreso';
    public const STATUS_CIERRE      = 'cierre';

    protected $table = 'event_orders';

    protected $fillable = [
        'folio',
        'cliente_id',
        'cliente_nombre',
        'cliente_email',
        'cliente_telefono',
        'contacto_nombre',
        'contacto_telefono',
        'lugar',
        'direccion',
        'fecha_entrega',
        'fecha_inicio',
        'fecha_recoleccion',
        'notas',
        'estatus',
        'subtotal',
        'impuestos',
        'total',
        'pagado_total',
        'saldo_pendiente',
    ];

    protected $casts = [
        'fecha_entrega'     => 'datetime',
        'fecha_inicio'      => 'datetime',
        'fecha_recoleccion' => 'datetime',
        'subtotal'          => 'decimal:2',
        'impuestos'         => 'decimal:2',
        'total'             => 'decimal:2',
        'pagado_total'      => 'decimal:2',
        'saldo_pendiente'   => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function lineas()
    {
        return $this->hasMany(EventOrderLine::class, 'event_order_id');
    }

    public function pagos()
    {
        return $this->hasMany(EventPayment::class);
    }

    public function itemLoans()
    {
        return $this->hasMany(EventOrderItemLoan::class, 'event_order_id');
    }

    public static function estatusDisponibles(): array
    {
        return [
            self::STATUS_BORRADOR    => 'Borrador',
            self::STATUS_COTIZACION  => 'Cotización',
            self::STATUS_CONFIRMADA  => 'Confirmada (anticipo)',
            self::STATUS_PREPARACION => 'Preparación',
            self::STATUS_SALIDA      => 'Salida',
            self::STATUS_REGRESO     => 'Regreso',
            self::STATUS_CIERRE      => 'Cierre',
        ];
    }
    
    /**
     * Indica si la orden está bloqueada para edición de líneas/ítems.
     */
    public function isLockedForItems(): bool
    {
        return in_array($this->estatus, [
            self::STATUS_SALIDA,
            self::STATUS_REGRESO,
            self::STATUS_CIERRE,
        ], true);
    }

    /**
     * Indica si se pueden editar líneas (agregar/quitar ítems) en el POS.
     */
    public function canEditLines(): bool
    {
        return ! $this->isLockedForItems();
    }

    public function getEstatusLabelAttribute(): string
    {
        return self::estatusDisponibles()[$this->estatus] ?? ucfirst($this->estatus);
    }

    /**
     * Recalcula subtotal, impuestos y total
     * usando cantidad * precio_unitario e impuesto_porcentaje
     * para TODAS las líneas (item, bundle, extra).
     */
    public function recalcularTotales(): void
{
    // 👇 Muy importante: forzar recarga de líneas
    // para incluir las líneas EXTRA de reposición recién creadas
    $this->unsetRelation('lineas');
    $this->load('lineas');

    $subtotal  = 0.0;
    $impuestos = 0.0;

    foreach ($this->lineas as $linea) {
        $precio   = (float) ($linea->precio_unitario ?? 0);
        $cantidad = (int) $linea->cantidad;
        $pctImp   = (float) ($linea->impuesto_porcentaje ?? 0);

        $importe       = $precio * $cantidad;
        $impuestoLinea = $importe * ($pctImp / 100);

        $subtotal  += $importe;
        $impuestos += $impuestoLinea;
    }

    $this->subtotal  = $subtotal;
    $this->impuestos = $impuestos;
    $this->total     = $subtotal + $impuestos;

    $this->save();
}



    /**
     * Accessor por si saldo_pendiente viene null.
     */
    public function getSaldoPendienteAttribute($value)
    {
        if (!is_null($value)) {
            return $value;
        }

        $pagado = $this->pagos()->sum('monto');
        return max(0, ($this->total ?? 0) - $pagado);
    }

    /**
     * Recalcula pagado_total y saldo_pendiente en base a los pagos.
     */
    public function recalcularPagos(): void
    {
        $pagado = (float) $this->pagos()->sum('monto');

        $this->pagado_total    = $pagado;
        $this->saldo_pendiente = max(0, ($this->total ?? 0) - $pagado);

        $this->save();
    }
}
