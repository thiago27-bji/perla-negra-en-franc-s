<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    protected $primaryKey = 'idPersonal';
    public $incrementing = false;

    protected $fillable = [
        'idPersonal',
        'nombre',
        'apellido',
        'especialidad',
        'estadoDisponible'
    ];

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'personal_servicio', 'FK_personal', 'FK_servicio');
    }

    public function disponibilidades()
    {
        return $this->hasMany(Disponibilidad::class, 'FK_personal', 'idPersonal');
    }
}