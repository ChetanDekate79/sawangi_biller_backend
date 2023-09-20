<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use PDF;

class DynamicPDFController extends Controller
{
    function index()
    {
     $customer_data = $this->get_customer_data();
     return view('dynamic_pdf')->with('customer_data', $customer_data);
    }

    function pdf(Request $req)
    {
       // return $request->all();
     $pdf = \App::make('dompdf.wrapper');
     PDF::setOptions(['dpi' => 96, 'defaultFont' => 'sans-serif']);
     $pdf->setPaper('A3', 'landscape');
    // $pdf->loadHTML($this->convert_customer_data_to_html());
     $pdf->loadHTML($req->bi);

     return $pdf->stream();
    }

    function convert_customer_data_to_html()
    {
        $output  = '';
     return $output;
    }
}
