<?php

namespace App\Http\Controllers;

use App\Mail\EnergyReport as MailEnergyReport;
use Carbon\Carbon;
use App\model\energy;   
//use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use PDF;

class EnergyReport7 extends Controller
{ 
    //

    public function getEnergyReport($opration='download',$date='now',$doc='html',$type='try'){
try{
    Cache::flush();
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
            return $this->setEnergyReport($date,$doc);
        }
        else if($opration='mail') {
            $email = $this->getList(DB::select("select email from report_mail_to where energy_report = 1 and $type = 1"));
            $file =  $this->setEnergyReport($date,'pdf');
            $file_name = "Safal | Samay | Sambhav , Sausar[DATE- ".$date."].pdf";
            $this->send($email,$file,$file_name);
            return $this->successResponse($email);
        }
}
catch(Exception $e){
    return $e->getMessage();
return '<h2 class="warning"> Something Went Wrong 
        Look like a Date or Category Not Selected
        </h2>';
}
    }
    public function setEnergyReport($date,$doc){
try{
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');

         $report_data = [
            'category' => $this->dailyCategoryEnergy($date),
            'feeders' => $this->transformHeatmapData($this->reportFeedersEnergy($date))
        ];
      //return $report_data;
     // return    $this->genrateHTML($report_data);
        if($doc == 'pdf'){
            return $this->pdf($this->genrateHTML($report_data));
        }
        else
           return  $this->genrateHTML($report_data);
}
catch(Exception $e){
return '<h2 class="warning"> Something Went Wrong 
        Look like a Date or Category Not Selected
        </h2>';
}

    }

    //worked

//      public function genrateHTML($arr){
// try{
//     $data = json_decode(json_encode($arr['category']), True);
//         $data2 = json_decode(json_encode($arr['feeders']), True);
//         //dd($data2);
//      $energy = new energy();
//         $date = $data2['date'];
//         $html_page['head'] = '
//             <head>

//             <title>RC Plasto '.$date.'</title>
//                 <style  type="text/css" media="all">
//                     table {
//                         font-family: arial, sans-serif;
//                         border-collapse: collapse;
//                         width: 100%;
//                     }
//                     td{
//                         /*height:20px !important;*/
//                         line-height: 35px;

//                     }
//                     td, th {
//                         border: 1px solid #dddddd;
//                         text-align: left;
//                         padding: 8px;
//                     }
//                     td, th {
//                         position: relative;
                    
//                       }
//                     .std{ border: 0px; !important}
//                     thead{ background-color: #dddddd;}
//                     .flex-container {
//                         display: flex;
//                       }
//                         /*tr:nth-child(even) {
//                             background-color: #dddddd;
//                         }*/
//                               .header-report{

//                                 top: -60px;
//                                 left: -60px;
//                                 right: -60px;

//                                 /** Extra personal styles **/
//                                 background-color: #d1fec5;
//                                 color: white;
//                                 text-align: center;
//                                 line-height: 35px;
//                               }
//                               table { overflow: visible !important; }
//                               thead { display: table-header-group !important;  }
//                               tfoot { display: table-row-group !important;  }
//                               tr { page-break-inside: avoid !important; }
//                      @page { margin: 50px 25px 25px 25px;}
//                      footer { position: fixed; bottom: -60px; left: 0px; right: 0px;}
//                      .footer .page-number:after { content: counter(page); }
//                      main { text-align: center;}
//                      @page {
//                         font-family: arial;
//                         font-size:14px; !important;
// /*page-break-before: always !important; */
//                     }
//                     .page_break {page-break-before: always !important; 
//  }
//                     .text{
//                         color:black;
//                        /* font-weight:bold;*/
//                     }
// .category{
//  width:15%;
//      border: 0px;

// }

// /*table{
// page-break-inside: avoid !important;
// break-inside: avoid-page !important;

// }


// tr ,td{
// page-break-inside: avoid !important;
// break-inside: avoid-page !important;
// }*/
//                 </style>
//             </head>
//             <footer class="footer"  ><span class="page-number">[Page: </span> <span> ] </span>
//              <span style="text-align:center padding:0 20%;">
//              Heta Datain    www.hetadatain.com
//              </span></footer>'
//         ;
//         $html_page['heading'] ='
//             <div class="header-report">
//                 <table>
//                     <tr>
//                         <td class="std" style="text-align: left;">
//                             <span style="" >
//                             <img   width="150px" src= "'.$energy->heta_logo.'" id=""></span>
//                         </td>
//                         <td  class="std txt-align" style="text-align: right;">
//                             <span style=" ">
//                        <img   width="100px" src="'.$energy->plasto_logo.'" id=""> </span>
//                         </td>
//                     </tr>
//                 </table>
//             </div>
//             <center>
//                 <div>
//                     <h2>RC Plasto</h2>
//                     <h3 > Energy Report For Date - '.$date.' </h3>
//                 </div>
//             </center><hr>';
//         $html_page['heading_1'] ='<h3>Total Consumption </h3>';
//         $html_page['heading_2'] ='<h3>Main Feeders Consumption </h3>';
//         $html_page['heading_3'] ='<h3>Sub-Feeders Consumption </h3>';
//          $html_page['heading_4'] ='<h3>Load Consumption </h3>';
//         $t_head1 ='<thead>
//                         <tr>
//                         <th>Name </th>
//                         <th> </th>
//                         <th>Today (Recieved/Delivered)</th>
//                         <th>Yesterday (Recieved/Delivered)</th>
//                         <th>This Month (Recieved/Delivered)</th>
//                         <th>This Year (Recieved/Delivered)</th>
//                        <!-- <th>Today Money</th>-->
//                         </tr>
//                     </thead>';
//         $t_head2 ='<thead>
//                         <tr>
//                             <th>Feeders Name</th>
//                             <th> </th>
//                             <th>Today(kwh)(Rec/Dev)</th>
//                             <th>Yesterday(kwh)(Rec/Dev)</th>
//                             <th>This Month(kwh)(Rec/Dev)</th>
//                             <th>This Year(kwh)(Rec/Dev)</th>
//                             <!--<th>Today Money</th>-->
//                         </tr>
//                     </thead>';
//         $t_body1 = '';
//         $t_body2 = '';
//         $t_body3 = '';
//         $t_body4 = '';
//         foreach($data as $key=>$value){
//             $t_body1.='
//             <tr style="background-color:'.$value['category_color'].'" class="text">
//                 <td rowspan="1" >'.$value['category'].'</td>
//                 <td> kwh </td>
//                 <td style="text-align: right;">'.$value['kwh'].'</td>
//                 <td style="text-align: right;">'.$value['yesterday_kwh'].'</td>
//                 <td style="text-align: right;">'.$value['month_kwh'].'</td>
//                 <td style="text-align: right;">'.$value['year_kwh'].'</td>
//                <!-- <td>'.round($value['kwh_expense']).'</td>-->
//             </tr>';
//         }

//        foreach($data2['data'][1] as $key=>$value){
//             $rowspan = count($value);
//             $flag = true;
//             foreach($value as $key1=>$value1){
//                 $str_row = '';
//                 if($flag)
//                 {
//                     $str_row =  '<td class="category"   rowspan="" >'.$data2['label'][1][$key].'</td>';
//                     $flag = false;
//                 }
// else{
//     $str_row =  '<td class="category"   rowspan=""></td>';

// }

// //echo $value1['name'];
//    if( $value1['device_id']==23){
//     $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
//     $pv=round(($value1['kwh']/739),2);
//     $kwh=round($value1['kwh']).'<b> ('.$pv.')</b>';
//    // yesterday
//     $pv2=round(($value1['yesterday_kwh']/739),2);
//     $yesterday_kwh=round($value1['yesterday_kwh']).'<b> ('.$pv2.')<b>';
//    }else{
//     if($value1['device_id']==26){
//         $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
//      $pv=round(($value1['kwh']/490),2);
//     $kwh=round($value1['kwh']).'<b> ('.$pv.')</b>';

