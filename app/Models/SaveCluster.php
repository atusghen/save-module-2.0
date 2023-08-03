<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class SaveCluster extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $connection = "";

    public function __construct() {
    }

    protected $table = 'save_clusters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "ha_id","label","technology","lamp_num","device_num","average_device_power","dimmering","created_at"
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
