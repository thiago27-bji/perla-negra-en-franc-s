<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'cita';
    protected $primaryKey = 'idCita';

    protected $fillable = [
        'FK_usuario',
        'FK_personal',
        'fechaCita',
        'FK_estadoCita',
        'FK_factura',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'FK_usuario', 'idUsuario');
    }

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'FK_personal', 'idPersonal');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoCita::class, 'FK_estadoCita', 'idEstado');
    }

    public function detalle()
    {
        return $this->hasOne(DetalleCita::class, 'FK_cita', 'idCita');
    }
}
