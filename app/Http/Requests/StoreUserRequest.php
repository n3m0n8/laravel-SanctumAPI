<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * This request class is 
     * 
     * Determine if the user is authorized to make this request. This is bascially a validation of a request being made. We generate this template using php artisan make:request RequestName... The point of the request class is to absorb a user's request submitted in a form (hence why the superclass here is FormRequest). The request is validated as against certain rules, which we can create custom-based ones in the rules() function below: 
     *
     * @return bool
     */
    public function authorize()
    {
        // note if this authorization remains under a false boolean value, then the http request will return a 403 Forbiidenn response saying it is unauthorised... if we want this custom template StoreUserRequest Class to validate the incoming $request form data as is done in the rules() meth below then we must set this to true. 
        //basically a true value here means that whoever is sending the request to post form data for validation adn input into db has authorisation (i.e. it is linked to an auth of a user (for example the user is logged in or has a valid session cookie))
        //return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //here we customise the rules for validating an auth request coming in via the form. We use key-value pairs of the database key-column names that are to be filled in by the form incoming via front end toward this backend api handler. The values will be rules that validate the input of each of these key-column fields being entered in on the front-end form so as to avoid an attacks like SQL injections or to maintain our 3 normalisation forms relating to the integrity and uniqueness of our database set.
            //Note that these validations can be tested without interaction with the front by using postman - under the body tab the form-data and x-www-form sub-tabs provide this functionality.
            // so for first key we set expectations that the incoming value arriving to this api handler for submission into the database is not null (required), a string type, and max 255 chars (i.e VARCHAR255 on an SQL DB for instance).
            'name' => ['required', 'string', 'max:255'],
            //email is same but we add the extra condition that it has to be unique on the users table to avoid duplication of records (second form of normalisatio of db)
            'email' => ['required', 'string', 'max:255', 'unique:users'],
            //finally the password with confirmation requiremenet which means there will be two form inputs that will need to be filled in and matching on another (in the tutorial he hasn't put a minimum but i've added it in here a minim of 8 chars and string type). Note also that in the tutorial he didn't import the illuminate\validation\Password superclass bue i hava above and now the deployment is simply Password clss:: default password validation(method).
            'password' => ['required', 'confirmed', 'string', 'min:8', Password::defaults()],
            //once this is all set, we deploy the validation rules check into our register method, defined and held within the AuthController class.
        ];
    }
}
