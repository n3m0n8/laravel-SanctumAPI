<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TasksResource extends JsonResource
{
    /**
     * NOTE the purpose of a resource is to shape our API data responses into a JSON output  as per the relevant json specification conventions.
     * In this case we are making a class instance of the JsonResource superclass which can be isntantiated whenever we want to respond with data via our TasksController to a request for tasks-related data... but in a correctly formatted json format (otherwise it will be sent over as a raw data dump)
     *  Generally, JSON conventions are : 
     *  + at the top of every json, we need to start with the data member - which acts as the 'container data member' that will, within it hold all of the relevant data. 
     * 
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    // here we see that the class template already handled making our container data memmber as an array from the http request object.
    public function toArray($request)
    {// now we can define what the granual data objects within this container data member will be. In this case we first set out a key-value pair of the id - string of the id value.  The reason why we make the value a string is because we can then use that string in a URL after a query ? demarcator, in order to locat a specific task on the basis of the passed id ... i.e. /tasks/?id={$id} cam then be www.wesbite.com/tasks/?id=43
        return [
            'id' => (string)$this->id,
            // now that we have this unique identifier of the id that associates the data set to a specific 'owner/requestor' - in this case the tasks table tabel to the user associated to thos tasks, we can create a new array element but this element will have a nested sub-array holding all of the relevant attitbutes of the data that is being brought over in relation ot the id of the requestor/owner... so having this multi-depth /nested construct is reccomended for constructing json so as to allow it o be granular but also specific to a specific reason/query/owner ... (somwhat similar to GraphQl, although with GraphQL the granularity is much more specific)
            'attributes' => [
                'name'=> $this->name,
                'description'=> $this->description,
                'priority'=> $this->priority,
                //good practice to add the timestamps also, even if the metadata isnt particularly required
                'created_at'=> $this->created_at,
                'updated_at'=> $this->updated_at,
            ],
            //just like above, we create a nested array... but in this case, we are outlining the relationships of the primary data being brought over (i.e. in this case the tasks table data relating to the foreign keyd id' user asking for it) but here, we are also providing additional related data about that user that we are dragging in from the users table in relation to the specific requesting/associated id (i.e. the user logggin in)
            //note that in this case, we can't just rely on the $this referent since this $this is point to the current request object which is a /tasks resource associated http request object... so we must add the additional referent of ->user to point $this->user instance object of the User elqouent model template which then opens up acces via that eloquent/orm model template class to our database's users table, and thereby we can chain a further directive of ->id 
            //note also the id's (string)format [again because we might use that for url like /user/${user} so we need it converted toString()]  
            'relationships' => [
                'id'=> (string)$this->user->id,
                'user name' => $this->user->name,
                'user email' => $this->user->email,
            ]
        ];

    }
}
