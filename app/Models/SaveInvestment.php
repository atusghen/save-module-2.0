<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;




class SaveInvestment extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $connection = "";

    public function __construct() {
    }

    protected $table = 'save_investments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "label","municipality_code","wacc",
        "share_municipality","share_bank","mortgage_installment",
        "fee_esco","share_esco","energy_unit_cost",
        "incentives_duration","tep_kwh","tep_value","management_cost",
        "duration_amortization","project_duration","taxes",
        "share_funded","cost_funded","created_at"
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
