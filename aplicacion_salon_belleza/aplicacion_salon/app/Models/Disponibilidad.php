<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Disponibilidad extends Model
{
    use HasFactory;

    protected $table = 'disponibilidad';
    protected $primaryKey = 'idDisponibilidad';

    protected $fillable = [
        'FK_personal',
        'diaSemana',
        'horaInicio',
        'horaFin',
    ];

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'FK_personal', 'idPersonal');
    }
}