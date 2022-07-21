<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
   /* public function __construct()
    {
        $this->middleware('auth');
    }*/

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        
        if(\Auth::user()->user_type == 'admin')
        {
			$customersCount=\App\Models\Customer::count();
			$clientCount=\App\Models\Client::count();
			$slotCount=\App\Models\Slot::count();
			$slotCountToday=\App\Models\Slot::whereDate('start_date',\Carbon\Carbon::today())
			->whereDate('end_date',\Carbon\Carbon::today())
			->orWhereDate('end_date',\Carbon\Carbon::today())
			->count();
			$bookingCount=\App\Models\Booking::count();
			$bookingCountToday=\App\Models\Booking::whereDate('created_at',\Carbon\Carbon::today())->count();

            return view('admin.dashboard.index',compact('customersCount','clientCount','slotCount','bookingCount','slotCountToday','bookingCountToday'));
        }
        
        if(\Auth::user()->user_type == 'carer')
        {
            return view('customer.dashboard.index');
        }
        if(\Auth::user()->user_type == 'carer_home')
        {
			$customersCount=\App\Models\Customer::count();
			 
			$slotCount=\App\Models\Slot::where('client_id',\Auth::user()->id)->count();
			$slotCountToday=\App\Models\Slot::where('client_id',\Auth::user()->id)
			->whereDate('start_date',\Carbon\Carbon::today())
			->whereDate('end_date',\Carbon\Carbon::today())
			->orWhereDate('end_date',\Carbon\Carbon::today())
			->count();
			$bookingCount=\App\Models\Booking::select('booking.*','slot.client_id')
			->leftJoin('slot','slot.id','booking.slot_id')
			->where('slot.client_id',\Auth::user()->id)
			->count();
			$bookingCountToday=\App\Models\Booking::select('booking.*','slot.client_id')
			->leftJoin('slot','slot.id','booking.slot_id')
			->where('slot.client_id',\Auth::user()->id)
			->whereDate('booking.created_at',\Carbon\Carbon::today())->count();
			$positionsCount=\App\Models\Positions::where('client_id',\Auth::user()->id)->count();
            return view('client.dashboard.index',compact('customersCount','slotCount','bookingCount','slotCountToday','bookingCountToday','positionsCount'));
        }
        
    }
}
