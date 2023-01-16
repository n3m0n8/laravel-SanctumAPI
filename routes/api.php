<?php
// REMEMBER THAT WE DEPLOY ROUTES TO API.php when dealing with BACKEND... web.php when dealing with frontend./
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TasksController;

/// NOTE WE CRWATE A DIVISION BETWEEN PUBLIC AND PROTECTED ROUTES - so as to isolate those that should be available vs those that are not... the obvious point here is to avoid any attacker/user being able to simply try out different routes and then get to a section of the website which should be protected/hidden behind authentication checks

//BACKEND ONLY ROUTES/DEFAULT SANCTUM PACKAGE ROUTE: 
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// PUBLIC ROUTES
// note we skip get because this tutorial/project is focussing on backend only ...
// We create a post router, the purpose of which is to create the endpoint (arg1) which is the login.  arge 2 holds an array with the authConroller class on the left side with that controlle class' login method on the right.
Route::post('/login', [AuthController::class, 'login']);
// note that this post route is tested in postman by create a route under the title 'login'. however, using an endpoint on postman of http://l27.0.0.1:8000/login gives a 404... we need the api prefixe, found in th app/Providers/RouteServicesProvider's api method. that defines the prefix as /api although we can customise to  /api/v3 for example.

// here we defined the registration route for users to register.
route::post('/register', [AuthController::class, 'register']);

// PROTECTED ROUTES

// NOTE the protected routes cannot be simply listed here as they are in public routes... the reason is that simply demarcating the public vs protected routes via comments doesn't do anything in code. In the default/backend only routes section at the top of this namespace, we find a auth middlewware route which was generated when installing the Sanctum package... we now need to create an inbuilt group-type route which is defined in the Routes Facade class to allow multiple routes defined within one meta-level route (in this case a group route method which will pass arg1 as the array key-value pair in which the key is simply title middleware and the value is itself a nested array holding the above defined auth::sanctum middleware backend only route. That arg1 auth:sanctum backend middleware route basically itself has an arg1 which points to auth:sanctum

Route::group(['middleware' => ['auth:sanctum']], function (){
    // there are two levels of arrays. The first level of abstract array is the one held by http/kernel superclass in its protected routeMiddlewar array container variable. Among the key-value listed routes held in this http/kernel superclass we find :  'auth' => \App\Http\Middleware\Authenticate::class, ...
    //so basically it points to a second level of abstract supercalass, in this case the http/Middleware/Authenticate class which holds a function that tests for the presence of a laravel session cookie.. if the test fails, it redirects to the (public section above) 'login' /login route
    // we define our tasks-related route here:
    //resource meth deploys a set of inbuilt default routing parameters.
    // note that arg1 takes the new base resource we want to create (in this case /tasks/ whatever we go to). And arg2 takes the ResourceController template class  in this case the TasksController class we generated.
    //But also note that in the backend section at the top of this namespace, we are also chaining an additional get() directive method which is taking arg1 as the particular instance of the user model class ???held in the /user pathway??? then arg2 is a function which grabs the http request object instance then chains to that a http/Request class's user() method which tests if that particular instance of the user model (i.e. whichever particular user is acessing attempting to acess the /user resource at that time) is indeed verified/accessible iun our database record.
    //We will undertake a test of the tasks route when it has been veiled behind our protected sanctum auth middleware group route. to do so, we need to define test output response within the TasksController index() method which basically indexes all relevant routes and in this case we are making a mock test route to check that our laravel sanctum session token is indeed being request on this element of our group meta-routes array.  
    route::resource('/tasks', TasksController::class); 
    // logout route is also within the protected group route... note that the authenticate superclass which is what is pointed to by the http/kernel superclass in its array listing of activated middleware routes outlines a conditional test to check if someone has an authetication laravel session cookie for them to be ableto move forward twward the named 'logout' route, which takes them to this /logout path. That is why IT IS IMPORTANT TO MAINTAIN LARAVEL'S NAMING CONVENTIONS FOR ROUTES- BECAUSE WE KEEP POINT MULTIPLE LEVELS UP IN ABSTRACTION/SUPERCLASS, FROm THIS API ROUTES CLASS THROUGH THE HTTP/KERNEL SUPERCLASS WHICH LISTS THE 'BOOKED IN' MIDDLEWARE STRATEGIES ONTO THE AUTHENTICATE SUPERCLASS THAT DEPLOYES THE expectedJson() test... if the test passes it returns route('login')... if we name that login route (which was defined up above in the public routes since it must be accessible for anybody) something else like loginUser() then the chain breaks. In any case, the authenticated test basically checks if the user has a laravel session cookie that is valid (not expired) and if not then redirects to the login route (asking them to login to access the protected routes held in this group meta-route) 
    route::post('/logout', [AuthController::class, 'logout']); 
});




