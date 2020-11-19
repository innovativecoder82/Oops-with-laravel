<?php
namespace samarnas;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Provider_transaction extends Model
{
    protected $fillable = [ "provider_id","provider_name","provider_type","services","duration","provider_earning","samarnas_deduction","provider_balance","created_at","updated_at" ];
}
