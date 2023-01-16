<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
//use vendor\laravel\sanctum\src\HasApiTokens;

class AuthController extends Controller
{
    // this controller will use the HttpResponses trait to know how to react to particular requests (i.e. wehether they have succeed or failed) and what to do in response...
    use HttpResponses;
    //use HasApiTokens;
    // defining the login method of the authcontroller class:

    public function login(LoginUserRequest $request){
        $request->validated($request->all());
        // here we deploy an auth if conditional check... that is to prevent receiving a 200 ok response when the user has NOT been authenticated when submitting the relevant login info... i.e. we need to check that the inputted credentials are indeed authenticated before providing a 200. So first we deploy an If block to say: we are not recieving auth'd credentials, we will send a 401 user not authorised response
        //note that useing the attempt method held by the auth superclass, we pass the $request http object as arg 1 as an array BUT we also chain an only() method from the InteractsWithInput class that filters out the subset of array data from the  $request object to provide us only those highlight in onlys(args) i.e. only the email and password key-column values interest us for auth purposes.
        //NOTE BE CARWFUL WITH THE ARRAY BRACKETS ENCLOSINGTHE REQUEST OBJECT AS A WHOLE>>> THE only meth should also fall within array brackets since array is the request object response data array while only is only a chained directive method.
        //NOTE, there is a bug with his tutorial where he wraps the request object into an array .... but if I do this I get a 500 SQL column error. If i pass it just like this, then login is succesfful, and using wrong credentials results in the failure error message being returne (401.)
        if(!Auth::attempt($request->only('email','password'))){
            //if !not Auth'd then return fail mesage:
                // the return says return the request object data wrapped into our onw defined error() method, defined in the HttpResponses class... remember that method has three args, 1 is data- in this case it's empty, 2 is message - unauthorised here, and 3 is http signal 401 code. 
            return $this->error('', 'Unauthorised', '401');
        }
        // defining this user container var from basis of User model class... we use the conditional where() method that the User model calss inherits. That method checks for the unique id record, which in this case is the email key-column. we compare that to request object's email key value. 
        $user = User::where('email', $request->email)->first(); // eloquent meth retrieve first hit retrieved from DB the purpose of this method is to reduce effort of parsing through the entire database... (its based on an underlying php algorithms that make sure that parsing through we hit it and quit -iee avoid going through entier dataset.)
        return $this->success([
            // having defined this user container var by the unique id held in the users table of our db , in this case the email address... and since we passed the credentials check earlier, we can now proceeed to call our HttpResponses Trait's success() method. In this case we give that success's arg1 which is the data-concerned arg the user value as one part of encapsulated array of data followed by the session cookie token as whcih is generated using createToken(arg1 of that meth being the 'inne nickname' of the token that we custom defined). We also chain the plain text property to that createToken meth, so that we have it as a string rather than bytecode/hex with whitespaces.
            //arg2 is a null defaulting messa, arg3 is the http header code (i nthis case a 200 is recieved by postman )
            'user' => $user, // this is nested object held as first element of this data array
            'token' => $user->createToken('Api Token for :'.$user->name)->plainTextToken,
        ]);

        //deprecated test to check for response
        // output a 200 ok response in text for postman verification
        //return 'this is my login meth';
    }
    // note here that we deploy the StoreUserRequest request class instance into the arg1 of our register() method... the purpose being that we 'sieve' whatever front-end form inputted keycolumn-value/record-row $request data is incoming into the register() method with this filter/validation's rules as they are defined and held in that StoreUserRequest's rules() method.
    public function register(StoreUserRequest $request){
        //now that we have passed the StoreUserRequest class wrapping over the $requst incoming front-end form data container object, then the container object has a validated()method chained to it. The validated() method is provided by the request object's class i.e. Illuminate\Http\Request. And we pass it the request object again as the array of data to be validated... with a further 'embedded' chain of the all() method, meaning , validate all data on the iconimg request form data object's. NOTE IT HAS TO BE VALIDATED() not VALIDATE()
        $request->validated($request->all());
        //Here, on the basis of validation, we can now create a new user - i.e their form was validated and they can now be added to our database. Just like with the templating of the rules for validation that we did in the StoreUserRequest class, we now template the key-column to value/record-row expectations for the api to grab the incoming http request form data and to intermate it (via Laravel's Eloquent ORM) to the database - in this case a MySQL DB.
        // we assign to the container user var an instance of the User model class using it's create method() the methor takes an arg with an array of data to be 'fitted into' the SQL db: 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            //DONT FORGET TO HASH THE INCOMING PASSWORD RAW DATA!!
            'password' => Hash::make($request->password), 
        ]);
        // in this test 200 output, we put out the response in json'd format... json is the expected format for almost every api (except xml i guess.)
        //return response()->json('Registered successfully');
        // Instead of the above dummy response that we tested with postman, we will now, upon succesfful validation and entry of the request object data into our database, return a success message which will be deployed under the success method held in our HttpResponses Trait class.
        return $this->success([
            //remember that the success() in the httpResponses trait takes three  args. ar1 is data incoming. arg2 is a message. arg3 is status code. So here, in the arg1 of the success () meth that we are calling, we input the data as an array. We are stuffing into this success(arg1) array the incomding request object's form data which has been validated, and 'mirrored' into an instance of a User model template class with all of the relevant data inputted into the SQL database. The $this thus refers to the request object which is holding all relevant (name, email password data). But also note that this success(arg1) array is also being stuffed with a further key-value pair of 'token'. That token's value is simply generated using Laravel Sanctum's createToken method which returns an instance of the \Sanctum\NewAccessToken class in a 256 hash... but we do chain onto this the plaintexttoken property because we want the token returned to use in text string format, not bytestrings or hex. note that the createToken(arg1) is a kind of 'internal message' for sanctum's handlign of auth - it basically creates a name to attach to this token - and a good practice is to 'attach' the token to the freshly created  user - although this wont be unique, but the token will be.
            'user' => $user,
            'token' => $user->createToken('API token for '. $user->name)->plainTextToken,
            //one more point before testing this out on postman is that we need to provide http headers within postman to tell the client what type of data this api is expecting to be sent to it. in this case, the api spceificiation is a dual keypair of under the headers tab:
                //key              value
                //Accept            application/vnd.api+json
                //Content-Type      application/vnd.api+json
                //and of course, if we are not implementing the front end directly but simply undertaking backend-focussed work as is the case here, then we can use postman to 'mock' the frontend form input that fills in the $request object. we do so in the Body tab, form-data sub-tab.
        ]); 
    }
    public function logout(){
    // NOTE - FOR APP AND USER SECURITY IT IS IMPORTANT TO REVOKE ALL LARAVEL SESSION TOKEN COOKIES VALIDITY FOR THE USER'S CURRENT SESSION THIS PREVENTYS A SESSION FIXATION ATTACK IN WHICH AN ATTACKER FEEDS BAD INITIAL SESSION COOKIE THAT THEY OBTAINED FRAUDULENTLY FROM THE SERVER OF THE WEBSITE TO OUR VICTIM USER AND THEN THE USER ENTERS THEIR LOGIN CREDENTIALS - THIS CAN BE DONE VIA (SPEAR)PHISHING EMAIL FOR EXAMPLE... THE USER CONNECTS TO THAT FRAUDULENT SESSION VIA THE LINK (THINKING IT IS THEIR SESSION) BUT IN FACT IT IS THE ATTACKERS 'PRE-COOKED/FIXED' SESSION COOKIE THAT THEY CAN KEEP RENEWING AND USE TO GAIN ACCESS TO THE USERS DATA WITHOUT THE SERVER/WEBSITE PROVIDER BEING ABLE TO TELL THE DIFFERENCE.
        //The below return is a test message for postman, it is deprecated
        //return response()->json('Loggd out');
        // here instead we deploy the actual loggin out. We start with the countermeasure to session fixation, made very easy with laravel because we simply refer to the Auth:: facade class (which inherits and implements the sanctum superclass)'s user() method (meaning the currently session'd logged' user)... then we chain on to that a directive meth currentAccessToken() which fetches the currently granted Laravel session token and then we chain on ->delete() which revokes the token
        // VSC doesn't recognise this sanctum method but it is there and works anytway : const currentAcessToken() = \vendor\Laravel\framework\Sanctum\src\HasApiTokens::currentAccessToken();
        Auth::user()->currentAccessToken()->delete();
        //then we  call out success() meth in the HttpResponse trait class to redirect user upon logout with confimration of loggin out- rememebr that the success meth takes three args (arg1 data, arg2 message, arg3 http status code but here we're doing nothing excpet confirming an action, so no data, no code just msg held in the data array expected in args for success(args)) .
        return \app\Traits\HttpResponses::success([
            'message' => 'You are logged out!',
        ]);
        // NOTE THE currentAccessToken is correctly parsed by the php parser... this undefined method error is only within intelephense for VSC.... SO the session token is correctly revoked... but one bug is that confirmation message isn't put out because the HttpResponses trait class' success() meth is protected so it is not allowing a call from an external class somehow..
    }
}
