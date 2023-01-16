<?php 
// namespace makes a way to refer this file from other files within this directory/package.

namespace App\Traits;

//best practice for naming the trait is to name it as the filename is.
// a trait is similar to a middleware 'strategy' or an interface class in java ... its an oop-base concept that allows multiple (polymorphic) re-deployment in various instances/use cases.
trait HttpResponses {
    //first method defined which deals with successfull http responses. 3 args included. first arg deals with incoming API data. 2nd arg deals with http response (headers?) message which is set at default of null, arg3 deals with the status code, defaulted to 200 a ok. not there are several 200 success codes like 201 204 etc... we can overide arg3 value with more granualar status code on deployment of this trait when instantiating it.
    protected function success($data, $message=null, $code=200){
        // in function block we return the http response (on assumption of success.) we chain a toJson method which converts incoming (presumably byteccode?) array to a json (javascript object notation) var
        // within jason, we outline three args that are key-value pairs.
        //these are the 'templates' for structuring the array of incoming network response data (incoming from ip/ethernet/wifi packet headers- presumably in bytecode but now transferred into json). in that json, we have three templates: status, message and data.
        return response()->json([
            'status' => 'Request Success', // generic msg since any 200s code means success
            'message' => $message, //pass the incoming arg2 $message related data from the instance use of this trait into our json-templat of 'message' object property... message i assume refers to http headers
            'data' => $data, // same as above but in this case the data.
            //outside of this data array that will be toJson'd we also pass the $code var which as mentioned above, will be a 200 default code
        ], $code);
    }
// now we also define an arror message equivalent in our traits namespace class. but without defualt (because error can be 500(server) 400s(resource location) 300(permissions/unauthaurised))
    protected function error($data, $message=null, $code){
        return response()->json([
            'status' => 'Error has occurred', 
            'message' => $message, 
            'data' => $data, 
        ], $code);
    }
};
