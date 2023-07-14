<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function handleRespondSuccess($message ,$data){
        $respond = [
            'message'=>$message,
            'data'=>$data
        ];

        return response()->json($respond);
    }
    public function  handleRespondError($message){
        $respond = [
            'message'=>$message,
        ];
        return response()->json($respond);
    }
}
