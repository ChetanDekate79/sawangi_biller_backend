<?php

namespace App\Http\Controllers;

use App\Mail\EnergyReport as MailEnergyReport;
use Carbon\Carbon;
//use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use App\model\energy;

class ProductivityReport  extends Controller
{
    //
    public function getReport($opration='download',$date='now',$doc='html',$type='try'){
        $now_date = Carbon::now()->format("Y-m-d");
        $old_date = Carbon::now()->subDays(1);
        $old_date = $old_date->format("Y-m-d");

        if($date== 'now' || $date == $now_date ){
            //$yesterday = date("Y-m-d");
           $date = $old_date;
        }
else if((explode('-', $date))[2] == '0'){
            //$yesterday = date("Y-m-d");
           $date = $old_date;
        }
        if($opration == 'download'){
            return $this->setProductivityReport($date,$doc);
        }
        else if($opration='mail') {
            $email = $this->getList(DB::select("select email from report_mail_to where productivity_report = 1 and $type = 1"));
            $file =  $this->setProductivityReport($date,'pdf');
            $file_name = "Safal | Samay | Sambhav , Sausar productivity [DATE- ".$date."].pdf";
            $this->send($email,$file,$file_name);
            return $this->successResponse($email);
        }
    }
    public function setProductivityReport($date,$doc){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');

         $report_data = $this->transformHeatmapData($this->ProductionMachinData($date));
     // return $report_data;
     // return    $this->genrateHTML($report_data);
        if($doc == 'pdf'){
            return $this->pdf($this->genrateHTML($report_data));
        }
        else
           return  $this->genrateHTML($report_data);

    }

