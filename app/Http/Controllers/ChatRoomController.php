<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Events\NewRoomEvent;
use App\Events\MessageSent;
use App\Events\IsTyping;
use App\Events\JoinedRoom;

class ChatRoomController extends Controller
{
    protected $tableName = 'chat_room';

    /*
        @Return AJAX purposes
    */
    protected $request = array();

    public function __construct() {
        $this->requestStatus();
    }

    public function getUserRooms() {
        $this->isAuthenticated();

        $result = DB::table($this->tableName)
                    ->select($this->tableName.'.*')
                    ->addSelect(DB::raw('count(*) as participants'))
                    ->leftJoin('chat_room_participants', 'chat_room_participants.chatroomID', '=', $this->tableName.'.id')
                    ->where('created_by', Auth::user()->id)
                    ->groupBy($this->tableName.'.id')
                    ->get();

        if($result) {
            $this->request['status'] = 1;
            $this->request['message'] = '';
            $this->request['rows'] = $result;
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'No result to be returned';
        }

        return $this->request;
    }

    public function getAllRooms() {
        $this->isAuthenticated();

        $result = DB::table($this->tableName)
                    ->select($this->tableName.'.*')
                    ->addSelect('users.name as userName')
                    ->addSelect((DB::raw('count( ( CASE WHEN chat_room_participants.participantID = '.Auth::user()->id.' THEN chat_room_participants.id END ) ) AS joined')) )
                    ->addSelect(DB::raw('count(chat_room_participants.id) as participants'))
                    ->leftJoin('users','users.id', '=',$this->tableName.'.participantID')
                    ->leftJoin('chat_room_participants', 'chat_room_participants.chatroomID', '=', $this->tableName.'.id')
                    ->where('created_by', '!=', Auth::user()->id)
                    ->groupBy($this->tableName.'.id')
                    ->get();

        if($result) {
            $this->request['status'] = 1;
            $this->request['message'] = '';
            $this->request['rows'] = $result;
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'No result to be returned';
        }

        return $this->request;
    }

    public function getActiveRooms() {

    }

    public function createRoom(Request $request) {
        //if auth
        $this->isAuthenticated();

        if($request) {
            $roomCount = DB::table($this->tableName)->where('created_by', Auth::user()->id)->get();
            if(count($roomCount) < 5) {
                $isPrivate = 1;
                if($request->isPrivate) {
                    $isPrivate = 0;
                }
                $insert = array(
                    'roomName' => $request->name,
                    'participantID' => Auth::user()->id,
                    'randomPass' => $request->randomPass,
                    'isPublic'  => $isPrivate,
                    'created_by'=> Auth::user()->id,
                    'created_at' => date('Y-m-d H:m:i'),
                    'updated_at' => date('Y-m-d H:m:i'),
                );
                $id = DB::table($this->tableName)->insertGetId($insert);
                if($id) {

                    //trigger an even
                    event(new NewRoomEvent(Auth::user(), $insert));

                    $this->request['status'] = 1;
                    $this->request['message'] = 'Room successfully created';
                    $this->request['roomPassword'] = $request->randomPass; 

                    //join the room
                    $join = array(
                        'chatroomID' => $id,
                        'participantID' => Auth::user()->id,
                        'created_at' => date('Y-m-d H:m:i'),
                        'updated_at' => date('Y-m-d H:m:i'),
                    );
                    DB::table('chat_room_participants')->insert($join);
                } else {
                    $this->request['status'] = 0;
                    $this->request['message'] = 'Failure to create room';
                }
            } else {
                $this->request['status'] = 0;
                $this->request['message'] = 'Maximum room count';
            }
            
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'Empty payload';
        }

        return $this->request;
    }

