<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\District;
use Auth;
use Illuminate\Http\Request;
use DataTables;
use DB;

class DistrictController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request){
        if(Auth::user()->level == 'user') {
            Alert::info('Oopss..', 'Anda dilarang masuk ke area ini.');
            return redirect()->to('/');
        }

        if($request->ajax())
        {
            // $data = District::get();
            $data = DB::table('kka_dab.mst_district')
            ->leftJoin('kka_dab.mst_province', 'kka_dab.mst_district.dis_provid', '=', 'kka_dab.mst_province.prov_id')
            ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->dis_id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editDistrict">Edit</a>';
                    $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->dis_id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteDistrict">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function edit($prov_id)
    {
        $data = District::find($prov_id);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        if($request->dis_id == ''){
            $data = new District();
            $data->dis_provid = $request->dis_provid;
            $data->dis_name = $request->dis_name;
            $data->dis_bps_code = $request->dis_bps_code;
            $data->dis_status = $request->dis_status;
            $data->dis_created_by = 1;
            $data->dis_created_date = date(now());
            $data->save();
        }else{
            DB::table('kka_dab.mst_district')
                ->where('dis_id', $request->dis_id)
                ->update(['dis_name' => $request->dis_name,
                    'dis_bps_code' => $request->dis_bps_code,
                     'dis_status' => $request->dis_status,
                     'dis_updated_date' => date(now())]);
        }
        return response()->json(['success'=>'District Type saved successfully.']);
    }

    public function destroy($dis_id)
    {
        District::find($dis_id)->delete();
        return response()->json(['success'=>'District Type deleted successfully.']);
    }

    public function getdistrict(Request $request){
        // var_dump($request);
        // if(Auth::user()->level == 'user') {
        //     Alert::info('Oopss..', 'Anda dilarang masuk ke area ini.');
        //     return redirect()->to('/');
        // }
        // if($request->ajax())
        // {
            // $data = District::get();
            $cari = $request->cari;
            $data = DB::table('kka_dab.mst_district')
            ->where('dis_provid', $cari)
            ->get();
            
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->dis_id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editDistrict">Edit</a>';
                    $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->dis_id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteDistrict">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        // }
    }
}