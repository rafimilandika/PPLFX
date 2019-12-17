<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\DB;
use App\m_diagnosa;

class c_diagnosa extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gejala = m_diagnosa::all();
        return view("v_diagnosa", [
            'data' => $gejala
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_akun = Auth::user()->id;
        date_default_timezone_set('Asia/Jakarta');

        
        $coba = $request->gejala;
        if (!is_array($coba)) {        
             return Redirect()->route('diagnosa.index');
            //echo "HELLO";
        } else{
        for ($i=0; $i < count($coba) ; $i++) { 
            DB::insert("INSERT into konsultasi values (null,".$id_akun.",".$coba[$i].",1)");
        }

        return Redirect()->route('diagnosacoba');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        echo "berhasil";
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function cekdiagnosa(){
        $id_akun = Auth::user()->id;
        $status=1;
        
        // Mencari penyakit tertetu sesuai gejala yang dipilih
        $hasil = DB::SELECT("SELECT * FROM konsultasi as k INNER JOIN gejala as g on k.id_gejala = g.id_gejala INNER jOIN detail_penyakit as dp on g.id_gejala = dp.id_gejala WHERE id_akun = '$id_akun' AND k.status = '$status'");

        for ($i=0; $i < count($hasil); $i++) { 
            $waktu = date('Y-m-d H:i:s');
            $idp = $hasil[$i]->id_penyakit;
            $bobotg = $hasil[$i]->bobot_gejala;
            DB::INSERT("INSERT INTO cek_bobot (id_akun, id_penyakit, bobot_penyakit, waktu, status) VALUES ('$id_akun', '$idp', '$bobotg', '$waktu', '$status')");
        }

        // Menjumlah bobot dari gejala yang dipilih berdasarkan jenis penyakit
        $diag = DB::SELECT("SELECT id_penyakit, SUM(bobot_penyakit) AS total FROM cek_bobot WHERE id_akun = '$id_akun' AND status = '$status' GROUP BY id_penyakit");

        for ($i=0; $i <count($diag) ; $i++) { 
            $hasilp = $diag[$i]->id_penyakit;
            $hasilg = $diag[$i]->total;
            DB::INSERT("INSERT INTO hasil_diag (id_akun, id_penyakit, bobot_penyakit, waktu, status) VALUES ('$id_akun', '$hasilp', '$hasilg', '$waktu', '$status')");
        }

        // Mengambil penyakit dengang bobot terbesar
        $akhir = DB::SELECT("SELECT * FROM hasil_diag WHERE id_akun = '$id_akun' AND status = '$status' ORDER BY bobot_penyakit DESC LIMIT 1");
        $idPenyakit = $akhir[0]->id_penyakit;
        $bobotGejala = $akhir[0]->bobot_penyakit;
        $akhir2 = DB::SELECT("SELECT * FROM penyakit WHERE id_penyakit = '$idPenyakit'");
        $gejala2 = DB::SELECT("SELECT * FROM konsultasi k INNER JOIN gejala g on k.id_gejala=g.id_gejala WHERE status = 1");

        $namap = $akhir2[0]->nama_penyakit;
        $penangananp = $akhir2[0]->penanganan;
        $penjelasanp = $akhir2[0]->penjelasan;
        DB::INSERT("INSERT INTO riwayat (id_akun, nama_penyakit, bobot_penyakit, waktu) VALUES ('$id_akun', '$namap', '$hasilg', '$waktu')");


        return view('v_hasil_diagnosa',['namap'=> $namap,'penjelasanp'=>$penjelasanp, 'penangananp'=>$penangananp, 'gejala'=>$gejala2]);
        
        
    }

    public function updatestatus(){
        $id_akun = Auth::user()->id;
        DB::UPDATE("UPDATE cek_bobot SET status = 2 WHERE id_akun = '$id_akun'");
        DB::UPDATE("UPDATE hasil_diag SET status = 2 WHERE id_akun = '$id_akun'");
        DB::UPDATE("UPDATE konsultasi SET status = 2 WHERE id_akun = '$id_akun'");
        return view('home');
    }
}