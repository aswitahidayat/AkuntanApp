<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Auth;
use Illuminate\Http\Request;
use DataTables;
use DB;

class ProvinceController extends Controller
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
            $data = Province::get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->prov_id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editProvince">Edit</a>';
                    $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->prov_id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteProvince">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function edit($prov_id)
    {
        $data = Province::find($prov_id);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        if($request->prov_id == ''){
            $data = new Province();
            $data->prov_name = $request->prov_name;
            $data->prov_bps_code = $request->prov_bps_code;
            $data->prov_status = $request->prov_status;
            $data->prov_created_by = 1;
            $data->prov_created_date = date(now());
            $data->save();
        }else{
            DB::table('kka_dab.mst_Province')
                ->where('prov_id', $request->prov_id)
                ->update(['prov_name' => $request->prov_name,
                    'prov_bps_code' => $request->prov_bps_code,
                     'prov_status' => $request->prov_status,
                     'prov_updated_date' => date(now())]);
        }
        return response()->json(['success'=>'Province Type saved successfully.']);
    }

    public function destroy($prov_id)
    {
        Province::find($prov_id)->delete();
        return response()->json(['success'=>'Province Type deleted successfully.']);
    }
}
