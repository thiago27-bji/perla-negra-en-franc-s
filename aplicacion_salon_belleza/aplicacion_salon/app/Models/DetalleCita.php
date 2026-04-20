<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleCita extends Model
{
    use HasFactory;

    protected $table = 'detalle_cita';
    protected $primaryKey = 'idDetalle';

    protected $fillable = [
        'FK_cita',
        'FK_servicio',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'FK_cita', 'idCita');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'FK_servicio', 'idServicio');
    }
}
