<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {// set to true for auth
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
            //here we set two key/fields. email vaildates a proper email format. no need for string validation here since email validation is in place.
            'email' => ['required', 'email'],
            //min 8 chars just like the storeUserRequest
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
