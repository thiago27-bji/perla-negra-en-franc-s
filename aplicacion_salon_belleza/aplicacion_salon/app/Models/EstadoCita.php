<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstadoCita extends Model
{
    use HasFactory;

    protected $table = 'estado_cita';
    protected $primaryKey = 'idEstado';

    protected $fillable = [
        'nombre',
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class, 'FK_estadoCita', 'idEstado');
    }
}
