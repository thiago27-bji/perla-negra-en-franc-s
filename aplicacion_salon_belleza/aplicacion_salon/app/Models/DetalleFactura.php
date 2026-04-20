<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleFactura extends Model
{
    use HasFactory;

    protected $table = 'detalle_factura';
    protected $primaryKey = 'idDetalle';

    protected $fillable = [
        'FK_factura',
        'FK_servicio',
        'cantidad',
        'precioUnitario'
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'FK_factura', 'idFacturas');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'FK_servicio', 'idServicio');
    }
}