//      // yesterday
//     $pv2=round(($value1['yesterday_kwh']/490),2);
//     $yesterday_kwh=round($value1['yesterday_kwh']).'<b> ('.$pv2.')<b>';
//    }else{
//      if($value1['device_id']==15){
//     $pv=round(($value1['kwh']/303),2);
//     $kwh=round($value1['kwh']).'<b> ('.$pv.')</b>';
//     $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
//      // yesterday
//     $pv2=round(($value1['yesterday_kwh']/303),2);
//     $yesterday_kwh=round($value1['yesterday_kwh']).'<b> ('.$pv2.')<b>';
//    }else{
//     $kwh=($value1['kwh']);
//     $yesterday_kwh=($value1['yesterday_kwh']);
//     $name=$value1['name'];
//    }
//    }
//    }
  
  

//                 $t_body2.='
//                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
//                 <td>  '.$name.'  </td>
//                 <td style="text-align: right;">'.$kwh.'</td>
//                 <td style="text-align: right;">'.$yesterday_kwh.'</td>
//                 <td style="text-align: right;">'.($value1['month_kwh']).'</td>
//                 <td style="text-align: right;">'.($value1['year_kwh']).'</td>
//                 <!--<td>'.round($value1['kwh_expense']).'</td>-->
//                 </tr>';
//             }
//         }
//         foreach($data2['data'][2] as $key=>$value){
//             $rowspan = count($value);
//             $flag = true;
//             foreach($value as $key1=>$value1){
//                 $str_row = '';
//                 if($flag)
//                 {
//                     $str_row =  '<td class="category"   rowspan=""  >'.$data2['label'][2][$key].'</td>';
//                     $flag = false;
//                 }else{
//     $str_row =  '<td class="category"   rowspan=""></td>';

// }

            
//                 $t_body3.='
//                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
//                 <td>  '.$value1['name'].'  </td>
//                 <td style="text-align: right;">'.round($value1['kwh']).'</td>
//                 <td style="text-align: right;">'.round($value1['yesterday_kwh']).'</td>
//                 <td style="text-align: right;">'.round($value1['month_kwh']).'</td>
//                 <td style="text-align: right;">'.round($value1['year_kwh']).'</td>
//                <!-- <td>'.round($value1['kwh_expense']).'</td>-->
//                 </tr>';
//             }
//         }
// //          foreach($data2['data'][3] as $key=>$value){
// //             $rowspan = count($value);
// //             $flag = true;
// //             foreach($value as $key1=>$value1){
// //                 $str_row = '';
// //                 if($flag)
// //                 {
// //                     $str_row =  '<td class="category"   rowspan=""  >'.$data2['label'][3][$key].'</td>';
// //                     $flag = false;
// //                 }else{
// //     $str_row =  '<td class="category"   rowspan=""></td>';

// // }

// //                 $t_body4.='
// //                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
// //                 <td>  '.$value1['name'].'  </td>
// //                 <td>'.$value1['kwh'].'</td>
// //                 <td>'.$value1['yesterday_kwh'].'</td>
// //                 <td>'.$value1['month_kwh'].'</td>
// //                 <td>'.$value1['year_kwh'].'</td>
// //                <!-- <td>'.round($value1['kwh_expense']).'</td>-->
// //                 </tr>';
// //             }
// //         }
//          $t_body1 = '<tbody>'.$t_body1.'</tbody>';
//          $t_body2 = '<tbody>'.$t_body2.'</tbody>';
//          $t_body3 = '<tbody>'.$t_body3.'</tbody>';
//           $t_body4 = '<tbody>'.$t_body4.'</tbody>';
//          $table1 = '<table>'.$t_head1.''.$t_body1.'</table>';
//          $table2 = '<table>'.$t_head2.''.$t_body2.'</table>';
//          $table3 = '<table>'.$t_head2.''.$t_body3.'</table>';
//          $table4 = '<table>'.$t_head2.''.$t_body4.'</table>';
//          // $html_page['body'] =
//          // '<body>'.$html_page['heading'].''
//          //        .$html_page['heading_1'].''.$table1.''
//          //         .$html_page['heading_2'].''.$table2.'<div class="page_break_none"><br></div>'.$html_page['heading_3'].''.$table3.'
//          //          <div class="page_break_none"><br></div>'.$html_page['heading_4'].''.$table4.'
                 

//          //           </body>';
//          // $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';

//           $html_page['body'] =
//          '<body>'.$html_page['heading'].''
//                 .$html_page['heading_1'].''.$table1.''
//                  .$html_page['heading_2'].''.$table2.'<div class="page_break_none"><br></div>'.$html_page['heading_3'].''.$table3.'
                 
                 

//                    </body>';
//          $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';
//     return $html_page['page'];
// }
// catch(Exception $e){
// return '<h2 class="warning"> Something Went Wrong 
//         Look like a Date or Category Not Selrcted
//         </h2>'; 
// }


//     }

    //worked

//    public function genrateHTML($arr){
// try{
//     $data = json_decode(json_encode($arr['category']), True);
//         $data2 = json_decode(json_encode($arr['feeders']), True);
//         //dd($data2);
//      $energy = new energy();
//         $date = $data2['date'];
//         $html_page['head'] = '
//             <head>

//             <title>RC Plasto '.$date.'</title>
//                 <style  type="text/css" media="all">
//                     table {
//                         font-family: arial, sans-serif;
//                         border-collapse: collapse;
//                         width: 100%;
//                     }
//                     td{
//                         /*height:20px !important;*/
//                         line-height: 35px;

//                     }
//                     td, th {
//                         border: 1px solid #dddddd;
//                         text-align: left;
//                         padding: 8px;
//                     }
//                     td, th {
//                         position: relative;
                    
//                       }
//                     .std{ border: 0px; !important}
//                     thead{ background-color: #dddddd;}
//                     .flex-container {
//                         display: flex;
//                       }
//                         /*tr:nth-child(even) {
//                             background-color: #dddddd;
//                         }*/
//                               .header-report{

//                                 top: -60px;
//                                 left: -60px;
//                                 right: -60px;

//                                 /** Extra personal styles **/
//                                 background-color: #d1fec5;
//                                 color: white;
//                                 text-align: center;
//                                 line-height: 35px;
//                               }
//                               table { overflow: visible !important; }
//                               thead { display: table-header-group !important;  }
//                               tfoot { display: table-row-group !important;  }
//                               tr { page-break-inside: avoid !important; }
//                      @page { margin: 50px 25px 25px 25px;}
//                      footer { position: fixed; bottom: -60px; left: 0px; right: 0px;}
//                      .footer .page-number:after { content: counter(page); }
//                      main { text-align: center;}
//                      @page {
//                         font-family: arial;
//                         font-size:14px; !important;
// /*page-break-before: always !important; */
//                     }
//                     .page_break {page-break-before: always !important; 
//  }
//                     .text{
//                         color:black;
//                        /* font-weight:bold;*/
//                     }
// .category{
//  width:15%;
//      border: 0px;

// }

// /*table{
// page-break-inside: avoid !important;
// break-inside: avoid-page !important;

// }


