<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TasksResource;
use App\Http\Requests\StoreTaskRequest;
use App\Traits\HttpResponses;

// alongside our custom -defined AuthController, which is relatively lean because we implement our own custom functions within it, we also create this following TasksController class. But when creating this one, we use make:controller TasksController -r with r standing for it to be generated as a RESOURCE class- what this means is that the class template comes already packaged with various methods for resource storage... this is different from the requirements of our routes so we deploy the -r for this template.

//NOTE the important aspect of useing a Resource Controller like the one here is that it comes pre-packaged with many routes relating to all that is needed for handling a particular resource. 
// in this case the reosurce is the /tasks base which in our api.php routes namespace we will pass as arg1 to out route::resource method. arg2 is this current TasksController Class which is itself an implementation/inheritance of the BaseController super-class... i.e. it inherits multiple methods like: 
/*
    ///BASE ROUTE / HOME 
GET|HEAD        / .......... 
// AUTH CONTROLLER RELATED- CUSTOME DEFINED/BAREBONES CONTROLLER-CLASS ROUTE HERE FOR USER LOGIN, REGISTRATION, LOGOUT.
POST          api/login ......................... AuthController@login
POST            api/logout .......................................... AuthController@logout
POST            api/register ...................................... AuthController@register

TASKS RESOURCE CONTROLLER  ROUTES HERE 
WE SEE FOR INSTANCE, FROM THE /TASKS/ base the various CRUD operations 
>>>
  GET|HEAD  api/tasks ............... tasks.index › TasksController@index
  POST  api/tasks ................ tasks.store › TasksController@store
  GET|HEAD  api/tasks/create ...... tasks.create › TasksController@create
  GET|HEAD api/tasks/{task} ........ tasks.show › TasksController@show
  PUT|PATCH api/tasks/{task} ........tasks.update › TasksController@update
  DELETE api/tasks/{task} .....tasks.destroy › TasksController@destroy
  GET|HEAD api/tasks/{task}/edit ..... tasks.edit › TasksController@edit
*/