    public function joinRoom(Request $request) {
        $this->isAuthenticated();
        $where = array(
            'chatroomID' => $request->roomID,
            'participantID' => Auth::user()->id,
        );
        $joined = DB::table('chat_room_participants')->where($where)->get();

        if(count($joined)) {
            $this->request['status'] = 0;
            $this->request['message'] = 'You already joined this room';
        } else {

            $room_pass = DB::table('chat_room')->where('id', $request->room_id)->get();
            
            if($request->room_is_public == 1) {
                $insert = array(
                    'chatroomID' => $request->room_id,
                    'participantID' => Auth::user()->id,
                );
                $id = DB::table('chat_room_participants')->insertGetId($insert);
                if($id) {
                    
                    //trigger event
                    event(new JoinedRoom(Auth::user(), $room_pass));

                    $this->request['status'] = 1;
                    $this->request['message'] = 'Room successfully joined';
                } else {
                    $this->request['status'] = 0;
                    $this->request['message'] = 'Failed to join room.';
                }
            } else {
                if($request->room_pass == $room_pass[0]->randomPass) {
                    $insert = array(
                        'chatroomID' => $request->room_id,
                        'participantID' => Auth::user()->id,
                    );
                    $id = DB::table('chat_room_participants')->insertGetId($insert);
                    if($id) {
                        $this->request['status'] = 1;
                        $this->request['message'] = 'Room successfully joined';
                    } else {
                        $this->request['status'] = 0;
                        $this->request['message'] = 'Failed to join room.';
                    }
                } else {
                    $this->request['status'] = 0;
                    $this->request['message'] = 'Incorrect room password';
                }
            }
            
            
        }

        return $this->request;
    }

    public function isJoined(Request $request)
    {
        //if authenticated
        $this->isAuthenticated();
        $where = array(
            'chatroomID' => $request->roomID,
            'participantID' => Auth::user()->id,
            'status' => 1,
        );
        $isExist = DB::table('chat_room_participants')->where($where)->exists();

        $where2 = array(
            'id' => $request->roomID,
            'created_by' => Auth::user()->id,
            'status' => 1,
        );
        $isHost = DB::table('chat_room')->where($where2)->exists();

        if($isExist || $isHost) {
            $this->request['status'] = 1;
            $this->request['message'] = 'Is joined';
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'Not joined';
        }

        return $this->request;
    }

    public function getRoomMessages(Request $request) 
    {
        $this->isAuthenticated();

        if($request) {
            $where = array(
                'chatroomID' => $request->room_id,
                'status'     => 1,  
            );
            $messages = DB::table('room_chat_logs')
                        ->select('room_chat_logs.*')
                        ->addSelect('users.name as userName')
                        ->leftJoin('users', 'users.id', '=','room_chat_logs.participantID')
                        ->where($where)->get();

            if(count($messages)) {
                $this->request['rows']  = $messages;
                $this->request['status'] = 1;
                $this->request['message'] = '';
            } else {
                $this->request['status'] = -1;
                $this->request['message'] = 'No messages';
            }
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'Empty payload';
        }

        return $this->request;
    }

    public function sendTypingEvent(Request $request)
    {
        $this->isAuthenticated();

        if($request) {
            if($request->isTyping) 
            {
                //trigger event
                event(new IsTyping(Auth::user(), $request->room_id, $request->isTyping));
                $this->request['status'] = 1;
                $this->request['message'] = 'Typing..';
            } else {
                //trigger event
                event(new IsTyping(Auth::user(), $request->room_id, $request->isTyping));
                $this->request['status'] = 0;
                $this->request['message'] = 'Stopped typing..';
            }
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'Empty payload';
        }
        return $this->request;
    }


    public function sendMessage(Request $request)
    {
        $this->isAuthenticated();
        
        if($request) {
            $insert = array(
                'participantID' => Auth::user()->id,
                'chatroomID'    => $request->room_id,
                'message'       => $request->message,
                'created_at'    => date('Y-m-d H:m:i'),
            );
            $id = DB::table('room_chat_logs')->insertGetId($insert);
            if($id) {

                //trigger event
                event(new MessageSent(Auth::user(), $request->room_id));

                $this->request['status'] = 1;
                $this->request['message'] = 'Message successfully sent';
            } else {
                $this->request['status'] = 0;
                $this->request['message'] = 'Failed to send message';
            }
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'Empty payload';
        }
        return $this->request;
    }

    public function isExist(Request $request)
    {
        //if authenticated
        $this->isAuthenticated();

        $isExist = DB::table($this->tableName)->where('roomName', $request->name)->exists();
        if(!$isExist) {
            $this->request['status'] = 1;
            $this->request['message'] = 'Name is available';
        } else {
            $this->request['status'] = 0;
            $this->request['message'] = 'Name already exist';
        }

        return $this->request;
    }

    public function isAuthenticated() {
        if(!Auth::check()) {
            return redirect('/login');
        } 

        return true;
    }
    public function requestStatus() {
        $this->request['status']    = 1;
        $this->request['message']   = '';
    }
}
