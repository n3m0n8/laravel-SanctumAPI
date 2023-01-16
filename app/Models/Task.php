<?php

// in the tutorial he creates an illuminate model that will structure the tasks that are being POST'd via api.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    // We create a protected array $fillable container var that will define what type of values are to be allowed for mass assignment upon creation of a task... the idea being that we create a 'whitelist' of acceptable vars that will have their values created/modified on the tasks table of the DB in any one action while excluding others from being included in this whitelist
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'priority',
    ];
    // another 'integrity/ 2nd form normalisation check' that we deploy here on our task Model template class for whenever a task instance of this model is to be created is to spell out the one-to-many relationship that the user holds in regard to the tasks attached to them (as established in our migration of the tasksp table in our DB using user_id FK)
    public function user(){
        //return $this obbject instance of the task model template class BELONGS TO one or another object instance of the user model template class 
        return $this->belongsTo(User::class);
    }
}
