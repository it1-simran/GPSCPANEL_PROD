<?php



namespace App;



use Illuminate\Notifications\Notifiable;

use Illuminate\Foundation\Auth\User as Authenticatable;



class Reseller extends Authenticatable

{

    use Notifiable;



    /**

     * @var string

     */

    protected $guard = 'reseller';



    /**

     * The attributes that are mass assignable.

     *

     * @var array

     */



    protected $fillable = [

        'name', 'mobile', 'email', 'password','LoginPassword','showLoginPassword','today_pings','total_pings','ip','port','logs_interval','sleep_interval','transmission_interval','FOTA','Active_Status','user_type','created_by'

    ];



    /**

     * The attributes that are mass assignable.

     *

     * @var array

     */

    protected $hidden = [

        'password', 'remember_token',

    ];

}

