<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'factura';
    protected $primaryKey = 'idFacturas';
    public $incrementing = false; // El ID es un string generado manualmente
    protected $keyType = 'string';

    protected $fillable = [
        'idFacturas',
        'fechaGeneracion',
        'montoTotal',
        'FK_usuario',
        'FK_estadoCita'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'FK_usuario', 'idUsuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class, 'FK_factura', 'idFacturas');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoCita::class, 'FK_estadoCita', 'idEstado');
    }
}