    public function genrateHTML($arr){

		$energy = new energy();
        $data = json_decode(json_encode($arr), True);
        $date = $data['date'];
        $html_page['head'] = '
            <head>

            <title> Safal | Samay | Sambhav , Sausar-'.$date.'</title>
                <style  type="text/css" media="all">
                    table {
                        font-family: arial, sans-serif;
                        border-collapse: collapse;
                        width: 100%;
                    }
                    td, th{
                       /* height:20px !important;*/
                        line-height: 35px;
                    }
                    td, th {
                        border: 1px solid #dddddd;
                        text-align: left;
                        padding: 8px;
                    }
                    td, th {
                        position: relative;
                      }
                    .std{ border: 0px; !important}
                    thead{ background-color: #dddddd;}
                    .flex-container {
                        display: flex;
                      }
                        /*tr:nth-child(even) {
                            background-color: #dddddd;
                        }*/
                              .header-report{

                                top: -60px;
                                left: -60px;
                                right: -60px;

                                /** Extra personal styles **/
                                background-color: #d1fec5;
                                color: white;
                                text-align: center;
                                line-height: 35px;
                              }
                              table { overflow: visible !important; }
                              thead { display: table-header-group !important;  }
                              tfoot { display: table-row-group !important;  }
                              tr { page-break-inside: avoid !important; }
                     @page { margin: 50px 25px 25px 25px;}
                     .table_title{background-color:#C9D0CC; color:black; text-align: center; }
                     footer { position: fixed; bottom: -60px; left: 0px; right: 0px;}
                     .footer .page-number:after { content: counter(page); }
                     table{margin:20px;}
                     main { text-align: center;}
                     @page {
                        font-family: arial;
                        font-size:14px; !important;
                    }
                    .page_break { page-break-after: always; }
                    .text{
                        color:black;
                       /* font-weight:bold;*/
                    }
.w-50{
width:30%;}
.w-20{
width:20%;}
                </style>
            </head>
            <footer class="footer"  ><span class="page-number">[Page: </span> <span> ] </span>
             <span style="text-align:center padding:0 20%;">
             Heta Datain    www.hetadatain.com
             </span></footer>'
        ;
        $html_page['heading'] ='
            <div class=" header-report">
                <table>
                    <tr>
                        <td class="std" style="text-align: left;">
                            <span style="" >
                            <img   width="150px" src="'.$energy->heta_logo.'"  id=""></span>
                        </td>
                        <td  class="std txt-align" style="text-align: right;">
                            <span style=" ">
                        <img   width="100px" src="'.$energy->plasto_logo.'" id=""> </span>
                        </td>
                    </tr>
                </table>
            </div>
            <center>
                <div>
                    <h2>RC Plasto</h2>
                    <h3 > Productivity Report For Date - '.$date.' </h3>
                </div>
            </center><hr>';

        $html_page['heading_1'] ='<h3>Machine Production Time and Efficiency</h3>';
        $html_page['heading_2'] ='<h3>Main Feeders Consumption </h3>';
        $html_page['heading_3'] ='<h3>Sub-Feeders Consumption </h3>';

        $t_head1 = '';
        $t_body1 = '';
        $table1 = '';
        foreach($data['data'] as $key=>$value_data){
            $row_tbT1 = '';
            foreach($value_data as $index=>$value){
                $t_head1 ='<thead>
                            <tr class="table_title"><th colspan="5"><center> '.$value['device_category'].'</center> </th></tr>
                            <tr>
                                <th>Sr. No.</th>
                               <th class="w-50">Machine Name</th>
					<!--<th class="w-20">DPR Machine Code</th>-->

                                <th>Eff</th>
					<!--<th>Eff (Shifts)</th>-->
                                <th>Running Time</th>
                                <th>Off Time</th>
                            </tr>
                        </thead>';
                $row_tbT1 .=
                    '<tr ">
                        <th class="" >'.($index+1).'</th>
                        <th class="w-50" >'.$value['device_name'].'</th>
<!--<th class="w-20" >'.$value['dpr_machine_code'].'</th>-->

                        <th class="" >'.$value['eff'].'</th>
<!--<th class="" >'.$value['eff2'].'</th>-->
                        <th class="" >'.$value['running_time'].'</th>
                        <th class="" >'.$value['off_time'].'</th>
                    </tr>';
            }
            $t_body1 = '<tbody>'.$row_tbT1.'</tbody>';
            $table1 .= '<table class="">'.$t_head1.''.$t_body1.'</table><div></div>';
        }
         $html_page['body'] =
         '<body>'.$html_page['heading'].''
                .$html_page['heading_1'].''.$table1.'
                 </body>';
         $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';
    return $html_page['page'];

    }




public function ProductionMachinData($date){
    return DB::select("
        select device_name,
            concat(device_category,' : ',location_id) device_category,
            T1.device_id, abbrivation_name as name,
dpr_machine_code,
            (total_time) as total_time,
            location_id as location_id,
            date as date,
            (idle_time) as idle_time,
            (off_time) as off_time,
            (running_time) as running_time,
            concat(round((time_to_sec(running_time)/time_to_sec(total_time)) * 100),'%') as  eff,
concat(round((time_to_sec(running_time)/59400) * 100),'%') as  eff2
        from device_details_productivity T1
        join productivity_data_logger_summary T2 on
        T1.device_id = T2.device_id
        where cast(dt_time as date ) = cast('$date' as date)
        and T1.visibility = 1
        order by report_sort");
}

    function transformHeatmapData($data){
        $result = array();
        $label = array();
        $array = json_decode(json_encode($data), True);
        foreach($array as $k => $v) {
            $result[$v['device_category']][] = $v;
            $label[$v['device_category']] = $v['device_category'];
        }
        return [
            'data'=>$result,
            'label'=>$label,
            'date' => $array[0]['date']
        ];
    }
    function pdf($html)
    {
       // return $request->all();
     $pdf = \App::make('dompdf.wrapper');
     PDF::setOptions(['dpi' => 96,
                    'defaultFont' => 'sans-serif',
                    'isPhpEnabled' => true,
                    'isHtml5ParserEnabled'=> true,
                    'isRemoteEnabled' => true,
                    'isFontSubsettingEnabled'=> true]);
     $pdf->setPaper('A4', 'portrait');
    // $pdf->loadHTML($this->convert_customer_data_to_html());
     $pdf->loadHTML($html);

     return $pdf->stream();
    }



    public function send($email,$file,$file_name){
       return Mail::bcc($email)->send(new MailEnergyReport($file,$file_name));
    }
    public function successResponse($email){

        return response() -> json([
            'data' => 'Report sent Successfully',
            'email' => $email
        ], Response::HTTP_OK);
    }

    public function failedResponse(){
        return response() -> json([
            'error' => 'Email doesn\'t found in our database'
        ], Response::HTTP_NOT_FOUND);
    }

    function getList($data){
        $arr= [];
        foreach($data as $key=>$value){
            $arr[] = $value->email;
        }
        return $arr;
    }
}


