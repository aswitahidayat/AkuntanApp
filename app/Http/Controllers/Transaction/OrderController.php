<?php
/**
 * Created by PhpStorm.
 * User: Shandy
 * Date: 4/22/2019
 * Time: 7:30 PM
 */

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Order\OrderDtl;
use App\Models\Order\OrderAssumption;
use App\Models\Assumption;

use Auth;
use Illuminate\Http\Request;
use DataTables;
use DB;
use Redirect,Response;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        return view('transaction.order.orderIndex');
    }

    public function search(Request $request)
    {
        if($request->ajax())
        {
            $url = route('company.index');

            $query = Order::
            leftJoin('kka_dab.mst_order_program', 'ordhdr_program', '=', 'kka_dab.mst_order_program.ordprg_id')
            ;

            if($request->name != ''){
                $query->where('ordhdr_ordnum', 'LIKE',  "%$request->name%");
            }
            $data = $query->get();
            
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->ordhdr_id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editOrder">Edit</a>';
                    $btn .= ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->ordhdr_id.'" data-original-title="Assumption" class="assumption btn btn-primary btn-sm assumptionOrder">Assumption</a>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $exception = DB::transaction(function() use ($request) {
            if($request->ordhdr_id == ''){
                // $data = new Order();
                $orderNum = $this->orderNum();
                $q = Order::create([
                    'ordhdr_ordnum' => $orderNum,
                    'ordhdr_program' => $request->ordhdr_program,
                    'ordhdr_service_hdr' => $request->ordhdr_service_hdr,
                    'ordhdr_period_count' => $request->ordhdr_period_count,
                    'ordhdr_period_lastyear' => $request->ordhdr_period_lastyear,
                    'ordhdr_pension_age' => $request->ordhdr_pension_age,
                    'ordhdr_sal_increase' => $request->ordhdr_sal_increase,
                    
                    'ordhdr_date' => $request->ordhdr_date,
                    'ordhdr_pay_date' => $request->ordhdr_pay_date,
                    'ordhdr_amount' => $request->ordhdr_amount,
        
                    'ordhdr_pay_status' => 'N',
        
                    'ordhdr_created_by' => Auth::user()->user_type,
                    'ordhdr_created_date' => date(now()),
                ]);
                // $data->save();

                $this->orderDetail($request->detail, $q->ordhdr_id, $q->ordhdr_ordnum);
                $this->setAssumption($q->ordhdr_id, $q->ordhdr_ordnum, $q->ordhdr_period_count, $q->ordhdr_period_lastyear);
            }else{
                Order::
                    where('ordhdr_id', $request->ordhdr_id)
                    ->update(['ordhdr_ordnum' => $request->ordhdr_ordnum,
                        'ordhdr_program' => $request->ordhdr_program,
                        'ordhdr_service_hdr' => $request->ordhdr_service_hdr,
                        'ordhdr_period_count' => $request->ordhdr_period_count,
                        'ordhdr_period_lastyear' => $request->ordhdr_period_lastyear,
                        'ordhdr_pension_age' => $request->ordhdr_pension_age,
                        'ordhdr_date' => $request->ordhdr_date,
                        'ordhdr_amount' => $request->ordhdr_amount
                        ]);

                $this->orderDetail($request->detail, $request->ordhdr_id, $q->ordhdr_ordnum);
            }
        });

        return is_null($exception) ? response()->json(['success'=>'Order saved successfully.']) : $exception;

    }

    public function edit($ordhdr_id)
    {
        $data = Order::find($ordhdr_id);
        return response()->json($data);
    }

    public function orderDetail($detail, $id, $num){
        foreach ($detail as $key =>$value) {
            $cd = OrderDtl::create([
                'orddtl_hdrid' => $id,
                'orddtl_ordnum' => $num,
                'orddtl_npk' => !empty($value['NPK'])?$value['NPK']:'',
                'orddtl_name' => !empty($value['Name'])?$value['Name']:'',
                'orddtl_sex' => !empty($value['Gender'])?$value['Gender']:'',
                'orddtl_birthdate' => !empty($value['Birthdate'])?$value['Birthdate']:'',
                'orddtl_ktp_num' => !empty($value['KTP'])?$value['KTP']:'',
                'orddtl_npwp_num' => !empty($value['NPWP'])?$value['NPWP']:'',
                'orddtl_addr' => !empty($value['Address'])?$value['Address']:'',
                'orddtl_hp' => !empty($value['HP'])?$value['HP']:'',
                'orddtl_startdate' => !empty($value['Startdate'])?$value['Startdate']:'',
                'orddtl_curr_sal' => !empty($value['Salery'])?$value['Salery']:'',

                'orddtl_created_by' => 1,
                'orddtl_created_date' => date(now()),
            ]);
        }
    }

    public function orderNum():?string 
    {
        $year= date("Y");
        $count = 1;
        $q = Order::where('ordhdr_ordnum', 'LIKE', "%$year%")
        ->orderBy('ordhdr_id', 'desc')->first();
        if(isset($q->ordhdr_ordnum)){
            $var2 = str_replace($year.'ORD',"",$q->ordhdr_ordnum);
            $count = str_pad( $var2+1, 5, "0", STR_PAD_LEFT );
        }

        return $year.'ORD'.$count;
    }

    public function setAssumption($id= 0, $numm=0, $count =0, $year = 0){
        for ($x = $year; $x > ($year - $count); $x--) {
            $ass = Assumption::get();
            foreach ($ass as $key => $value){
                OrderAssumption::create([
                    'ordass_hdrid' => $id,
                    'ordass_ordnum' => $numm,
                    'ordass_periode' => $x,
                    'ordass_sp' => 'S',
                    'ordass_code' => $value['assump_templ_code'],
                    'ordass_value' => 0,
                    'ordass_created_by' => Auth::user()->user_id,
                    'ordass_created_date' => date(now()),
                ]);
            }

        }

    }
}
