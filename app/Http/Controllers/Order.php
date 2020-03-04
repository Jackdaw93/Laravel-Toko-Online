<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Order extends Controller
{
    public function Order(Request $request) {
      DB::table('tbl_keranjang')->insert([
        'id_user'   => Session::get('id_user'),
        'id_barang' => $request->id_barang,
        'jumlah'    => $request->jumlah
      ]);
      return redirect('/');
    }

    public function Keranjang(Request $request) {
      $Keranjang = DB::table('keranjang')->get();
      return view('Keranjang',['keranjang' => $Keranjang]);
    }

    public function Checkout() {
      $id_check = rand().rand().rand();
      $total=0;
      $data = DB::table('tbl_keranjang')->where('id_user', Session::get('id_user'))->get();
      foreach ($data as $krj) {
        $barang = DB::table('tbl_barang')->where('id', $krj->id_barang)->get();
        foreach ($barang as $brg) {
          $total += ($krj->jumlah * $brg->harga);
          DB::table('detail_checkout')->insert([
            'id_checkout' => $id_check,
            'id_barang'   => $krj->id_barang,
            'jumlah'      => $krj->jumlah
          ]);
        }
      }
      DB::table('tbl_checkout')->insert([
        'id_checkout' => $id_check,
        'id_user'     => Session::get('id_user'),
        'total'       => $total
      ]);

      return redirect('/Checkout_List');
    }

    public function Checkout_List() {
      $checkout = DB::table('checkout')->get();
      return view('Checkout', ['checkout' => $checkout]);
    }

    public function Confirm() {
      return view('Confirm');
    }

    public function Confirm_Simpan(Request $request) {
      $this->validate($request, [
        'file' => 'required | max:2048'
      ]);
      $file = $request->file('file');
      $nama_file = time()."_". $file->getClientOriginalName();

      $tujuan_upload = 'data_file';
      if($file->move($tujuan_upload, $nama_file)) {
        DB::table('tbl_konfirmasi')->insert([
          'id_user'     => Session::get('id_user'),
          'id_checkout' => $request->id_token,
          'bukti'       => $nama_file
        ]);

        return redirect('/Confirm');
      }
    }
}
