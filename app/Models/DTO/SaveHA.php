<?php

namespace App\Models\DTO;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Definisce la tabella delle Homogeneous Area, sia TO-BE che ASIS
 */

class SaveHA extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $connection = "";

    public function __construct() {
    }

    protected $table = 'save_has';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "plant_id","label_ha","type_ha","ref_has_is_id_ha", "is_ready","lamp_cost","lamp_disposal","maintenance_interval","panel_cost"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    ];

    protected $dates = ['created_at', 'updated_at'];


    // READ FUNCTIONS




}
