<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\Country;

class ContactsApi extends Controller
{
    function index(Request $request){
        $tickets=Contact::latest('id');
        if($request->user_id!='')
            $tickets=$tickets->where('user_id',$request->user_id);
        $tickets=$tickets->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$tickets->toArray()
        ]);
    }
    function show(Request $request,Ticket $ticket){
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>Ticket::where('id',$ticket->id)->with('replies')->first()->toArray()
        ]);
    }

    function update(Ticket $ticket,Request $request){
        $request->validate([
            'reply'=>'required',

        ]);
       $user=$request->user();
       $user->ticketReplies()->create([
           'reply'=>$request->reply,
           'ticket_id'=>$ticket->id
       ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم أضافة الرد بنجاح'
        ]);
    }
    function destroy(Request $request,Ticket $ticket){
        $ticket->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
    function toggleActive(Country $country){
        if($country->is_active==1)
            $country->update(['is_active'=>0]);
        else
            $country->update(['is_active'=>1]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
}
