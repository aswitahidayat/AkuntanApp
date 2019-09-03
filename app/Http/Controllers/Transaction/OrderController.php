<?php
/**
 * Created by PhpStorm.
 * User: Shandy
 * Date: 4/22/2019
 * Time: 7:30 PM
 */

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(Auth::user()->level == 'user') {
            Alert::info('Oopss..', 'Anda dilarang masuk ke area ini.');
            return redirect()->to('/');
        }

        $datas = User::paginate(10);

        return view('transaction.order.index', compact('datas'));
    }

    public function search(Request $request)
    {
        if ($request->ajax()) {
            $output = "";
            $usertypes = DB::table('dm_usertype')->where('usertype_nm', 'LIKE', '%' . $request->search . "%")->get();
            if ($usertypes) {
                foreach ($usertypes as $key => $usertype) {
                    $output .= '<tr>' .
                        '<th>' . $usertype->usertype_nm . '</th>' .
                        '<th>' . $usertype->usertype_status . '</th>' .
                        '<th> test </th>' .
                        '</tr>';
                }
                return Response($output);
            }
        }
    }
}
