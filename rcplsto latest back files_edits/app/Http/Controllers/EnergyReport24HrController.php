<?php

namespace App\Http\Controllers;
use App\Mail\EnergyReport as MailEnergyReport;
use Carbon\Carbon;
use Exception;
//use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use App\model\energy;
class EnergyReport24HrController extends Controller
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
        if($opration == 'download'){
            return $this->setEnergyReport($date,$doc);
        }
        else if($opration='mail') {

            $email = $this->getList(DB::select("select email from report_mail_to where energy_report = 1 and $type = 1"));
            $file =  $this->setEnergyReport($date,'pdf');
            $file_name = "RC Plasto, Energy Report [DATE- ".$date."].pdf";
            $this->send($email,$file,$file_name);
            return $this->successResponse($email);
        }
    }

    public function setEnergyReport($date,$doc){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');

         $report_data = [
            'feeders' => $this->transform($this->commanAreaEnergy($date),)
        ];

      //return    $this->genrateHTML($report_data);
        if($doc == 'pdf'){
            //return  $this->genrateHTML($report_data);

            return $this->pdf($this->genrateHTML($report_data));
        }
        else
           return  $this->genrateHTML($report_data);

    }


public function genrateHTML($arr){

        $data2 = json_decode(json_encode($arr['feeders']), True);
      // print_r(json_encode($data2));
      // exit;
        $energy = new energy();
        $date = $data2['date'];
        $html_page['head'] = '
        <head>

        <title>  RC Plasto -'.$date.'</title>
            <style  type="text/css" media="all">
                table {
                    font-family: arial, sans-serif;
                    border-collapse: collapse;
                    width: 100%;
                }
                td{
                    /*height:20px !important;*/
                    line-height: 35px;
                }
                td, th {
                    border: 1px solid #aaaaaa;
                    text-align: left;
                    padding: 8px;
                }
                td, th {
                    position: relative;
                  }
                .std{ border: 0px; !important}
                thead{ background-color: #dddddd;line-height: 35px;}
                .calculated{ font-weight: bold;}
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
                            <img   width="150px" src= "'.$energy->heta_logo.'" id=""></span>
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
                    <h2>RC PLASTO,NAGPUR</h2>
                    <h3 >Hourly Energy Report For Date - '.$date.' </h3>
                </div>
            </center><hr>';
            ;
             $html_page['heading_0'] ='<h3>Incomers</h3>';
        $html_page['heading_1'] ='<h3>Main Feeders</h3>';
        $html_page['heading_2'] ='<h3>Sub Feeders </h3>';
        $html_page['heading_3'] ='<h3>Load Consumption</h3>';
        $t_head1 ='<thead >
                        <tr style="text-align:center !important;" >
                        <th>category</th>
                        <th>Device\Hour</th>
                        <th>1 (00:00 to 01:00)</th>
                        <th>2 (01:00 to 02:00)</th>
                        <th>3 (02:01 to 03:00)</th>
                        <th>4 (03:01 to 04:00)</th>
                        <th>5 (04:01 to 05:00)</th>
                        <th>6 (05:01 to 06:00)</th>
                        <th>7 (06:01 to 07:00)</th>
                        <th>8 (07:01 to 08:00)</th>
                        <th>9 (08:01 to 09:00)</th>
                        <th>10 (09:01 to 10:00)</th>
                        <th>11 (10:01 to 10:00)</th>
                        <th>12 (11:01 to 12:00)</th>
                        <th>13 (12:01 to 13:00)</th>
                        <th>14 (13:01 to 14:00)</th>
                        <th>15 (14:01 to 15:00)</th>
                        <th>16 (15:01 to 16:00)</th>
                        <th>17 (16:01 to 17:00)</th>
                        <th>18 (17:01 to 18:00)</th>
                        <th>19 (18:01 to 19:00)</th>
                        <th>20 (19:01 to 20:00)</th>
                        <th>21 (20:01 to 21:00)</th>
                        <th>22 (21:01 to 22:00)</th>
                        <th>23 (22:01 to 23:00)</th>
                        <th>24 (23:01 to 24:00)</th>
                        <th>TOTAL</th>

                        </tr>
                    </thead>';
        $t_body1 = '';
        $t_body2 = '';
         $t_body3 = '';
         $t_body4 = '';
        $r_tot = '';
        //$total_ver = array();

foreach($data2['data'][0] as $key=>$value){
            //print_r($value);
            $rowspan = count($value);
            $flag = true;
            foreach($value as $key1=>$value1){
                $hours = array_map(function($item) {
                    return $item['hour'];
                        }, $value1);
                $str_row = '';
                if($flag)
                {
                    $str_row =  '<td rowspan="'.$rowspan.'" >'.$data2['label'][1][$key].'</td>';
                    $flag = false;
                }

                $t_body1 .= '<tr style="background-color:'.$value1[0]['category_color'].'" class="text">'.$str_row.'
                        <td class="calculated">  '.$key1.'  </td>';
                $hour = 0;
                for($i=0; $i<=23; $i++){

                    if(in_array($i+1,$hours)){
                        $total_ver =  $value1[$hour]['row_total'];
                        $t_body1.='<td class="">'.$value1[$hour]['kwh'].'</td>';
                        $hour++;
                    }
                    else{
                        $t_body1.='<td class="">0</td>';
                    }
                }
                $t_body1.='<td  class="calculated">'.$total_ver.'</td>';
                $t_body1 .= '</tr>';
            }
        }
//new added above
        foreach($data2['data'][1] as $key=>$value){
            //print_r($value);
            $rowspan = count($value);
            $flag = true;
            foreach($value as $key1=>$value1){
                $hours = array_map(function($item) {
                    return $item['hour'];
                        }, $value1);
                $str_row = '';
                if($flag)
                {
                    $str_row =  '<td rowspan="'.$rowspan.'" >'.$data2['label'][1][$key].'</td>';
                    $flag = false;
                }

                $t_body2 .= '<tr style="background-color:'.$value1[0]['category_color'].'" class="text">'.$str_row.'
                        <td class="calculated">  '.$key1.'  </td>';
                $hour = 0;
                for($i=0; $i<=23; $i++){

                    if(in_array($i+1,$hours)){
                        $total_ver =  $value1[$hour]['row_total'];
                        $t_body2.='<td class="">'.$value1[$hour]['kwh'].'</td>';
                        $hour++;
                    }
                    else{
                        $t_body2.='<td class="">0</td>';
                    }
                }
                $t_body2.='<td  class="calculated">'.$total_ver.'</td>';
                $t_body2 .= '</tr>';
            }
        }
        //($data2['data'][2]);
        foreach($data2['data'][2] as $key=>$value){
            //print_r($value);
            $rowspan = count($value);
            $flag = true;
            foreach($value as $key1=>$value1){
               // print_r(json_encode($value1));
               $hours = array_map(function($item) {
                return $item['hour'];
                    }, $value1);
                $str_row = '';
                if($flag)
                {
                    $str_row =  '<td rowspan="'.$rowspan.'" >'.$data2['label'][2][$key].'</td>';
                    $flag = false;
                }

                $t_body3 .= '<tr style="background-color:'.$value1[0]['category_color'].'" class="text">'.$str_row.'
                        <td class="calculated">  '.$key1.'  </td>';

                $hour = 0;
                for($i=0; $i<=23; $i++){
                    try{
                        if(in_array($i+1,$hours)){
                            $total_ver =  $value1[$hour]['row_total'];
                            $t_body3.='<td class="">'.$value1[$hour]['kwh'].'</td>';
                            $hour++;
                        }
                        else{
                            $t_body2.='<td class="">0</td>';

                        }

                    }
                    catch(Exception $e){
                        return '<h2> Data Error</h2>';
                    }


                }
                $t_body3.='<td  class="calculated">'.$total_ver.'</td>';
                $t_body3 .= '</tr>';
            }
        }
        //  foreach($data2['data'][3] as $key=>$value){
        //     //print_r($value);
        //     $rowspan = count($value);
        //     $flag = true;
        //     foreach($value as $key1=>$value1){
        //        // print_r(json_encode($value1));
        //        $hours = array_map(function($item) {
        //         return $item['hour'];
        //             }, $value1);
        //         $str_row = '';
        //         if($flag)
        //         {
        //             $str_row =  '<td rowspan="'.$rowspan.'" >'.$data2['label'][3][$key].'</td>';
        //             $flag = false;
        //         }

        //         $t_body4 .= '<tr style="background-color:'.$value1[0]['category_color'].'" class="text">'.$str_row.'
        //                 <td class="calculated">  '.$key1.'  </td>';

        //         $hour = 0;
        //         for($i=0; $i<=23; $i++){
        //             try{
        //                 if(in_array($i+1,$hours)){
        //                     $total_ver =  $value1[$hour]['row_total'];
        //                     $t_body4.='<td class="">'.$value1[$hour]['kwh'].'</td>';
        //                     $hour++;
        //                 }
        //                 else{
        //                     $t_body4.='<td class="">0</td>';

        //                 }

        //             }
        //             catch(Exception $e){
        //                 return '<h2> Data Error</h2>';
        //             }


        //         }
        //         $t_body4.='<td  class="calculated">'.$total_ver.'</td>';
        //         $t_body4 .= '</tr>';
        //     }
        // }


         $t_body1 = '<tbody>'.$t_body1.'</tbody>';
         $t_body2 = '<tbody>'.$t_body2.'</tbody>';
         $t_body3 = '<tbody>'.$t_body3.'</tbody>';
          $t_body4 = '<tbody>'.$t_body4.'</tbody>';

         $table1 = '<table>'.$t_head1.''.$t_body1.'</table>';
         $table2 = '<table>'.$t_head1.''.$t_body2.'</table>';
          $table3 = '<table>'.$t_head1.''.$t_body3.'</table>';
            $table4 = '<table>'.$t_head1.''.$t_body4.'</table>';


         // $html_page['body'] =
         // '<body>'.$html_page['heading'].''
         //  .$html_page['heading_0'].''.$table1.'<div class="page_break"></div>'
         //        .$html_page['heading_1'].''.$table2.'<div class="page_break"></div>'
         //        .$html_page['heading_2'].''.$table3.'<div class="page_break"></div>
         //         '.$html_page['heading_3'].''.$table4.'<div class="page_break"></div>
         //         </body>';
         // $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';

            $html_page['body'] =
         '<body>'.$html_page['heading'].''
          .$html_page['heading_0'].''.$table1.'<div class="page_break"></div>'
                .$html_page['heading_1'].''.$table2.'<div class="page_break"></div>'
                .$html_page['heading_2'].''.$table3.'<div class="page_break"></div>
                 
                 </body>';
         $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';
    return $html_page['page'];

    }
//     public function genrateHTML($arr){

// 		$energy = new energy();
//         $data2 = json_decode(json_encode($arr['feeders']), True);
//       // print_r(json_encode($data2));
//       // exit;
// 		$logo = asset('/assets/hetalogo.png');
//         $date = $data2['date'];
//         $html_page['head'] = '
//         <head>

//         <title>  RC Plasto-'.$date.'</title>
//             <style  type="text/css" media="all">
//                 table {
//                     font-family: arial, sans-serif;
//                     border-collapse: collapse;
//                     width: 100%;
//                 }
//                 td{
//                     /*height:20px !important;*/
//                     line-height: 35px;
//                 }
//                 td, th {
//                     border: 1px solid #aaaaaa;
//                     text-align: left;
//                     padding: 8px;
//                 }
//                 td, th {
//                     position: relative;
//                   }
//                 .std{ border: 0px; !important}
//                 thead{ background-color: #dddddd;line-height: 35px;}
//                 .calculated{ font-weight: bold;}
//                 .flex-container {
//                     display: flex;
//                   }
//                     /*tr:nth-child(even) {
//                         background-color: #dddddd;
//                     }*/
//                           .header-report{

//                             top: -60px;
//                             left: -60px;
//                             right: -60px;

//                             /** Extra personal styles **/
//                             background-color: #d1fec5;
//                             color: white;
//                             text-align: center;
//                             line-height: 35px;
//                           }
//                           table { overflow: visible !important; }
//                           thead { display: table-header-group !important;  }
//                           tfoot { display: table-row-group !important;  }
//                           tr { page-break-inside: avoid !important; }
//                  @page { margin: 50px 25px 25px 25px;}
//                  .table_title{background-color:#C9D0CC; color:black; text-align: center; }
//                  footer { position: fixed; bottom: -60px; left: 0px; right: 0px;}
//                  .footer .page-number:after { content: counter(page); }
//                  table{margin:20px;}
//                  main { text-align: center;}
//                  @page {
//                     font-family: arial;
//                     font-size:14px; !important;
//                 }
//                 .page_break { page-break-after: always; }
//                 .text{
//                     color:black;
//                    /* font-weight:bold;*/
//                 }

//             </style>
//         </head>
//         <footer class="footer"  ><span class="page-number">[Page: </span> <span> ] </span>
//          <span style="text-align:center padding:0 20%;">
//          Heta Datain    www.hetadatain.com
//          </span></footer>'
//         ;
//         $html_page['heading'] ='
//             <div class=" header-report">
//                 <table>
//                     <tr>
//                         <td class="std" style="text-align: left;">
//                             <span style="" >
//                             <img   width="150px" src="'.$energy->heta_logo.'"  id=""></span>
//                         </td>
//                         <td  class="std txt-align" style="text-align: right;">
//                             <span style=" ">
//                      <img   width="100px" src="'.$energy->plasto_logo.'" id=""> </span>
//                         </td>
//                     </tr>
//                 </table>
//             </div>
//             <center>
//                 <div>
//                     <h2>RC Plasto</h2>
//                     <h3 >Hourly Energy Report For Date - '.$date.' </h3>
//                 </div>
//             </center><hr>';
//             ;
//         $html_page['heading_1'] ='<h3>Main Feeders</h3>';
//         $html_page['heading_2'] ='<h3>Sub Feeders </h3>';
//         $t_head1 ='<thead >
//                         <tr style="text-align:center !important;" >
//                         <th>category</th>
//                         <th>Device\Hour</th>
//                         <th>1 (00:00 to 01:00)</th>
//                         <th>2 (01:00 to 02:00)</th>
//                         <th>3 (02:01 to 03:00)</th>
//                         <th>4 (03:01 to 04:00)</th>
//                         <th>5 (04:01 to 05:00)</th>
//                         <th>6 (05:01 to 06:00)</th>
//                         <th>7 (06:01 to 07:00)</th>
//                         <th>8 (07:01 to 08:00)</th>
//                         <th>9 (08:01 to 09:00)</th>
//                         <th>10 (09:01 to 10:00)</th>
//                         <th>11 (10:01 to 10:00)</th>
//                         <th>12 (11:01 to 12:00)</th>
//                         <th>13 (12:01 to 13:00)</th>
//                         <th>14 (13:01 to 14:00)</th>
//                         <th>15 (14:01 to 15:00)</th>
//                         <th>16 (15:01 to 16:00)</th>
//                         <th>17 (16:01 to 17:00)</th>
//                         <th>18 (17:01 to 18:00)</th>
//                         <th>19 (18:01 to 19:00)</th>
//                         <th>20 (19:01 to 20:00)</th>
//                         <th>21 (20:01 to 21:00)</th>
//                         <th>22 (21:01 to 22:00)</th>
//                         <th>23 (22:01 to 23:00)</th>
//                         <th>24 (23:01 to 24:00)</th>
//                         <th>TOTAL</th>

//                         </tr>
//                     </thead>';
//         $t_body1 = '';
//         $t_body2 = '';
//         $r_tot = '';
//         //$total_ver = array();

//         foreach($data2['data'][1] as $key=>$value){
//             //print_r($value);
//             $rowspan = count($value);
//             $flag = true;
//             foreach($value as $key1=>$value1){
//                 $hours = array_map(function($item) {
//                     return $item['hour'];
//                         }, $value1);
//                 $str_row = '';
//                 if($flag)
//                 {
//                     $str_row =  '<td rowspan="'.$rowspan.'" >'.$data2['label'][1][$key].'</td>';
//                     $flag = false;
//                 }

//                 $t_body1 .= '<tr style="background-color:'.$value1[0]['category_color'].'" class="text">'.$str_row.'
//                         <td class="calculated">  '.$key1.'  </td>';
//                 $hour = 0;
//                 for($i=0; $i<=23; $i++){

//                     if(in_array($i+1,$hours)){
//                         $total_ver =  $value1[$hour]['row_total'];
//                         $t_body1.='<td class="">'.$value1[$hour]['kwh'].'</td>';
//                         $hour++;
//                     }
//                     else{
//                         $t_body1.='<td class="">0</td>';
//                     }
//                 }
//                 $t_body1.='<td  class="calculated">'.$total_ver.'</td>';
//                 $t_body1 .= '</tr>';
//             }
//         }
//         //($data2['data'][2]);
//         foreach($data2['data'][2] as $key=>$value){
//             //print_r($value);
//             $rowspan = count($value);
//             $flag = true;
//             foreach($value as $key1=>$value1){
//                // print_r(json_encode($value1));
//                $hours = array_map(function($item) {
//                 return $item['hour'];
//                     }, $value1);
//                 $str_row = '';
//                 if($flag)
//                 {
//                     $str_row =  '<td rowspan="'.$rowspan.'" >'.$data2['label'][2][$key].'</td>';
//                     $flag = false;
//                 }

//                 $t_body2 .= '<tr style="background-color:'.$value1[0]['category_color'].'" class="text">'.$str_row.'
//                         <td class="calculated">  '.$key1.'  </td>';

//                 $hour = 0;
//                 for($i=0; $i<=23; $i++){
//                     try{
//                         if(in_array($i+1,$hours)){
//                             $total_ver =  $value1[$hour]['row_total'];
//                             $t_body2.='<td class="">'.$value1[$hour]['kwh'].'</td>';
//                             $hour++;
//                         }
//                         else{
//                             $t_body2.='<td class="">0</td>';

//                         }

//                     }
//                     catch(Exception $e){
//                         return '<h2> Data Error</h2>';
//                     }


//                 }
//                 $t_body2.='<td  class="calculated">'.$total_ver.'</td>';
//                 $t_body2 .= '</tr>';
//             }
//         }
// ///

//          $t_body1 = '<tbody>'.$t_body1.'</tbody>';
//          $t_body2 = '<tbody>'.$t_body2.'</tbody>';

//          $table1 = '<table>'.$t_head1.''.$t_body1.'</table>';
//          //$table2 = '<table>'.$t_head1.''.$t_body2.'</table>';


//          $html_page['body'] =
//          '<body>'.$html_page['heading'].''
//                 .$html_page['heading_1'].''.$table1.'<div class="page_break"></div>
//                  </body>';
//          $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';

//     return $html_page['page'];

//     }



    public function commanAreaEnergy($date){

         return DB::select("
         select level_no,T1.device_id, device_name, T1.hour, cast(dt_time as date) as date ,
                kwh,round(col_total) as col_total , round(row_total) as row_total ,device_category as category , type,category_color,type as category_code
            from (SELECT device_id, hour, dt_time ,kwh
                from	 hour_summary
                    WHERE cast(DT_TIME as date) = '$date'
                ) T1  join
                (SELECT max(level_no) as level_no, max(category_color) as category_color, T2.device_id,max(device_name) as  device_name,
                    max(device_category) as device_category ,max(device_category_code) as type,
                    max(sort_order) as sort_order , sum(kwh) as  row_total
                    FROM hour_summary  T1
                    join device_details_energy T2 on T1.device_id = T2.device_id
                    WHERE cast(DT_TIME as date) = '$date'
                    and visibility='1'
                    group by device_id
                ) T2 on T1.device_id = T2.device_id
                left join
                (SELECT hour,sum(kwh)  as col_total
                    FROM hour_summary  T1
                    join device_details_energy T2 on T1.device_id = T2.device_id
                    WHERE cast(DT_TIME as date) = '$date'
                    and visibility='1'
                    and device_category_code = 'cat_1'
                    group by hour
                ) T3
            on T1.hour = T3.hour
            order by hour,level_no,type,T2.sort_order
        ");
    }

    function transform($data){
        $result = array();
        $label = array();
       // print_r(json_encode($data));
        //exit(0);
        $array = json_decode(json_encode($data), True);

        foreach($array as $k => $v) {

            $result[$v['level_no']][$v['category_code']][$v['device_name']][] = $v;
            $label[$v['level_no']][$v['category_code']] = $v['category'];
           // $result[$v['device_name']][] = $v;
        }
       // print_r($result);
        //exit
       // dd($result);
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
     PDF::setOptions(['dpi' => 200,
                    'defaultFont' => 'sans-serif',
                    'isPhpEnabled' => true,
                    'isHtml5ParserEnabled'=> true,
                    'isRemoteEnabled' => true,
                    'isFontSubsettingEnabled'=> true]);
     $pdf->setPaper('A3', 'landscape');
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



