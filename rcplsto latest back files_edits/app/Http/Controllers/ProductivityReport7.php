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

class ProductivityReport7  extends Controller
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
            $file_name = "Rc plasto, Nagpur productivity [DATE- ".$date."].pdf";
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

            <title> RC PLASTO TANKS AND PIPES PRIVATE LIMITED -'.$date.'</title>
                <style  type="text/css" media="all">
                    table {
                        font-family: arial, sans-serif;
                        border-collapse: collapse;
                        width: 100%;
                    }
                    td,th{
                        /*height:20px !important;*/
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
.w-50 {
    width: 30%;
}
.w-20 {
    width: 20%;
}

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
                       <img width="100px" src="'.$energy->plasto_logo.'" id=""> </span>
                        </td>
                    </tr>
                </table>
            </div>
            <center>
                <div>
                    <h2>RC PLASTO TANKS AND PIPES PRIVATE LIMITED</h2>
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
                            <tr class="table_title"><th colspan="6"><center> '.$value['device_category'].'</center> </th></tr>
                            <tr>
                                <th>Sr. No.</th>
                             <th class="w-50" >Machine Name</th>
                             <!--<th class="w-20" >DPR Machine Code</th>-->
                                <th>Eff</th>
                        <!--<th>Eff (Shifts)</th>-->
                                <th>Running Time</th>
                                  <th>Idle Time</th>
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
                        <th class="" >'.$value['idle_time'].'</th>
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
     $dt = new \DateTime($date); // <== instance from another API
       $carbon = Carbon::instance($dt);
       $old_date1=Carbon::parse($carbon)->format("Y-m-d");
       $now_date1=$carbon->addDays(1);
     $date= Carbon::parse($now_date1)->format("Y-m-d");
    return DB::select("
          SELECT c.device_name,
        c.device_category,
        c.device_idtt AS device_id,
        c.name,
          c.dpr_machine_code,
        sec_to_time(time_to_sec(c.total_total_time)+time_to_sec(c.total_idle_time)) as total_time,
        c.location_id,
        CONCAT(cast(c.dt1 AS DATE),' 7am -',cast(c.dt2 AS DATE),' 7am') as date,
          c.total_idle_time as idle_time,
            c.total_off_time as off_time,
            c.total_running_time as running_time,
    concat(round((time_to_sec(c.total_running_time)/(time_to_sec(c.total_total_time))) * 100),'%') as  eff,
     concat(round((time_to_sec(c.total_running_time)/59400) * 100),'%') as  eff2

from

(SELECT u.dt_time AS dt2,
 u.DEVICE_ID AS device_idtt,
 u.hour,
 u.total_time,
 u.running_time,
 u.off_time,
 p.dt_time AS dt1,
 p.DEVICE_ID,
 p.hour AS hour2,
 p.total_time AS total_time2,
 p.running_time AS running_time2,
 p.off_time AS off_time2,
 u.hour+p.hour AS total_hour,
 sec_to_time(time_to_sec(u.total_time)+time_to_sec(p.total_time)) AS total_total_time,
sec_to_time(time_to_sec(u.running_time)+time_to_sec(p.running_time)) AS total_running_time,
sec_to_time(time_to_sec(u.off_time)+time_to_sec(p.off_time)) AS total_off_time,
sec_to_time(time_to_sec(u.idle_time)+time_to_sec(p.idle_time)) AS total_idle_time,
u.device_name,
u.device_category,
u.name,
u.dpr_machine_code,
u.location_id

 
 from
(SELECT w.device_name,
max(g.DT_TIME) AS dt_time,
g.DEVICE_ID,
w.abbrivation_name AS name,
w.dpr_machine_code,
w.location_id,

CONCAT(w.location_id,':',w.device_category) as device_category,
g.date,
count(g.hour) AS hour,
sec_to_time(SUM(time_to_sec(g.total_time))) AS total_time,
sec_to_time(SUM(time_to_sec(g.running_time))) AS running_time,
sec_to_time(SUM(time_to_sec(g.off_time))) AS off_time,
sec_to_time(SUM(TIME_TO_SEC(g.idle_time))) AS idle_time
FROM productivity_data_logger_data_rs485_summary g
 join device_details_productivity w
 ON g.DEVICE_ID=w.device_id

  WHERe  g.date='$date' AND g.hour<=7
GROUP BY g.DEVICE_ID) u
JOIN 
(SELECT max(g.DT_TIME) AS dt_time,
g.DEVICE_ID,
g.date,
count(g.hour) AS hour,
sec_to_time(SUM(time_to_sec(g.total_time))) AS total_time,
sec_to_time(SUM(time_to_sec(g.running_time))) AS running_time,
sec_to_time(SUM(time_to_sec(g.off_time))) AS off_time,
sec_to_time(SUM(TIME_TO_SEC(g.idle_time))) AS idle_time



FROM productivity_data_logger_data_rs485_summary g

  WHERe  g.date=DATE_SUB('$date', INTERVAL 1 DAY) AND g.hour>7
GROUP BY g.DEVICE_ID) p
 ON u.DEVICE_ID=p.DEVICE_ID) c

        ");
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


