<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantController extends Controller
{

    protected $tableName = '';

    /*
        @Return AJAX purposes
    */
    protected $request = array();

    public function __construct() {
        $this->requestStatus();
    }

    public function setCurrentUser(Request $request)
    {
        if(isset($request))
        {
            $participant = DB::table('participants')->where('name', $request->name)->get();
            session()->put('participant', $participant[0]);

            if($this->isSessionActive('participant')) {
                return 1;
            } else {
                return view('welcome');
            }
        }
    }

    public function insertParticipant(Request $request)
    {
        if($request) 
        {
            $exist = DB::table('participants')->where('name', $request->name)->exists();
            if(!$exist) {
                $insert = array(
                    'name' => $request->name,
                    'created_at' => date('Y-m-d H:m:i'),
                    'updated_at' => date('Y-m-d H:m:i')
                );
                $id = DB::table('participants')->insertGetId($insert);
    
                if($id) {
                    $this->request['status'] = 1;
                    $this->request['message'] = 'Successfully inserted';
                } else {
                    $this->request['status'] = 0;
                    $this->request['message'] = 'Failure to insert';
                }
            } else {
                $this->request['status'] = 0;
                $this->request['message'] = 'Duplicate entry';
            }
            

            return $this->request;
        }
    }
    public function isExist(Request $request)
    {
        $isExist = DB::table('participants')->where('name', $request->name)->exists();
        if(!$isExist) {
            $this->request['status'] = 1;
            $this->request['message'] = 'Name is available';
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'Name already exist';
        }

        return $this->request;
    }

    public function getCurrentUser() 
    {
        if( $this->isSessionActive('participant') ) 
        {
            return session()->get('participant');
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = '';
        }

        return $this->request;
    }

    public function isSessionActive($sessName) {
        if(!session()->exists($sessName)) {
            return false;
        }
        
        return true;
    }

    public function requestStatus() {
        $this->request['status']    = 1;
        $this->request['message']   = '';
    }
}