// tr ,td{
// page-break-inside: avoid !important;
// break-inside: avoid-page !important;
// }*/
//                 </style>
//             </head>
//             <footer class="footer"  ><span class="page-number">[Page: </span> <span> ] </span>
//              <span style="text-align:center padding:0 20%;">
//              Heta Datain    www.hetadatain.com
//              </span></footer>'
//         ;
//         $html_page['heading'] ='
//             <div class="header-report">
//                 <table>
//                     <tr>
//                         <td class="std" style="text-align: left;">
//                             <span style="" >
//                             <img   width="150px" src= "'.$energy->heta_logo.'" id=""></span>
//                         </td>
//                         <td  class="std txt-align" style="text-align: right;">
//                             <span style=" ">
//                        <img   width="100px" src="'.$energy->plasto_logo.'" id=""> </span>
//                         </td>
//                     </tr>
//                 </table>
//             </div>
//             <center>
//                 <div>
//                     <h2>RC Plasto</h2>
//                     <h3 > Energy Report For Date - '.$date.' </h3>
//                 </div>
//             </center><hr>';
//         $html_page['heading_1'] ='<h3>Total Consumption </h3>';
//         $html_page['heading_2'] ='<h3>Main Feeders Consumption </h3>';
//         $html_page['heading_3'] ='<h3>Sub-Feeders Consumption </h3>';
//          $html_page['heading_4'] ='<h3>Load Consumption </h3>';
//         $t_head1 ='<thead>
//                         <tr>
//                         <th>Name </th>
//                         <th> </th>
//                         <th>Today (Recieved/Delivered)</th>
//                         <th>Yesterday (Recieved/Delivered)</th>
//                         <th>This Month (Recieved/Delivered)</th>
//                         <th>This Year (Recieved/Delivered)</th>
//                        <!-- <th>Today Money</th>-->
//                         </tr>
//                     </thead>';
//         $t_head2 ='<thead>
//                         <tr>
//                             <th>Feeders Name</th>
//                             <th> </th>
//                             <th>Today(kwh)(Rec/Dev)</th>
//                             <th>Yesterday(kwh)(Rec/Dev)</th>
//                             <th>This Month(kwh)(Rec/Dev)</th>
//                             <th>This Year(kwh)(Rec/Dev)</th>
//                             <!--<th>Today Money</th>-->
//                         </tr>
//                     </thead>';
//         $t_body1 = '';
//         $t_body2 = '';
//         $t_body3 = '';
//         $t_body4 = '';
//         foreach($data as $key=>$value){
//             $t_body1.='
//             <tr style="background-color:'.$value['category_color'].'" class="text">
//                 <td rowspan="1" >'.$value['category'].'</td>
//                 <td> kwh </td>
//                 <td>'.$value['kwh'].'</td>
//                 <td>'.$value['yesterday_kwh'].'</td>
//                 <td>'.$value['month_kwh'].'</td>
//                 <td>'.$value['year_kwh'].'</td>
//                <!-- <td>'.round($value['kwh_expense']).'</td>-->
//             </tr>';
//         }

//        foreach($data2['data'][1] as $key=>$value){
//             $rowspan = count($value);
//             $flag = true;
//             foreach($value as $key1=>$value1){
//                 $str_row = '';
//                 if($flag)
//                 {
//                     $str_row =  '<td class="category"   rowspan="" >'.$data2['label'][1][$key].'</td>';
//                     $flag = false;
//                 }
// else{
//     $str_row =  '<td class="category"   rowspan=""></td>';

// }

// //echo $value1['name'];
//    if( $value1['device_id']==23){
//     $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
//     $pv=round(($value1['kwh']/739),2);
//     $kwh=$value1['kwh'].'<b> ('.$pv.')</b>';
//    // yesterday
//     $pv2=round(($value1['yesterday_kwh']/739),2);
//     $yesterday_kwh=$value1['yesterday_kwh'].' ('.$pv2.')';
//    }else{
//     if($value1['device_id']==26){
//         $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
//      $pv=round(($value1['kwh']/490),2);
//     $kwh=$value1['kwh'].'<b> ('.$pv.')</b>';

//      // yesterday
//     $pv2=round(($value1['yesterday_kwh']/490),2);
//     $yesterday_kwh=$value1['yesterday_kwh'].' ('.$pv2.')';
//    }else{
//      if($value1['device_id']==15){
//     $pv=round(($value1['kwh']/303),2);
//     $kwh=$value1['kwh'].'<b> ('.$pv.')</b>';
//     $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
//      // yesterday
//     $pv2=round(($value1['yesterday_kwh']/303),2);
//     $yesterday_kwh=$value1['yesterday_kwh'].' ('.$pv2.')';
//    }else{
//     $kwh=$value1['kwh'];
//     $yesterday_kwh=$value1['yesterday_kwh'];
//     $name=$value1['name'];
//    }
//    }
//    }
  
  

//                 $t_body2.='
//                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
//                 <td>  '.$name.'  </td>
//                 <td>'.$kwh.'</td>
//                 <td>'.$yesterday_kwh.'</td>
//                 <td>'.$value1['month_kwh'].'</td>
//                 <td>'.$value1['year_kwh'].'</td>
//                 <!--<td>'.round($value1['kwh_expense']).'</td>-->
//                 </tr>';
//             }
//         }
//         foreach($data2['data'][2] as $key=>$value){
//             $rowspan = count($value);
//             $flag = true;
//             foreach($value as $key1=>$value1){
//                 $str_row = '';
//                 if($flag)
//                 {
//                     $str_row =  '<td class="category"   rowspan=""  >'.$data2['label'][2][$key].'</td>';
//                     $flag = false;
//                 }else{
//     $str_row =  '<td class="category"   rowspan=""></td>';

// }

            
//                 $t_body3.='
//                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
//                 <td>  '.$value1['name'].'  </td>
//                 <td>'.$value1['kwh'].'</td>
//                 <td>'.$value1['yesterday_kwh'].'</td>
//                 <td>'.$value1['month_kwh'].'</td>
//                 <td>'.$value1['year_kwh'].'</td>
//                <!-- <td>'.round($value1['kwh_expense']).'</td>-->
//                 </tr>';
//             }
//         }
// //          foreach($data2['data'][3] as $key=>$value){
// //             $rowspan = count($value);
// //             $flag = true;
// //             foreach($value as $key1=>$value1){
// //                 $str_row = '';
// //                 if($flag)
// //                 {
// //                     $str_row =  '<td class="category"   rowspan=""  >'.$data2['label'][3][$key].'</td>';
// //                     $flag = false;
// //                 }else{
// //     $str_row =  '<td class="category"   rowspan=""></td>';

// // }

// //                 $t_body4.='
// //                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
// //                 <td>  '.$value1['name'].'  </td>
// //                 <td>'.$value1['kwh'].'</td>
// //                 <td>'.$value1['yesterday_kwh'].'</td>
// //                 <td>'.$value1['month_kwh'].'</td>
// //                 <td>'.$value1['year_kwh'].'</td>
// //                <!-- <td>'.round($value1['kwh_expense']).'</td>-->
// //                 </tr>';
// //             }
// //         }
//          $t_body1 = '<tbody>'.$t_body1.'</tbody>';
//          $t_body2 = '<tbody>'.$t_body2.'</tbody>';
//          $t_body3 = '<tbody>'.$t_body3.'</tbody>';
//           $t_body4 = '<tbody>'.$t_body4.'</tbody>';
//          $table1 = '<table>'.$t_head1.''.$t_body1.'</table>';
//          $table2 = '<table>'.$t_head2.''.$t_body2.'</table>';
//          $table3 = '<table>'.$t_head2.''.$t_body3.'</table>';
//          $table4 = '<table>'.$t_head2.''.$t_body4.'</table>';
//          // $html_page['body'] =
//          // '<body>'.$html_page['heading'].''
//          //        .$html_page['heading_1'].''.$table1.''
//          //         .$html_page['heading_2'].''.$table2.'<div class="page_break_none"><br></div>'.$html_page['heading_3'].''.$table3.'
//          //          <div class="page_break_none"><br></div>'.$html_page['heading_4'].''.$table4.'
                 

//          //           </body>';
//          // $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';

//           $html_page['body'] =
//          '<body>'.$html_page['heading'].''
//                 .$html_page['heading_1'].''.$table1.''
//                  .$html_page['heading_2'].''.$table2.'<div class="page_break_none"><br></div>'.$html_page['heading_3'].''.$table3.'
                 
                 

//                    </body>';
//          $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';
//     return $html_page['page'];
// }
// catch(Exception $e){
// return '<h2 class="warning"> Something Went Wrong 
//         Look like a Date or Category Not Selrcted
//         </h2>'; 
// }


