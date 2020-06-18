<?php

namespace Influencer\MonthlyReport\Http\Controllers;

use App\Attachment;
use App\OnesignalDevice;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyReport extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * save influencer montly report
     */
    public function sendReport(Request $request)
    {
        $data= $this->validate($request, ['username'=>'required|exists:users,username','month'=>'required|numeric|max:12','year'=>'required|numeric','report'=>'required|file']);
        $user= User::where('username',$data['username'])->first();
        if(!$user)
            return response()->json(['message'=>'This user is not found or seems to be deleted'],400);

        $url= $this->uploadInfluencerRequestToS3($request,$user);
        $attachment= $user->attachments()->create([
            'url'=>$url,
            'username'=>$data['username'],
            'month'=>$data['month'],
            'year'=>$data['year'],
        ]);
        return response()->json(['message'=>'Attachment Saved Successfully','report'=>$attachment->url],200);

    }

    private function uploadInfluencerRequestToS3(Request $request, User $user){
        $file = $request->file('report');
        $name=$file->getClientOriginalName();
        $filePath = 'attachable/' .$user->id.'/'. $user->username.'-'.$request->month.'-'.$request->year??Carbon::now()->year.$file->getClientOriginalExtension();        Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');
//        $user->addMediaFromRequest('report')->toMediaCollection('monthly_report_attachments','s3-plus');
        return $filePath;
    }

    public function getBusinessUserNames(Request $request)
    {
        if(!$request->username)
            return response()->json(['usernames'=>[]]);
        $userNames= User::where('username','like',"%".$request->username.'%')->take(20)->select(['id','username'])->get();
        return response()->json(['usernames'=>$userNames]);
    }
}