class TasksController extends Controller
{
    // use the trait HttpResponses class  for use in deploying error message redirects
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // here we can list relevant custom routes for our tasks resource.. .for example listing all tasks or whatever we output to the frontend. 
        //following is a test route, we take the http response object container var anb chain to it a json output meth with arg1 as hello message key-value object for Postman:
            //now we deprecate this test response
            //return response()->json('Test'); 
        //instead of that test, we now have a TaskResource defined which serialises our $request http object as a basic json format with the container toArray and then a nested further array with a key value pair of id=> (string)id
        //So we can use this TaskeResource's collection method which is inherited by the TasksResource template from the JsonResource superclass that it is an instantiation of. 
        return TasksResource::collection(
            //we specify here a conditional test for the data to be returned in  the json'd version of the $request object. The data is to be fetched from the Task eloquent model class intermediary/ORM access to our tasks table on the database  WHERE the user_id key-column field which is, rememebr the FK interliunking the Users table's id unique key-column field to the task table's unsigned big int user_id key-column field... that is the arg1 passed on the eloquen where() method which understakes the SQL WHERE query.... arg2 is what we want to compare it to... in this case, we pass the authenticable middleware class's [current]user() method which fetchs the currently authenticated user (not clear how since I can't find where the user() method is, seems to be held in contracts/auth/authenticable class because Auth is a facade class that acts as a kind of crossroads for other 'action/concrete' classes.... in any case, we fethc the currently auth'd user and fetched specifically (but chaining ->) that user's id value on the users table of our db.... the point here is we are checking the tasks user_id foreign key value against the users table's currently authenticated user... so that any logged in user will only see thos tasks that have aforeign key association to them, and thus compartmentalise the tasks table's contenst (many) to the one relevant user being logged in a session (one)
            //DONT forget to chain a get () to the wher() method in order to actually fetch those relevant tasks of the logged in user. 
            Task::where('user_id', Auth::user()->id)->get()
        );
    }

    /*
    public function create()
    {
        //
    }
    WE DONT USE THE CREATE METHOD BECAUSE THAT CONCERNS THE FRONT_END USER'S INTERACTION WITH CREATING A RESOURCE ... SOMETHIGN THAT WE AS BACKEND API PROVIDERS ARE NOT CONCERNED WITH.
    */
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //AS WAS THE CASE WITH THE AUTHCONTROLLER CLASS, INSTEAD OF VALIDATING THE INCOMING FORM DATA (WHO IN THIS CASE WOULD HAVE SUBMITTED A FORM WITH DATA TO CREATE A NEW TASK RATHER THAN A NEW USER)FOR A NEW TASK WITHIN OUR TASKSCONTROLLER::STORE METH BLOCK DIRECTLY  WE INSTEAD CREATE A FORMREUQEST CLASS INSTANCE THAT WE CAN THEN REFERENCE IN THIS STORE METHOD BLOCK AS A PREFIX TO THE HTTP REQUEST CONTAINR OBJECT VAR instead of the generic Request class.
    public function store(StoreTaskRequest $request)
    {
        // again we call the validation method on all of the incoming request object contents as per the rules held by our StoreTaskRequest class's ::rules() meth
        $request->validated($request->all());
        //once validation has passed we must create an actuall instance of the new task based on the eloquent orm Task model class - which will then be slotted into our db via eloquent ORM 
        $task = Task::create([
            // note that the user_id key-value pair is a bit different here because our user_id value is not incoming from the tasks table... the tasks table's user_id key-column field is an unsigned big it which has a constraint associating it to the users table... therefore, when creating a new task, we will be importing that information relating to the associated id/fk association via whichever user is the one who is filling in the form to create that task... which can be done useing the authenticable superclass' user() method although how that works is a bit more complex to me... We use that metho to grab the currently laravel session token auth'd user from the users table and take that users table's id key-column field value (via chained on -> id property) and that's what we assign to the user_id foreign key/unsignedbigint key-column on the tasks table.
            'user_id' => Auth::user()->id,
            // the others are simpler since this is just grabbing whatever the user has filled into the form for each of the relevant fields and the grabbing these from the $request object raw data as structured by the HttpResponses trait class 
            'name' =>$request->name,
            'description' =>$request->description,
            'priority' =>$request->priority,
        ]);
        // once the task has been create and sent to the tasks table, we will the pass this $task container var holding the data into an instance of our json(TasksResource class-based) response, specifically shifting the data into its structure so as to send that json back to the user as a confirmation of the task they have created into the database.
        return new TasksResource($task);
        
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * THe show method could be done in a direct manner:  i.e. : 
     * public function show($id){
     *      $task = Task::where('id', 'id')->get();
     *      return action ...
     * }
     * but since we have a custom defined model underlying the particular resource that we are querying - in this case the Task eloquent model  class is passed to the show(method as arg1) instead of the generic $id placeholder var... behind the scenes, Illuminate works out that this shorthand is making a request to a task instance object of the Task model class stored (via Eloquen ORM) on mySql that matches the unique identifier key-column filed (in this case the unique id key-column field value for the tasks table that auto-increments each new task added to it as a unique identifier) 
     * So with the shorthand done, we can return our action... and because we have created a TasksResource class inheriting from the JsonResource superclass that provides the structured data with information about the requested/fetched task from the tasks table, we can now return and instance of this class specific to the $task container var instance of the Task eloquent model class that has been requested TO the user in response to the request FROM the user... that json will hold easy to unpack data that can be rendered on the frontent via js or other frontend manner to SHOW the user the task they have requested
    */
    public function show(Task $task)
    {
        //here we implement a basic if statemetn check that checks that the AUth class instance of the user being identified by the laravel session token cookie is indeed equal to the id on the mySQL users table that is being brought in as a foreign kye into that specific $task's unsignedbigint user_id key-column row/record.
        //if (Auth::user()->id !== $task->user_id){
            //if not authorised then return instance of an error object of the httpResponses trait class we defined at start of tutorial which takes arg1 data (null here), arg2 msg (unauthorised), arg3 http status code
            //    return $this->error('', 'Unauthorised', 403);
        //}
        //else ...
        //else {
          //  return new TasksResource($task);
        //}

        // the above check and return statemetns are deprecated because we have a shorthand now available to use since we created a private function at bottom of namespace that we can deploy here  and with the show() meth block. To do so, we deploy a ternary logic that runs the private function to check if the private function's logic holds true or not (i.e. is the user id value == to the table user_id FK value). If it is !notTrue then we got option 1 of the ternary - which is simply the private function itself as an action ... ie the return statement of the authd check which will redirect to a unauthorise 403... but if the first third of the ternary statement (ie the logical reading of the auth'd private function  check) holds true then we proceed with what was originally in the return statement
        return $this->isNotAuthd($task) ? $this->isNotAuthd($task) : new TasksResource($task);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * AS IS THE CASE WITH CREATE ABOVE THIS EDIT CONTROLLER METHOD DOESNT CONCERN BACKEND... THATS BECAUSE ANY DEIFNTION OF A CREATE OR EDIT PATH WOULD JUST BE A REDIRECT OF THE USER TO THE FRONT END 'SPLASH PAGES' PROVIDING THEM THE FORM THAT WOULD THEN ALLOW THEM TO EITHER STORE(vis a vis CREATE) OR UPDATED (vis-a-vis edit) ... WE AT BACKEND ARE CONCERNED WITH STORE AND UPDATE SINCE THAT IS WHAT INTERACTS WITH DATA AT REST ON OUR DATABASE- HANDLED VIA THE ELOQUENT ORM TEMPLATE CLASSES /
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     public function edit($id)
     * {
     * //
     * }

    */
    /**
     * Update the specified resource in storage.
     *
     * NOTE the followin is a data dump of our routes in this project shown using php artisan routes:list 
     * GET|HEAD        / ......  POST            _ignition/execute-solution ....... ignition.executeSolution › Spatie\LaravelIgnition › ExecuteSolutionController
Either Get or head    these two seem to be inbuilt routes for maintenance of laravel 
                                                                            package
   GET|HEAD        _ignition/health-check... ignition.healthCheck 
                                            › Spatie\LaravelIgnition › HealthCheckController
  POST            _ignition/update-config ..... ignition.updateConfig 
                                                › Spatie\LaravelIgnition › UpdateConfigController
  // here we see the User template model class related routes... login is natively handled using the Authenticable superclass and in our case via the AuthController class that we defined with three methods : login, logout, register that redirect to a success or error resource (based on success/error methods defined in our HttpResponses trait calss at those paths on the basis of whether authentication worked or failed.
  POST            api/login ..... AuthController@login
  POST            api/logout ......... AuthController@logout
  POST            api/register ...... AuthController@register
  // here we see the Task eloquent orm model class-related paths... the generic path is /tasks as a kind of basis for dealing with anythin relating to the tasks table on SQL that is translated/handled by our Task.php elqouent class. that /tasks base has been defined in the api.php route class. 
  //we see the generic /tasks path is used to get()->all() tasks as an index of all tasks if we send a GET header
  GET|HEAD        api/tasks .... tasks.index › TasksController@index
  // and if change the header to POST on the same path, we instead will STORE on SQL a new task that is being created by TasksController::store() 
  POST            api/tasks .... tasks.store › TasksController@store
  // we are not using /create here because it's frontend concern, but that's where we would style our frontend form for the user to create a task so as to send a POST STORE request to our DB via TasksController::store()
  GET|HEAD        api/tasks/create ..... tasks.create › TasksController@create
  // here the path takes an extra endpoint of the currently dealt with instance object of the Task eloquent model ORM class ... i.e. whichever current task id is being passed to our TaskController::show(Task $task) will end up being the /tasks/{task id number} it's a GET
  GET|HEAD        api/tasks/{task} ..... tasks.show › TasksController@show
  // here same as create, it's frontend only concern
  GET|HEAD        api/tasks/{task}/edit .... tasks.edit › TasksController@edit
  // with update we can send either a PUT or a PATCH header to undertake the UPDATE directive on our SQL i.e to modify the data on the tasks table for the pass $task in the tasks/{task route} NOTE THAT A PUT REQUEST IS EXPECTING TO MAKE TOTAL CHANGES TO ALL KEY-COLUMN FIELDS OF A GIVEN RECORD/ROW ... IN THIS CASE, WE ARE USING A PATCH REQUEST BECAUSE WE ONLY WANT TO ALLOW USER TO UPDATE THREE KEY-COLUMN FIELDS IN THE RELEVANT TASK{ID} INSTANCE - THE NAME KEY/COLUMN, THE DESCRIPTION AND/OR THE PRIORITY. WE DON'T WANT THEM TO BE ABLE TO UPDATE THE CREATE_AT ETC 
  PUT|PATCH       api/tasks/{task} ..... tasks.update › TasksController@update

  DELETE          api/tasks/{task} .... tasks.destroy › TasksController@destroy
  // this seems to be another User model related route, but instead of doing some action via the AuthController like login or register, this one simple shows an outpute of the requested user (which presumably will be the user intance object of the User Eloquent template model class that is currently authenticated)
  GET|HEAD        api/user ........ 
  // here we have our cross site Laravel sanctum csrf bearer token which provides session authentication for a properly logged in user. It's a get header because we are fetching the data of this cookie to check for auhtetnication of the session 
  GET|HEAD        sanctum/csrf-cookie ...sanctum.csrf-cookie › Laravel\Sanctum 
                                         › CsrfCookieController@show 
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // for update, we take two elements as arg1 and arg2. the http request object (holding http data and headers) of the Request class and the $currentTaskObject instance of the Task Eloquent ORM class as directed by the /tasks/{currentTaskID} path
    public function update(Request $request, Task $task)
    {
        //first we deploy a check that the user requesting the update on the task instance is auth'd to do so because their id value on the users table is cross-correlating to the user_id key-column value on the tasks table
        if (Auth::user()->id !== $task->user_id){
            return $this->error('', 'Unauthorised', 403);
        }
            //if the user is indeed cross-correlating to that task, then we take that task instance object of the Task Eloquent ORM  model (which is inheriting from the Eloquent\Model superclass that has all these pre-defined methods for dealing with multiple databases) and we chain on an update() method that will signal to SQL to UPDATE ALL fields that have had a value inputed onto them by the user from the frontend form (all because we have put in the update meth's arg1 the http request object which has an inbuilt all() method that is being chained on to it... that all() meth gets all inputs and files associated with the form submission httpRequest )
            $task->update($request->all());
            // once we undertaken the required changes on our database via the instance of our Eloquent ORM class, we can now let the user now by deploying our custom ('back to you') json'd TaskResource class template by creating a new instance of that TaskResource and filling out its toArray constructor method with our $request variable (in this cass the toArray($request) constructor is filled in with the newly updated id'd update()arg2-passed $task instance of the Eloquent Task model class which has just had its relevant modified key-column field values updated on SQL as per the http request object that had come in as arg1 of this current update() function whose function block we are working within) 
            return new TasksResource($task);
            // now Jason can arrive to user, handled on the frontend to show user confiramtion of their changes on our db.
            //ONE THING TO NOTE FOR POSTMAN IS THAT, WHEN DEALING WITH AN UPDATE PATCH REQUEST TO SIMULATE THE USER FORM INPUT WE MUST CHOOSE x-www-form-urlencoded instead of just form... not sure why.
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        // for verification of authentication, we can simply copy paste the previously used check that we have used for update and show... but in keeping with the DRY principile (don't repeat yourself) we can instead create a basic abstraction of the auth'd check to deploy in these various methods... That auth'd check method is defined below this final action block (the delete () block) - but NOTE that we won't deploy it to the Update method block - we will keep the update auth'd check the same as the originally written one... the reason being the that the update method block is doing somethin extra beyond the user table's id value being cross-correlated to the table's user_id FK value... 
        //simply take the task instance and deploy the prebuilt Eloquent\Model class' delete() handler method to send a DELETE signal on sql (that meth being inherited by our Task eloquent class of which the passed /tasks/$taskid object is an instance of)
        //$task->delete();
        //to confirm this to the user, there is no point in sending any json'd data so we send a null response... it's up to frontend peeps to style some frontent output using some state management for example to output a notification with Alpine JS or Vue or React or simply a blade vue redirect
        //return response(null, 204); // return nothing and a 204 signal success.
        //above return is deprecated and instead we deploy ternary as with show()
        return $this->isNotAuthd($task) ? $this->isNotAuthd($task) : $task->delete();
    }
    // here is aforementioned basic level abstraction of auth'd check method. We make it a private function since we want any other function within this class namespace that deploy it to not be able to acces its function block content and we don't want anything outside of this TasksController namespace to be able to acces this function at all... thus restricting ability to manipulate the auth check
    private function isNotAuthd($task){
        //in the body, we copy the traditional if check that we had used initially
        if (Auth::user()->id !== $task->user_id){
            return $this->error('', 'Unauthorised', 403);
        }
    }
}