//     }
    public function genrateHTML($arr){
try{
    $data = json_decode(json_encode($arr['category']), True);
        $data2 = json_decode(json_encode($arr['feeders']), True);
        //dd($data2);
     $energy = new energy();
        $date = $data2['date'];
        $html_page['head'] = '
            <head>

            <title>RC Plasto '.$date.'</title>
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
                     footer { position: fixed; bottom: -60px; left: 0px; right: 0px;}
                     .footer .page-number:after { content: counter(page); }
                     main { text-align: center;}
                     @page {
                        font-family: arial;
                        font-size:14px; !important;
/*page-break-before: always !important; */
                    }
                    .page_break {page-break-before: always !important; 
 }
                    .text{
                        color:black;
                       /* font-weight:bold;*/
                    }
.category{
 width:15%;
     border: 0px;

}

/*table{
page-break-inside: avoid !important;
break-inside: avoid-page !important;

}


tr ,td{
page-break-inside: avoid !important;
break-inside: avoid-page !important;
}*/
                </style>
            </head>
            <footer class="footer"  ><span class="page-number">[Page: </span> <span> ] </span>
             <span style="text-align:center padding:0 20%;">
             Heta Datain    www.hetadatain.com
             </span></footer>'
        ;
        $html_page['heading'] ='
            <div class="header-report">
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
                    <h2>RC Plasto</h2>
                    <h3 > Energy Report For Date - '.$date.' </h3>
                </div>
            </center><hr>';
        $html_page['heading_1'] ='<h3>Total Consumption </h3>';
        $html_page['heading_2'] ='<h3>Main Feeders Consumption </h3>';
        $html_page['heading_3'] ='<h3>Sub-Feeders Consumption </h3>';
         $html_page['heading_4'] ='<h3>Load Consumption </h3>';
        $t_head1 ='<thead>
                        <tr>
                        <th>Name </th>
                        <th> </th>
                        <th>Today <br><br> (Received/Delivered)</th>
                        <th>Yesterday<br><br> (Received/Delivered)</th>
                        <th>This Month <br><br> (Received/Delivered)</th>
                        <th>This Year <br><br> (Received/Delivered)</th>
                       <!-- <th>Today Money</th>-->
                        </tr>
                    </thead>';
        $t_head2 ='<thead>
                        <tr>
                            <th>Feeders Name</th>
                            <th> </th>
                            <th>Today(kwh)<br><br> (Received/Delivered)</th>
                            <th>Yesterday(kwh)<br><br> (Received/Delivered)</th>
                            <th>This Month(kwh)<br><br> (Received/Delivered)</th>
                            <th>This Year(kwh)<br><br> (Received/Delivered)</th>
                            <!--<th>Today Money</th>-->
                        </tr>
                    </thead>';
        $t_body1 = '';
        $t_body2 = '';
        $t_body3 = '';
        $t_body4 = '';
        foreach($data as $key=>$value){
            $t_body1.='
            <tr style="background-color:'.$value['category_color'].'" class="text">
                <td rowspan="1" >'.$value['category'].'</td>
                <td> kwh </td>
                <td style="text-align: right;">'.$value['kwh'].'</td>
                <td style="text-align: right;">'.$value['yesterday_kwh'].'</td>
                <td style="text-align: right;">'.$value['month_kwh'].'</td>
                <td style="text-align: right;">'.$value['year_kwh'].'</td>
               <!-- <td>'.round($value['kwh_expense']).'</td>-->
            </tr>';
        }

       foreach($data2['data'][1] as $key=>$value){
            $rowspan = count($value);
            $flag = true;
            foreach($value as $key1=>$value1){
                $str_row = '';
                if($flag)
                {
                    $str_row =  '<td class="category"   rowspan="" >'.$data2['label'][1][$key].'</td>';
                    $flag = false;
                }
else{
    $str_row =  '<td class="category"   rowspan=""></td>';

}

//echo $value1['name'];
    if( $value1['device_id']==23){
    $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
    $pv=round(($value1['kwh']/739),2);
   // dd($value1['kwh']);
    $kwh=round($value1['kwh']).'<b> ('.$pv.')</b>';
   // yesterday
    $pv2=round(($value1['yesterday_kwh']/739),2);
    $yesterday_kwh=round($value1['yesterday_kwh']).'<b> ('.$pv2.')<b>';
   }else{
    if($value1['device_id']==26){
        $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
     $pv=round(($value1['kwh']/490),2);
    $kwh=round($value1['kwh']).'<b> ('.$pv.')</b>';

     // yesterday
    $pv2=round(($value1['yesterday_kwh']/490),2);
    $yesterday_kwh=round($value1['yesterday_kwh']).'<b> ('.$pv2.')<b>';
   }else{
     if($value1['device_id']==15){
    $pv=round(($value1['kwh']/303),2);
    $kwh=round($value1['kwh']).'<b> ('.$pv.')</b>';
    $name=$value1['name'].' ( kWh / kW<sub>p</sub>)';
     // yesterday
    $pv2=round(($value1['yesterday_kwh']/303),2);
    $yesterday_kwh=round($value1['yesterday_kwh']).'<b> ('.$pv2.')<b>';
   }else{
    $kwh=($value1['kwh']);
    $yesterday_kwh=($value1['yesterday_kwh']);
    $name=$value1['name'];
   }
   }
   }
 //  dd($value1['year_kwh']);
  

                $t_body2.='
                <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
                <td>  '.$name.'  </td>
                <td style="text-align: right;">'.$kwh.'</td>
                <td style="text-align: right;">'.$yesterday_kwh.'</td>
                <td style="text-align: right;">'.$value1['month_kwh'].'</td>
                <td style="text-align: right;">'.$value1['year_kwh'].'</td>
                <!--<td>'.round($value1['kwh_expense']).'</td>-->
                </tr>';
            }
        }
        foreach($data2['data'][2] as $key=>$value){
            $rowspan = count($value);
            $flag = true;
            foreach($value as $key1=>$value1){
                $str_row = '';
                if($flag)
                {
                    $str_row =  '<td class="category"   rowspan=""  >'.$data2['label'][2][$key].'</td>';
                    $flag = false;
                }else{
    $str_row =  '<td class="category"   rowspan=""></td>';

}

            
                $t_body3.='
                <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
                <td>  '.$value1['name'].'  </td>
                <td style="text-align: right;">'.$value1['kwh'].'</td>
                <td style="text-align: right;">'.$value1['yesterday_kwh'].'</td>
                <td style="text-align: right;">'.$value1['month_kwh'].'</td>
                <td style="text-align: right;">'.$value1['year_kwh'].'</td>
               <!-- <td>'.round($value1['kwh_expense']).'</td>-->
                </tr>';
            }
        }
//          foreach($data2['data'][3] as $key=>$value){
//             $rowspan = count($value);
//             $flag = true;
//             foreach($value as $key1=>$value1){
//                 $str_row = '';
//                 if($flag)
//                 {
//                     $str_row =  '<td class="category"   rowspan=""  >'.$data2['label'][3][$key].'</td>';
//                     $flag = false;
//                 }else{
//     $str_row =  '<td class="category"   rowspan=""></td>';

// }

//                 $t_body4.='
//                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
//                 <td>  '.$value1['name'].'  </td>
//                 <td>'.$value1['kwh'].'</td>
//                 <td>'.$value1['yesterday_kwh'].'</td>
//                 <td>'.$value1['month_kwh'].'</td>
//                 <td>'.$value1['year_kwh'].'</td>
//                <!-- <td>'.round($value1['kwh_expense']).'</td>-->
//                 </tr>';
//             }
//         }
         $t_body1 = '<tbody>'.$t_body1.'</tbody>';
         $t_body2 = '<tbody>'.$t_body2.'</tbody>';
         $t_body3 = '<tbody>'.$t_body3.'</tbody>';
          $t_body4 = '<tbody>'.$t_body4.'</tbody>';
         $table1 = '<table>'.$t_head1.''.$t_body1.'</table>';
         $table2 = '<table>'.$t_head2.''.$t_body2.'</table>';
         $table3 = '<table>'.$t_head2.''.$t_body3.'</table>';
         $table4 = '<table>'.$t_head2.''.$t_body4.'</table>';
         // $html_page['body'] =
         // '<body>'.$html_page['heading'].''
         //        .$html_page['heading_1'].''.$table1.''
         //         .$html_page['heading_2'].''.$table2.'<div class="page_break_none"><br></div>'.$html_page['heading_3'].''.$table3.'
         //          <div class="page_break_none"><br></div>'.$html_page['heading_4'].''.$table4.'
                 

         //           </body>';
         // $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';

          $html_page['body'] =
         '<body>'.$html_page['heading'].''
                .$html_page['heading_1'].''.$table1.''
                 .$html_page['heading_2'].''.$table2.'<div class="page_break_none"><br></div>'.$html_page['heading_3'].''.$table3.'
                 
                 

                   </body>';
         $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';
    return $html_page['page'];
}
catch(Exception $e){
return '<h2 class="warning"> Something Went Wrong 
        Look like a Date or Category Not Selrcted
        </h2>'; 
}


    }



    public function dailyCategoryEnergy($date){
        $dt = new \DateTime($date); // <== instance from another API
       $carbon = Carbon::instance($dt);
       $old_date1=Carbon::parse($carbon)->format("Y-m-d");
       $now_date1=$carbon->addDays(1);
     $now_date1= Carbon::parse($now_date1)->format("Y-m-d");

//         return DB::select("
//        select
//         k.day AS date,
//         'kwh' AS expense_unit,
//         count(k.d1) AS no_of_feeders,
//         k.category_color,
//       k.device_category AS category,
//         k.device_category_code as category_code,
//         sum(k.unit) AS kwh,
//         null as net_kwh,
//         null as kwh_delivered,
//         null as kwh_expense ,
//         null as net_kwh_expense,
//         null as kwh_delivered_expense,

//          sum(p.unit) AS yesterday_kwh,
//         null as yesterday_net_kwh,
//         null as yesterday_kwh_delivered,
//         null as yesterday_kwh_expense,
//         null as yesterday_net_kwh_expense,
//         null as yesterday_kwh_delivered_expense,

//          sum(z.month_unit) AS month_kwh,
//         null as month_net_kwh ,
//         null as month_kwh_delivered ,
//         null as month_kwh_expense ,
//         null as month_net_kwh_expense,
//         null as month_kwh_delivered_expense,

//           sum(u.year_unit) AS year_kwh ,
//         null as year_net_kwh ,
//         null as year_kwh_delivered ,
//         null as year_kwh_expense ,
//         null as year_net_kwh_expense,
//         null as year_kwh_delivered_expense

//   from
//     (SELECT
//       CONCAT(cast(T2.date AS DATE),' 7:00am',' to ',cast(T1.dt_time AS DATE),' 7:00am') AS DAY,
      
//       T1.device_id AS d1,(T1.kwh) AS kwh1 ,T1.reading_RS485_30 AS READ1,T1.hour AS hr1,
//      T2.date AS yesday,T2.device_id AS d2,(T2.kwh) AS kwh2 ,T2.reading_RS485_30 AS READ2,T2.hour AS hr2,
//      ROUND((T1.reading_RS485_30-T2.reading_RS485_30),1) AS unit,
//      T3.device_name,T3.client_id,T3.device_category,T3.device_category_code,T3.category_color
//   from data_rs485_summary T1 
//   JOIN data_rs485_summary T2
//   ON   T1.hour=T2.hour AND T1.device_id=T2.device_id
//   JOIN device_details_energy T3
//   ON T1.device_id=T3.device_id
//   WHERE  cast(T1.dt_time AS DATE)='$now_date1' AND cast(T2.date AS DATE)=date_sub('$now_date1', interval 1 day)
//    and T1.hour=7 AND T2.hour=7) k
//     JOIN 
   
    
//     (SELECT
//       T1.dt_time AS day,T1.device_id AS d1,(T1.kwh) AS kwh1 ,T1.reading_RS485_30 AS READ1,T1.hour AS hr1,
//      T2.date AS yesday,T2.device_id AS d2,(T2.kwh) AS kwh2 ,T2.reading_RS485_30 AS READ2,T2.hour AS hr2,
//      ROUND((T1.reading_RS485_30-T2.reading_RS485_30),1) AS unit
//   from data_rs485_summary T1 
//   JOIN data_rs485_summary T2 
//   ON   T1.hour=T2.hour AND T1.device_id=T2.device_id
//   WHERE  cast(T1.dt_time AS DATE)='$old_date1' AND cast(T2.date AS DATE)=date_sub('$old_date1', interval 1 day)
//    and T1.hour=7 AND T2.hour=7) p
// ON k.d1=p.d1
//  JOIN 
//   (SELECT p.device_id AS d3,p.kwh,f.device_id,f.unit,f.unit-p.kwh AS month_unit
//    from
//    (SELECT device_id,round(sum(kwh),1) AS kwh
//     from data_rs485_summary 
//     WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
//     GROUP BY device_id) p
//     join
//     (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit
//      FROM data_rs485_summary T1
//      WHERE month(T1.date)=month('$now_date1') 
//      GROUP BY T1.device_id ) f
//      ON p.device_id=f.device_id) z
//      ON k.d1=z.d3
//     join
//     (
//      SELECT p.device_id AS d4,p.kwh,f.device_id,f.unit,f.unit-p.kwh AS year_unit
//    from
//    (SELECT device_id,round(sum(kwh),1) AS kwh
//     from data_rs485_summary 
//     WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
//     GROUP BY device_id) p
//     join
//     (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit
//      FROM data_rs485_summary T1
//      WHERE year(T1.date)=year('$now_date1') 
//      GROUP BY T1.device_id ) f
//      ON p.device_id=f.device_id
//     ) u
//     ON k.d1=u.d4
//     group by device_category_code
//             order by device_category_code
//                 ");

//      return DB::select("
//         select
//         k.day AS date,
//         'kwh' AS expense_unit,
//         count(k.d1) AS no_of_feeders,
//         k.category_color,
//       k.device_category AS category,
//         k.device_category_code as category_code,
//         k.unit AS kwh,
//         null as net_kwh,
//         null as kwh_delivered,
//         null as kwh_expense ,
//         null as net_kwh_expense,
//         null as kwh_delivered_expense,

//          p.unit AS yesterday_kwh,
//         null as yesterday_net_kwh,
//         null as yesterday_kwh_delivered,
//         null as yesterday_kwh_expense,
//         null as yesterday_net_kwh_expense,
//         null as yesterday_kwh_delivered_expense,

//          z.month_unit AS month_kwh,
//         null as month_net_kwh ,
//         null as month_kwh_delivered ,
//         null as month_kwh_expense ,
//         null as month_net_kwh_expense,
//         null as month_kwh_delivered_expense,

//       u.year_unit AS year_kwh ,
//         null as year_net_kwh ,
//         null as year_kwh_delivered ,
//         null as year_kwh_expense ,
//         null as year_net_kwh_expense,
//         null as year_kwh_delivered_expense

//   from
//     (SELECT
//       CONCAT(cast(T2.date AS DATE),' 7:00am',' to ',cast(T1.dt_time AS DATE),' 7:00am') AS DAY,
      
//       T1.device_id AS d1,(T1.kwh) AS kwh1 ,T1.reading_RS485_30 AS READ1,T1.hour AS hr1,
//      T2.date AS yesday,T2.device_id AS d2,(T2.kwh) AS kwh2 ,T2.reading_RS485_30 AS READ2,T2.hour AS hr2,
//      ROUND((T1.reading_RS485_30-T2.reading_RS485_30),1) AS unit,
//      T3.device_name,T3.client_id,T3.device_category,T3.device_category_code,T3.category_color
//   from data_rs485_summary T1 
//   JOIN data_rs485_summary T2
//   ON   T1.hour=T2.hour AND T1.device_id=T2.device_id
//   JOIN device_details_energy T3
//   ON T1.device_id=T3.device_id AND T3.level_no=0 AND T3.visibility=1
//   WHERE  cast(T1.dt_time AS DATE)='$now_date1' AND cast(T2.date AS DATE)=date_sub('$now_date1', interval 1 day)
//    and T1.hour=7 AND T2.hour=7 ORDER BY T3.report_sort) k
//     JOIN 
   
    
//     (SELECT
//       T1.dt_time AS day,T1.device_id AS d1,(T1.kwh) AS kwh1 ,T1.reading_RS485_30 AS READ1,T1.hour AS hr1,
//      T2.date AS yesday,T2.device_id AS d2,(T2.kwh) AS kwh2 ,T2.reading_RS485_30 AS READ2,T2.hour AS hr2,
//      ROUND((T1.reading_RS485_30-T2.reading_RS485_30),1) AS unit
//   from data_rs485_summary T1 
//   JOIN data_rs485_summary T2 
//   ON   T1.hour=T2.hour AND T1.device_id=T2.device_id
//     JOIN device_details_energy T3
//   ON T1.device_id=T3.device_id AND T3.level_no=0 AND T3.visibility=1
//   WHERE  cast(T1.dt_time AS DATE)='$old_date1' AND cast(T2.date AS DATE)=date_sub('$old_date1', interval 1 day)
//    and T1.hour=7 AND T2.hour=7) p
// ON k.d1=p.d1
//  JOIN 
//   (SELECT p.device_id AS d3,p.kwh,f.device_id,f.unit,f.unit-p.kwh AS month_unit
//    from
//    (SELECT device_id,round(sum(kwh),1) AS kwh
//     from data_rs485_summary 
//     WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
//     GROUP BY device_id) p
//     join
//     (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit
//      FROM data_rs485_summary T1
//      WHERE month(T1.date)=month('$now_date1') 
//      GROUP BY T1.device_id ) f
//      ON p.device_id=f.device_id) z
//      ON k.d1=z.d3
//     join
//     (
//      SELECT p.device_id AS d4,p.kwh,f.device_id,f.unit,f.unit-p.kwh AS year_unit
//    from
//    (SELECT device_id,round(sum(kwh),1) AS kwh
//     from data_rs485_summary 
//     WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
//     GROUP BY device_id) p
//     join
//     (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit
//      FROM data_rs485_summary T1
//      WHERE year(T1.date)=year('$now_date1') 
//      GROUP BY T1.device_id ) f
//      ON p.device_id=f.device_id
//     ) u
//     ON k.d1=u.d4
//      group by device_category_code
//             order by device_category_code
    
//       ");
//     }

//     public function monthCategoryEnergy($year,$month){
//         return DB::select("
//             select month(max(T1.date)) as month,
//                 year(max(T1.date)) as year,
//                 monthname(max(T1.date)) as month_name,
//                 max(category_color) as category_color,
//                 count(Distinct  T1.device_id) as no_of_feeders,
//                 round(sum(T1.rs485_1),1) as kw,
//                 round(sum(T1.rs485_30),1) as kwh,
//                 round(sum(T1.kwh_delivered),1) as kwh_delivered,
//                 round(sum(T1.net_kwh),1) as net_kwh,
//                 round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
//                 round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,
//                 max(device_category) as category,
//                 max(device_category_code) as category_code,
//                 max(unit) as expense_unit,
//                 round(sum(T1.rs485_1 * rate),1) as kw_expense,
//                 round(sum(T1.rs485_30 * rate),1) as kwh_expense,
//                 round(sum(T1.import_kw),1) as import_kw,
//                 round(sum(T1.import_kw * rate),1) as import_kw_expense,
//                 round(sum(T1.export_kw),1) as export_kw,
//                 round(sum(T1.export_kw * rate),1) as export_kw_expense
//             from data_rs485_summary T1  join device_details T2
//             on T1.device_id = T2.device_id

//             cross join rate_config on type ='money'
//             where device_category_level=1 and visibility = 1 and device_category_code != 'cat_2'
//             and year(T1.date) = $year
//             and month(T1.date) = $month
//             group by device_category_code
//             order by device_category_code;
//         ");
 return DB::select("
        
   
    
     select
        k.date AS date,
        k.dname AS name,
        k.d1 AS device_id,
        'kwh' AS expense_unit,
        (k.d1) AS no_of_feeders,
         if(k.level_no = 1,'feeder','sub-feeder') as sld,
        if(k.device_category_code = 'cat_1', 0 ,1) as indexs,
        k.level_no,
        k.category_color,
      k.device_category AS category,
        k.device_category_code as category_code,
        k.report_sort,
        concat(round(k.kwh),' /(',round(k.kwh_delivered),')') AS kwh ,
      
        null as net_kwh,
        (k.kwh_delivered) AS  kwh_delivered,
        null as kwh_expense ,
        null as net_kwh_expense,
        null as kwh_delivered_expense,
      
           concat(round(w.kwh),' /(',round(w.kwh_delivered),')') AS  yesterday_kwh,
        null as yesterday_net_kwh,
        (w.kwh_delivered) as yesterday_kwh_delivered,
        null as yesterday_kwh_expense,
        null as yesterday_net_kwh_expense,
        null as yesterday_kwh_delivered_expense,

         concat(round(z.month_unit),' /(',round(z.kwh_delivered_month),')') AS month_kwh,
        null as month_net_kwh ,
        (z.kwh_delivered_month) as month_kwh_delivered ,
        null as month_kwh_expense ,
        null as month_net_kwh_expense,
        null as month_kwh_delivered_expense,

         concat(round(u.year_unit),' /(',round(u.kwh_delivered_year),')') AS year_kwh ,
        null as year_net_kwh ,
        (u.kwh_delivered_year) as year_kwh_delivered ,
        null as year_kwh_expense ,
        null as year_net_kwh_expense,
        null as year_kwh_delivered_expense

  
     from
   (SELECT CONCAT(cast(p.dt_time AS DATE),' 7:00am',' to ',cast(f.dt_time AS DATE),' 7:00am') AS date,
    p.d1 AS d1,p.kwh1,f.device_id,
    f.kwh2,kwh1+kwh2 AS kwh,p.c1+f.c2 AS hr,
    p.kwh_delivered1+f.kwh_delivered2 as kwh_delivered,
     client_id,device_category,
      device_category_code,category_color,level_no,report_sort,dname
   from
   (SELECT     
     T3.device_name AS dname, T3.device_id AS d1,T1.dt_time,
     T3.client_id,T3.device_category,
      T3.device_category_code,T3.category_color,T3.level_no,T3.report_sort, 
     round(sum(kwh),1) AS kwh1,round(SUM(kwh_delivered),1) AS kwh_delivered1,COUNT(hour) AS c1
    from data_rs485_summary T1 
      JOIN device_details_energy T3
  ON T1.device_id=T3.device_id 
    WHERE   cast(dt_time AS DATE)='$now_date1' AND hour<=7
    GROUP BY T1.device_id) p 
    join
    (SELECT device_id,dt_time,round(sum(kwh),1) AS kwh2,round(SUM(kwh_delivered),1) AS kwh_delivered2,COUNT(hour) AS c2
    from data_rs485_summary 
    WHERE   cast(dt_time AS DATE)=date_sub('$now_date1', interval 1 day) AND hour>7
    GROUP BY device_id) f
    ON p.d1=f.device_id) k
    
    join
    (
    SELECT CONCAT(cast(p.dt_time AS DATE),' 7:00am',' to ',cast(f.dt_time AS DATE),' 7:00am') AS date,
    p.d1 AS d2,p.kwh1,f.device_id,
    f.kwh2,kwh1+kwh2 AS kwh,p.c1+f.c2 AS hr,
    p.kwh_delivered1+f.kwh_delivered2 as kwh_delivered,
     client_id,device_category,
      device_category_code,category_color,level_no,report_sort,dname
   from
   (SELECT     
     T3.device_name AS dname, T3.device_id AS d1,T1.dt_time,
     T3.client_id,T3.device_category,
      T3.device_category_code,T3.category_color,T3.level_no,T3.report_sort, 
     round(sum(kwh),1) AS kwh1,round(SUM(kwh_delivered),1) AS kwh_delivered1,COUNT(hour) AS c1
    from data_rs485_summary T1 
      JOIN device_details_energy T3
  ON T1.device_id=T3.device_id 
    WHERE   cast(dt_time AS DATE)='$old_date1' AND hour<=7
    GROUP BY T1.device_id) p 
    join
    (SELECT device_id,dt_time,round(sum(kwh),1) AS kwh2,
     round(SUM(kwh_delivered),1) AS kwh_delivered2,COUNT(hour) AS c2
    from data_rs485_summary 
    WHERE   cast(dt_time AS DATE)=date_sub('$old_date1', interval 1 day) AND hour>7
    GROUP BY device_id) f
    ON p.d1=f.device_id)  w
     on k.d1=w.d2
     JOIN 
  (SELECT p.device_id AS d3,p.kwh,f.device_id,f.unit,f.unit+p.kwh AS month_unit,
     p.kwh_d1+f.kwh_d2 as kwh_delivered_month
   from
   (SELECT device_id,round(sum(kwh),1) AS kwh,round(SUM(e.kwh_delivered),1) AS kwh_d1
    from data_rs485_summary e
    WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
    GROUP BY device_id) p
    join
    (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit,
    round(SUM(kwh_delivered),1) AS kwh_d2
     FROM data_rs485_summary T1
     JOIN device_details_energy T3
  ON T1.device_id=T3.device_id 
     WHERE month(T1.date)=month('$now_date1') AND T3.client_id!='v'
     GROUP BY T1.device_id ) f
     ON p.device_id=f.device_id) z
     ON k.d1=z.d3
    join
    (
     SELECT p.device_id AS d4,p.kwh,f.device_id,f.unit,f.unit+p.kwh AS year_unit,
     p.kwh_d1+f.kwh_d2 as kwh_delivered_year
   from
   (SELECT device_id,round(sum(kwh),1) AS kwh,round(SUM(e.kwh_delivered),1) AS kwh_d1
    from data_rs485_summary e
    WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
    GROUP BY device_id) p
    join
    (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit,
    round(SUM(kwh_delivered),1) AS kwh_d2
     FROM data_rs485_summary T1
     JOIN device_details_energy T3
  ON T1.device_id=T3.device_id 
     WHERE year(T1.date)=year('$now_date1') AND T3.client_id!='v'
     GROUP BY T1.device_id ) f
     ON p.device_id=f.device_id
    ) u
    ON k.d1=u.d4
     WHERE  k.level_no=0 AND k.device_id!=12
    ORDER BY k.report_sort");
    }

    public function yearCategoryEnergy($year){
        return DB::select("
            select
                year(max(T1.date)) as year,
                max(category_color) as category_color,
                count(Distinct  T1.device_id) as no_of_feeders,
                round(sum(T1.rs485_1),1) as kw,
                round(sum(T1.rs485_30),1) as kwh,
                round(sum(T1.kwh_delivered),1) as kwh_delivered,
                round(sum(T1.net_kwh),1) as net_kwh,
                round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
                round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,
                max(device_category) as category,
                max(device_category_code) as category_code,
                max(unit) as expense_unit,
                round(sum(T1.rs485_1 * rate),1) as kw_expense,
                round(sum(T1.rs485_30 * rate),1) as kwh_expense,
                round(sum(T1.import_kw),1) as import_kw,
                round(sum(T1.import_kw * rate),1) as import_kw_expense,
                round(sum(T1.export_kw),1) as export_kw,
                round(sum(T1.export_kw * rate),1) as export_kw_expense
            from data_rs485_summary T1  join device_details T2
            on T1.device_id = T2.device_id

            cross join rate_config on type ='money'
            where device_category_level=1 and visibility = 1 and device_category_code != 'cat_2'
                and year(T1.date) = $year

            group by device_category_code
            order by device_category_code;
        ");

    }

//************************ feeders *************************************** */
//************************************************************************ */
//************************ feeders *************************************** */
//************************************************************************ */
public function reportFeedersEnergy($date){
    $dt = new \DateTime($date); // <== instance from another API
       $carbon = Carbon::instance($dt);
       $old_date1=Carbon::parse($carbon)->format("Y-m-d");
       $now_date1=$carbon->addDays(1);
     $now_date1= Carbon::parse($now_date1)->format("Y-m-d");
//    return DB::select("
//          select
//         k.day AS date,
//         k.dname AS name,
//         k.d1 AS device_id,
//         'kwh' AS expense_unit,
//         (k.d1) AS no_of_feeders,
//          if(k.level_no = 1,'feeder','sub-feeder') as sld,
//         if(k.device_category_code = 'cat_1', 0 ,1) as indexs,
//         k.level_no,
//         k.category_color,
//       k.device_category AS category,
//         k.device_category_code as category_code,
//         k.report_sort,
//         (k.unit) AS kwh,
//         null as net_kwh,
//         null as kwh_delivered,
//         null as kwh_expense ,
//         null as net_kwh_expense,
//         null as kwh_delivered_expense,

//         (p.unit) AS yesterday_kwh,
//         null as yesterday_net_kwh,
//         null as yesterday_kwh_delivered,
//         null as yesterday_kwh_expense,
//         null as yesterday_net_kwh_expense,
//         null as yesterday_kwh_delivered_expense,

//         (z.month_unit) AS month_kwh,
//         null as month_net_kwh ,
//         null as month_kwh_delivered ,
//         null as month_kwh_expense ,
//         null as month_net_kwh_expense,
//         null as month_kwh_delivered_expense,

//         (u.year_unit) AS year_kwh ,
//         null as year_net_kwh ,
//         null as year_kwh_delivered ,
//         null as year_kwh_expense ,
//         null as year_net_kwh_expense,
//         null as year_kwh_delivered_expense

//   from
//     (SELECT
//       CONCAT(cast(T2.date AS DATE),' 7:00am',' to ',cast(T1.dt_time AS DATE),' 7:00am') AS DAY,
//       T3.device_name AS dname,
//       T1.device_id AS d1,(T1.kwh) AS kwh1 ,T1.reading_RS485_30 AS READ1,T1.hour AS hr1,
//      T2.date AS yesday,T2.device_id AS d2,(T2.kwh) AS kwh2 ,T2.reading_RS485_30 AS READ2,T2.hour AS hr2,
//      ROUND((T1.reading_RS485_30-T2.reading_RS485_30),1) AS unit,
//      T3.device_name,T3.client_id,T3.device_category,T3.device_category_code,T3.category_color,T3.level_no,T3.report_sort
//   from data_rs485_summary T1 
//   JOIN data_rs485_summary T2
//   ON   T1.hour=T2.hour AND T1.device_id=T2.device_id
//   JOIN device_details_energy T3
//   ON T1.device_id=T3.device_id
//   WHERE  cast(T1.dt_time AS DATE)='$now_date1' AND cast(T2.date AS DATE)=date_sub('$now_date1', interval 1 day)
//    and T1.hour=7 AND T2.hour=7) k
//     JOIN 
   
    
//     (SELECT
//       T1.dt_time AS day,T1.device_id AS d1,(T1.kwh) AS kwh1 ,T1.reading_RS485_30 AS READ1,T1.hour AS hr1,
//      T2.date AS yesday,T2.device_id AS d2,(T2.kwh) AS kwh2 ,T2.reading_RS485_30 AS READ2,T2.hour AS hr2,
//      ROUND((T1.reading_RS485_30-T2.reading_RS485_30),1) AS unit
//   from data_rs485_summary T1 
//   JOIN data_rs485_summary T2 
//   ON   T1.hour=T2.hour AND T1.device_id=T2.device_id
//   WHERE  cast(T1.dt_time AS DATE)='$old_date1' AND cast(T2.date AS DATE)=date_sub('$old_date1', interval 1 day)
//    and T1.hour=7 AND T2.hour=7) p
// ON k.d1=p.d1
//  JOIN 
//   (SELECT p.device_id AS d3,p.kwh,f.device_id,f.unit,f.unit-p.kwh AS month_unit
//    from
//    (SELECT device_id,round(sum(kwh),1) AS kwh
//     from data_rs485_summary 
//     WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
//     GROUP BY device_id) p
//     join
//     (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit
//      FROM data_rs485_summary T1
//      WHERE month(T1.date)=month('$now_date1') 
//      GROUP BY T1.device_id ) f
//      ON p.device_id=f.device_id) z
//      ON k.d1=z.d3
//     join
//     (
//      SELECT p.device_id AS d4,p.kwh,f.device_id,f.unit,f.unit-p.kwh AS year_unit
//    from
//    (SELECT device_id,round(sum(kwh),1) AS kwh
//     from data_rs485_summary 
//     WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
//     GROUP BY device_id) p
//     join
//     (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit
//      FROM data_rs485_summary T1
//      WHERE year(T1.date)=year('$now_date1') 
//      GROUP BY T1.device_id ) f
//      ON p.device_id=f.device_id
//     ) u
//     ON k.d1=u.d4
//      ORDER BY k.report_sort
//         ");
     return DB::select("

   
    
    select
        k.date AS date,
        k.dname AS name,
        k.d1 AS device_id,
        'kwh' AS expense_unit,
        (k.d1) AS no_of_feeders,
         if(k.level_no = 1,'feeder','sub-feeder') as sld,
        if(k.device_category_code = 'cat_1', 0 ,1) as indexs,
        k.level_no,
        k.category_color,
      k.device_category AS category,
        k.device_category_code as category_code,
        k.report_sort,
        (k.kwh) AS kwh,
        null as net_kwh,
        if(k.device_id IN (19,20,21),concat(round(k.kwh),' / (',round(k.kwh_delivered),')'),round(k.kwh)) AS kwh ,
        round(k.kwh_delivered) AS  kwh_delivered,
        null as kwh_expense ,
        null as net_kwh_expense,
        null as kwh_delivered_expense,

      if(w.device_id IN (19,20,21),concat(round(w.kwh),' / (',round(w.kwh_delivered),')'),round(w.kwh)) AS yesterday_kwh,
        null as yesterday_net_kwh,
        round(w.kwh_delivered) as yesterday_kwh_delivered,
        null as yesterday_kwh_expense,
        null as yesterday_net_kwh_expense,
        null as yesterday_kwh_delivered_expense,

 if(z.device_id IN (19,20,21),concat(round(z.month_unit),' / (',round(z.kwh_delivered_month),')'),round(z.month_unit))  AS month_kwh,
        null as month_net_kwh ,
        round(z.kwh_delivered_month) as month_kwh_delivered ,
        null as month_kwh_expense ,
        null as month_net_kwh_expense,
        null as month_kwh_delivered_expense,

if(u.device_id IN (19,20,21),concat(round(u.year_unit),' / (',round(u.kwh_delivered_year),')'),round(u.kwh)) AS year_kwh ,
        null as year_net_kwh ,
        round(u.kwh_delivered_year) as year_kwh_delivered ,
        null as year_kwh_expense ,
        null as year_net_kwh_expense,
        null as year_kwh_delivered_expense

  
     from
   (SELECT CONCAT(cast(f.dt_time AS DATE),' 7:00am',' to ',cast(p.dt_time AS DATE),' 7:00am') AS date,
    p.d1 AS d1,p.kwh1,f.device_id,
    f.kwh2,kwh1+kwh2 AS kwh,p.c1+f.c2 AS hr,
    p.kwh_delivered1+f.kwh_delivered2 as kwh_delivered,
     client_id,device_category,
      device_category_code,category_color,level_no,report_sort,dname
   from
   (SELECT     
     T3.device_name AS dname, T3.device_id AS d1,T1.dt_time,
     T3.client_id,T3.device_category,
      T3.device_category_code,T3.category_color,T3.level_no,T3.report_sort, 
     round(sum(kwh),1) AS kwh1,round(SUM(kwh_delivered),1) AS kwh_delivered1,COUNT(hour) AS c1
    from data_rs485_summary T1 
      JOIN device_details_energy T3
  ON T1.device_id=T3.device_id 
    WHERE   cast(dt_time AS DATE)='$now_date1' AND hour<=7
    GROUP BY T1.device_id) p 
    join
    (SELECT device_id,dt_time,round(sum(kwh),1) AS kwh2,round(SUM(kwh_delivered),1) AS kwh_delivered2,COUNT(hour) AS c2
    from data_rs485_summary 
    WHERE   cast(dt_time AS DATE)=date_sub('$now_date1', interval 1 day) AND hour>7
    GROUP BY device_id) f
    ON p.d1=f.device_id) k
    
    join
    (
    SELECT CONCAT(cast(p.dt_time AS DATE),' 7:00am',' to ',cast(f.dt_time AS DATE),' 7:00am') AS date,
    p.d1 AS d2,p.kwh1,f.device_id,
    f.kwh2,kwh1+kwh2 AS kwh,p.c1+f.c2 AS hr,
    p.kwh_delivered1+f.kwh_delivered2 as kwh_delivered,
     client_id,device_category,
      device_category_code,category_color,level_no,report_sort,dname
   from
   (SELECT     
     T3.device_name AS dname, T3.device_id AS d1,T1.dt_time,
     T3.client_id,T3.device_category,
      T3.device_category_code,T3.category_color,T3.level_no,T3.report_sort, 
     round(sum(kwh),1) AS kwh1,round(SUM(kwh_delivered),1) AS kwh_delivered1,COUNT(hour) AS c1
    from data_rs485_summary T1 
      JOIN device_details_energy T3
  ON T1.device_id=T3.device_id 
    WHERE   cast(dt_time AS DATE)='$old_date1' AND hour<=7
    GROUP BY T1.device_id) p 
    join
    (SELECT device_id,dt_time,round(sum(kwh),1) AS kwh2,
     round(SUM(kwh_delivered),1) AS kwh_delivered2,COUNT(hour) AS c2
    from data_rs485_summary 
    WHERE   cast(dt_time AS DATE)=date_sub('$old_date1', interval 1 day) AND hour>7
    GROUP BY device_id) f
    ON p.d1=f.device_id)  w
     on k.d1=w.d2
     JOIN 
  (SELECT p.device_id AS d3,p.kwh,f.device_id,f.unit,f.unit+p.kwh AS month_unit,
     p.kwh_d1+f.kwh_d2 as kwh_delivered_month
   from
   (SELECT device_id,round(sum(kwh),1) AS kwh,round(SUM(e.kwh_delivered),1) AS kwh_d1
    from data_rs485_summary e
    WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
    GROUP BY device_id) p
    join
    (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit,
    round(SUM(kwh_delivered),1) AS kwh_d2
     FROM data_rs485_summary T1
     JOIN device_details_energy T3
  ON T1.device_id=T3.device_id 
     WHERE month(T1.date)=month('$now_date1') AND T3.client_id!='v'
     GROUP BY T1.device_id ) f
     ON p.device_id=f.device_id) z
     ON k.d1=z.d3
    join
    (
     SELECT p.device_id AS d4,p.kwh,f.device_id,f.unit,f.unit+p.kwh AS year_unit,
     p.kwh_d1+f.kwh_d2 as kwh_delivered_year
   from
   (SELECT device_id,round(sum(kwh),1) AS kwh,round(SUM(e.kwh_delivered),1) AS kwh_d1
    from data_rs485_summary e
    WHERE   cast(dt_time AS DATE)='$now_date1' AND hour>7
    GROUP BY device_id) p
    join
    (SELECT T1.device_id,max(T1.dt_time),round(sum(T1.rs485_30),1) AS unit,
    round(SUM(kwh_delivered),1) AS kwh_d2
     FROM data_rs485_summary T1
     JOIN device_details_energy T3
  ON T1.device_id=T3.device_id 
     WHERE year(T1.date)=year('$now_date1') AND T3.client_id!='v'
     GROUP BY T1.device_id ) f
     ON p.device_id=f.device_id
    ) u
    ON k.d1=u.d4
    ORDER BY k.report_sort");
    }

    function transformHeatmapData($data){
        $result = array();
        $label = array();
        $array = json_decode(json_encode($data), True);
        foreach($array as $k => $v) {
            $result[$v['level_no']][$v['category_code']][] = $v;
            $label[$v['level_no']][$v['category_code']] = $v['category'];
        }
        try{
         return [
            'data'=>$result,
            'label'=>$label,
            'date' => $array[0]['date']
        ];
        }
        
        catch(\Exception $e){
            return false;
        }
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


