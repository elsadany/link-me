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
    function show(Request $request,Contact $contact){
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$contact->toArray()
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
    function destroy(Request $request,Contact $contact){
        $contact->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
    function toggleRead(Contact $contact){

            $contact->update(['is_read'=>1]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
}
