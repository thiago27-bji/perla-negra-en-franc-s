<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicio';
    protected $primaryKey = 'idServicio';

    protected $fillable = [
        'nombresServicio',
        'descripcion',
        'duracionMinuto',
        'precio',
        'imagen'
    ];

    public function personales()
    {
        return $this->belongsToMany(Personal::class, 'personal_servicio', 'FK_servicio', 'FK_personal');
    }
}