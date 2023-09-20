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
use PDF;

class EnergyReport extends Controller
{ 
    //

    public function getEnergyReport($opration='download',$date='now',$doc='html',$type='try'){
try{
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
		Look like a Date or Category Not Selrcted
		</h2>';
}

    }

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
                        <th>Today <br><br> (Recieved/Delivered)</th>
                        <th>Yesterday<br><br> (Recieved/Delivered)</th>
                        <th>This Month <br><br> (Recieved/Delivered)</th>
                        <th>This Year <br><br> (Recieved/Delivered)</th>
                       <!-- <th>Today Money</th>-->
                        </tr>
                    </thead>';
        $t_head2 ='<thead>
                        <tr>
                            <th>Feeders Name</th>
                            <th> </th>
                            <th>Today(kwh)<br><br> (Recieved/Delivered)</th>
                            <th>Yesterday(kwh)<br><br> (Recieved/Delivered)</th>
                            <th>This Month(kwh)<br><br> (Recieved/Delivered)</th>
                            <th>This Year(kwh)<br><br> (Recieved/Delivered)</th>
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
//worked
//   public function genrateHTML($arr){
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
//     $kwh=$value1['kwh'];
//     $yesterday_kwh=$value1['yesterday_kwh'];
//     $name=$value1['name'];
//    }
//    }
//    }
  
  

//                 $t_body2.='
//                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
//                 <td>  '.$name.'  </td>
//                 <td style="text-align: right;">'.$kwh.'</td>
//                 <td style="text-align: right;">'.$yesterday_kwh.'</td>
//                 <td style="text-align: right;">'.$value1['month_kwh'].'</td>
//                 <td style="text-align: right;">'.$value1['year_kwh'].'</td>
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

//     public function genrateHTML($arr){
// try{
//         $data = json_decode(json_encode($arr['category']), True);
//         $data2 = json_decode(json_encode($arr['feeders']), True);
// 		$energy = new energy();
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
// 	width:15%;
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
//         $t_head1 ='<thead>
//                         <tr>
//                         <th>Name </th>
//                         <th> </th>
//                         <th>Today</th>
//                         <th>Yesterday</th>
//                         <th>This Month</th>
//                         <th>This Year</th>
//                        <!-- <th>Today Money</th>-->
//                         </tr>
//                     </thead>';
//         $t_head2 ='<thead>
//                         <tr>
//                             <th>Feeders Name</th>
//                             <th> </th>
//                             <th>Today(kwh)</th>
//                             <th>Yesterday(kwh)</th>
//                             <th>This Month(kwh)</th>
//                             <th>This Year(kwh)</th>
//                             <!--<th>Today Money</th>-->
//                         </tr>
//                     </thead>';
//         $t_body1 = '';
//         $t_body2 = '';
//         $t_body3 = '';
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
// 	$str_row =  '<td class="category"   rowspan=""></td>';

// }
//                 $t_body2.='
//                 <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
//                 <td>  '.$value1['name'].'  </td>
//                 <td>'.$value1['kwh'].'</td>
//                 <td>'.$value1['yesterday_kwh'].'</td>
//                 <td>'.$value1['month_kwh'].'</td>
//                 <td>'.$value1['year_kwh'].'</td>
//                 <!--<td>'.round($value1['kwh_expense']).'</td>-->
//                 </tr>';
//             }
//         }
//         /*foreach($data2['data'][2] as $key=>$value){
//             $rowspan = count($value);
//             $flag = true;
//             foreach($value as $key1=>$value1){
//                 $str_row = '';
//                 if($flag)
//                 {
//                     $str_row =  '<td class="category"   rowspan=""  >'.$data2['label'][2][$key].'</td>';
//                     $flag = false;
//                 }else{
// 	$str_row =  '<td class="category"   rowspan=""></td>';

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
//         }*/
//          $t_body1 = '<tbody>'.$t_body1.'</tbody>';
//          $t_body2 = '<tbody>'.$t_body2.'</tbody>';
//          $t_body3 = '<tbody>'.$t_body3.'</tbody>';
//          $table1 = '<table>'.$t_head1.''.$t_body1.'</table>';
//          $table2 = '<table>'.$t_head2.''.$t_body2.'</table>';
//         // $table3 = '<table>'.$t_head2.''.$t_body3.'</table>';
//          $html_page['body'] =
//          '<body>'.$html_page['heading'].''
//                 .$html_page['heading_1'].''.$table1.''
//                  .$html_page['heading_2'].''.$table2.'<div class="page_break_none"><br></div>
//                  </body>';
//          $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';
//     return $html_page['page'];
// }
// catch(Exception $e){
// return '<h2 class="warning"> Something Went Wrong 
// 		Look like a Date or Category Not Selrcted
// 		</h2>'; 
// }


//     }



    public function dailyCategoryEnergy($date){
        return DB::select("
         select T1.date ,T1.expense_unit ,
        T1.no_of_feeders,
        T1.category_color,
        T1.category,
        T1.category_code,
        concat(round(T1.kwh),' /(',round(T1.kwh_delivered),')') AS kwh ,
        T1.net_kwh,
        T1.kwh_delivered,
        T1.kwh_expense ,
        T1.net_kwh_expense,
        T1.kwh_delivered_expense,

      concat(round(T1.yesterday_kwh),' /(',round(T1.yesterday_kwh_delivered),')') AS yesterday_kwh ,
        T1.yesterday_net_kwh,
         
        T1.yesterday_kwh_delivered,
        T1.yesterday_kwh_expense,
        T1.yesterday_net_kwh_expense,
        T1.yesterday_kwh_delivered_expense,

        concat(round(T2.kwh),' /(',round(T2.kwh_delivered),')') AS month_kwh  ,
        
        T2.net_kwh as month_net_kwh ,
        T2.kwh_delivered as month_kwh_delivered ,
        T2.kwh_expense as month_kwh_expense ,
        T2.net_kwh_expense as month_net_kwh_expense,
        T2.kwh_delivered_expense as month_kwh_delivered_expense,

        
          concat(ROUND(T3.kwh),' /(',ROUND(T3.kwh_delivered),')') AS year_kwh ,
        T3.net_kwh as year_net_kwh ,
        T3.kwh_delivered as year_kwh_delivered ,
        T3.kwh_expense as year_kwh_expense ,
        T3.net_kwh_expense as year_net_kwh_expense,
        T3.kwh_delivered_expense as year_kwh_delivered_expense

        from (
             select max(T1.date) as date,
            max(T3.date) as yesterday_date,
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
            round(sum(T1.export_kw * rate),1) as export_kw_expense,

            round(sum(T3.rs485_1),1) as yesterday_kw,
            round(sum(T3.rs485_30),1) as yesterday_kwh,
            round(sum(T3.kwh_delivered),1) as yesterday_kwh_delivered,
            round(sum(T3.net_kwh),1) as yesterday_net_kwh,
            round(sum(T3.net_kwh * rate),1) as yesterday_net_kwh_expense,
            round(sum(T3.kwh_delivered * rate),1) as yesterday_kwh_delivered_expense,

            round(sum(T3.rs485_1 * rate),1) as yesterday_kw_expense,
            round(sum(T3.rs485_30 * rate),1) as yesterday_kwh_expense,
            round(sum(T3.import_kw),1) as yesterday_import_kw,
            round(sum(T3.import_kw * rate),1) as yesterday_import_kw_expense,
            round(sum(T3.export_kw),1) as yesterday_export_kw,
            round(sum(T3.export_kw * rate),1) as yesterday_export_kw_expense


            from data_rs485_summary T1  join device_details_energy T2
            on T1.device_id = T2.device_id
             LEFT JOIN data_rs485_summary T3
            ON T3.device_id = T2.device_id
            AND  T3.date = date_sub('$date', interval 1 day)

            cross join rate_config on type ='money'
            where device_category_level=1 and visibility = 1
            and T1.date='$date'
            and T1.date >= '2020-02-08'
            group by device_category_code
            order by device_category_code ) T1
            left join
            (
            select max(T1.date) as date,
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
            from data_rs485_summary T1  join device_details_energy T2
            on T1.device_id = T2.device_id
            cross join rate_config on type ='money'
            where device_category_level=1 and visibility = 1
            and year(T1.date) =year('$date')
            and month(T1.date) =month('$date')
            and T1.date <= '$date'
            and T1.date >= '2020-02-08'
            group by device_category_code
            order by device_category_code
                ) T2
                on T1.category_code = T2.category_code
            left join(
            select max(T1.date) as date,
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
            from data_rs485_summary T1  join device_details_energy T2
            on T1.device_id = T2.device_id
            cross join rate_config on type ='money'
            where device_category_level=1 and visibility = 1
            and year(T1.date) =year('$date')
            and T1.date <= '$date'
            and T1.date >= '2020-02-08'
            group by device_category_code
            order by device_category_code
                ) T3
                on T2.category_code = T3.category_code
                ");
    }

    public function monthCategoryEnergy($year,$month){
        return DB::select("
            select month(max(T1.date)) as month,
                year(max(T1.date)) as year,
                monthname(max(T1.date)) as month_name,
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
            and month(T1.date) = $month
            group by device_category_code
            order by device_category_code;
        ");

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
     
   //  return DB::select("
   //      select  T1.date ,T1.name , T1.device_id , T1.expense_unit ,
   //      T1.no_of_feeders,
   //      if(T1.level_no = 1,'feeder','sub-feeder') as sld,
   //      if(T1.category_code = 'cat_1', 0 ,1) as indexs,
   //      T1.level_no,
	  //  T1.report_sort,
   //      T1.category_color,
   //      T1.category,
   //      T1.category_code,
   //      T1.kwh ,
   //      T1.net_kwh,
   //      T1.kwh_delivered,
   //      T1.kwh_expense ,
   //      T1.net_kwh_expense,
   //      T1.kwh_delivered_expense,

   //      T1.yesterday_kwh,
   //      T1.yesterday_net_kwh,
   //      T1.yesterday_kwh_delivered,
   //      T1.yesterday_kwh_expense,
   //      T1.yesterday_net_kwh_expense,
   //      T1.yesterday_kwh_delivered_expense,

   //      T2.kwh as month_kwh ,
   //      T2.net_kwh as month_net_kwh ,
   //      T2.kwh_delivered as month_kwh_delivered ,
   //      T2.kwh_expense as month_kwh_expense ,
   //      T2.net_kwh_expense as month_net_kwh_expense,
   //      T2.kwh_delivered_expense as month_kwh_delivered_expense,

   //      T3.kwh as year_kwh ,
   //      T3.net_kwh as year_net_kwh ,
   //      T3.kwh_delivered as year_kwh_delivered ,
   //      T3.kwh_expense as year_kwh_expense ,
   //      T3.net_kwh_expense as year_net_kwh_expense,
   //      T3.kwh_delivered_expense as year_kwh_delivered_expense

   //      from
   //      (select max(T1.date) as date,T2.report_sort,
   //                      max(category_color) as category_color,
   //                      max(level_no) as level_no,
   //                          max(device_name) as name,
   //                          max(T1.device_id) as device_id,
   //                          max(device_category) as category,
   //                          max(device_category_code) as category_code,
   //                  max(unit) as expense_unit,
   //                  count(Distinct  T1.device_id) as no_of_feeders,

   //                      round(sum(T1.rs485_30),1) as kwh,
   //                      round(sum(T1.kwh_delivered),1) as kwh_delivered,
   //                          round(sum(T1.net_kwh),1) as net_kwh,
   //                      round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
   //                      round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,

   //                      round(sum(T1.rs485_30 * rate),1) as kwh_expense,

   //                      round(sum(T3.rs485_30),1) as yesterday_kwh,
   //                      round(sum(T3.kwh_delivered),1) as yesterday_kwh_delivered,
   //                          round(sum(T3.net_kwh),1) as yesterday_net_kwh,
   //                      round(sum(T3.net_kwh * rate),1) as yesterday_net_kwh_expense,
   //                      round(sum(T3.kwh_delivered * rate),1) as yesterday_kwh_delivered_expense,
   //                      round(sum(T3.rs485_30 * rate),1) as yesterday_kwh_expense
   //                  from data_rs485_summary T1 , data_rs485_summary T3
   //                      join device_details_energy T2 on T3.device_id = T2.device_id
   //                  cross join rate_config on type ='money'
   //                  where  visibility = 1
   //                  and  T3.device_id = T1.device_id
   //                  and T3.date = date_sub('$date', interval 1 day)
   //                  and T1.date='$date'
			// 		and T1.date <= '$date'
   //                  and T1.date >= '2020-02-08'
   //                  group by  T3.device_id,T2.report_sort) T1

   //      left join
   //      (select month(max(T1.date)) as month,
   //              year(max(T1.date)) as year,
   //              monthname(max(T1.date)) as month_name,
   //              max(category_color) as category_color,
   //                  max(device_name) as name,
   //                  max(T1.device_id) as device_id,
   //                  max(device_category) as category,
   //                  max(device_category_code) as category_code,
   //              max(unit) as expense_unit,
   //              count(Distinct  T1.device_id) as no_of_feeders,
   //              round(sum(T1.rs485_30),1) as kwh,
   //              round(sum(T1.kwh_delivered),1) as kwh_delivered,
   //                  round(sum(T1.net_kwh),1) as net_kwh,
   //              round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
   //              round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,
   //              round(sum(T1.rs485_30 * rate),1) as kwh_expense
   //          from data_rs485_summary T1
   //              join device_details_energy T2 on T1.device_id = T2.device_id
   //          cross join rate_config on type ='money'
   //          where visibility = 1
   //          and year(T1.date)  = year('$date')
   //          and month(T1.date) = month('$date')
			// and T1.date <= '$date'
   //          and T1.date >= '2020-02-08'
   //          group by T1.device_id) T2
   //      on T1.device_id = T2.device_id
   //      left join
   //      (select
   //              year(max(T1.date)) as year,
   //                      max(category_color) as category_color,
   //                      max(device_name) as name,

   //                      max(T1.device_id) as device_id,
   //                  max(device_category) as category,
   //                  max(device_category_code) as category_code,
   //                  max(unit) as expense_unit,
   //                      count(Distinct  T1.device_id) as no_of_feeders,
   //                      round(sum(T1.rs485_1),1) as kw,
   //                      round(sum(T1.rs485_30),1) as kwh,
   //                      round(sum(T1.kwh_delivered),1) as kwh_delivered,
   //                          round(sum(T1.net_kwh),1) as net_kwh,
   //                      round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
   //                      round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,
   //                      round(sum(T1.rs485_30 * rate),1) as kwh_expense

   //                  from data_rs485_summary T1
   //                  join device_details_energy T2
   //                          on T1.device_id = T2.device_id
   //                  cross join rate_config on type ='money'
   //                  where  visibility = 1
   //                  and year(T1.date)  = year('$date')
			// 		and T1.date <= '$date'
   //                  and T1.date >= '2020-02-08'
   //                  group by T1.device_id) T3
   //      on T2.device_id = T3.device_id order by report_sort;


   //      ");

    // return DB::select(" select  T1.date ,T1.name , T1.device_id , T1.expense_unit ,
    //     T1.no_of_feeders,
    //     if(T1.level_no = 1,'feeder',if(T1.level_no = 2,'sub-feeder',if(T1.level_no = 3,'load','sub-feeder'))) AS sld,
    //     if(T1.category_code = 'cat_1', 0 ,1) as indexs,
    //     T1.level_no,
    //    T1.report_sort,
    //     T1.category_color,
    //     T1.category,
    //     T1.category_code,
    //     T1.kwh ,
    //     T1.net_kwh,
    //     T1.kwh_delivered,
    //     T1.kwh_expense ,
    //     T1.net_kwh_expense,
    //     T1.kwh_delivered_expense,

    //     T1.yesterday_kwh,
    //     T1.yesterday_net_kwh,
    //     T1.yesterday_kwh_delivered,
    //     T1.yesterday_kwh_expense,
    //     T1.yesterday_net_kwh_expense,
    //     T1.yesterday_kwh_delivered_expense,

    //     T2.kwh as month_kwh ,
    //     T2.net_kwh as month_net_kwh ,
    //     T2.kwh_delivered as month_kwh_delivered ,
    //     T2.kwh_expense as month_kwh_expense ,
    //     T2.net_kwh_expense as month_net_kwh_expense,
    //     T2.kwh_delivered_expense as month_kwh_delivered_expense,

    //     T3.kwh as year_kwh ,
    //     T3.net_kwh as year_net_kwh ,
    //     T3.kwh_delivered as year_kwh_delivered ,
    //     T3.kwh_expense as year_kwh_expense ,
    //     T3.net_kwh_expense as year_net_kwh_expense,
    //     T3.kwh_delivered_expense as year_kwh_delivered_expense

    //     from
    //     (select max(T1.date) as date,T2.report_sort,
    //                     max(category_color) as category_color,
    //                     max(level_no) as level_no,
    //                         max(device_name) as name,
    //                         max(T1.device_id) as device_id,
    //                         max(device_category) as category,
    //                         max(device_category_code) as category_code,
    //                 max(unit) as expense_unit,
    //                 count(Distinct  T1.device_id) as no_of_feeders,

    //                     round(sum(T1.rs485_30),1) as kwh,
    //                     round(sum(T1.kwh_delivered),1) as kwh_delivered,
    //                         round(sum(T1.net_kwh),1) as net_kwh,
    //                     round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
    //                     round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,

    //                     round(sum(T1.rs485_30 * rate),1) as kwh_expense,

    //                     round(sum(T3.rs485_30),1) as yesterday_kwh,
    //                     round(sum(T3.kwh_delivered),1) as yesterday_kwh_delivered,
    //                         round(sum(T3.net_kwh),1) as yesterday_net_kwh,
    //                     round(sum(T3.net_kwh * rate),1) as yesterday_net_kwh_expense,
    //                     round(sum(T3.kwh_delivered * rate),1) as yesterday_kwh_delivered_expense,
    //                     round(sum(T3.rs485_30 * rate),1) as yesterday_kwh_expense
    //                 from data_rs485_summary T1 , data_rs485_summary T3
    //                     join device_details_energy T2 on T3.device_id = T2.device_id
    //                 cross join rate_config on type ='money'
    //                 where  visibility = 1
    //                 and  T3.device_id = T1.device_id
    //                 and T3.date = date_sub('$date', interval 1 day)
    //                 and T1.date='$date'
    //                 and T1.date <= '$date'
    //                 and T1.date >= '2020-02-08'
    //                 group by  T3.device_id,T2.report_sort) T1

    //     left join
    //     (select month(max(T1.date)) as month,
    //             year(max(T1.date)) as year,
    //             monthname(max(T1.date)) as month_name,
    //             max(category_color) as category_color,
    //                 max(device_name) as name,
    //                 max(T1.device_id) as device_id,
    //                 max(device_category) as category,
    //                 max(device_category_code) as category_code,
    //             max(unit) as expense_unit,
    //             count(Distinct  T1.device_id) as no_of_feeders,
    //             round(sum(T1.rs485_30),1) as kwh,
    //             round(sum(T1.kwh_delivered),1) as kwh_delivered,
    //                 round(sum(T1.net_kwh),1) as net_kwh,
    //             round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
    //             round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,
    //             round(sum(T1.rs485_30 * rate),1) as kwh_expense
    //         from data_rs485_summary T1
    //             join device_details_energy T2 on T1.device_id = T2.device_id
    //         cross join rate_config on type ='money'
    //         where visibility = 1
    //         and year(T1.date)  = year('$date')
    //         and month(T1.date) = month('$date')
    //         and T1.date <= '$date'
    //         and T1.date >= '2020-02-08'
    //         group by T1.device_id) T2
    //     on T1.device_id = T2.device_id
    //     left join
    //     (select
    //             year(max(T1.date)) as year,
    //                     max(category_color) as category_color,
    //                     max(device_name) as name,

    //                     max(T1.device_id) as device_id,
    //                 max(device_category) as category,
    //                 max(device_category_code) as category_code,
    //                 max(unit) as expense_unit,
    //                     count(Distinct  T1.device_id) as no_of_feeders,
    //                     round(sum(T1.rs485_1),1) as kw,
    //                     round(sum(T1.rs485_30),1) as kwh,
    //                     round(sum(T1.kwh_delivered),1) as kwh_delivered,
    //                         round(sum(T1.net_kwh),1) as net_kwh,
    //                     round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
    //                     round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,
    //                     round(sum(T1.rs485_30 * rate),1) as kwh_expense

    //                 from data_rs485_summary T1
    //                 join device_details_energy T2
    //                         on T1.device_id = T2.device_id
    //                 cross join rate_config on type ='money'
    //                 where  visibility = 1
    //                 and year(T1.date)  = year('$date')
    //                 and T1.date <= '$date'
    //                 and T1.date >= '2020-02-08'
    //                 group by T1.device_id) T3
    //     on T2.device_id = T3.device_id order by report_sort;");

 return DB::select("
    select  T1.date ,T1.name , T1.device_id , T1.expense_unit ,
        T1.no_of_feeders,
        if(T1.level_no = 1,'feeder',if(T1.level_no = 2,'sub-feeder',if(T1.level_no = 3,'load','sub-feeder'))) AS sld,
        if(T1.category_code = 'cat_1', 0 ,1) as indexs,
        T1.level_no,
       T1.report_sort,
        T1.category_color,
        T1.category,
        T1.category_code,
        if(T1.device_id IN (19,20,21),concat(round(T1.kwh),' / (',round(T1.kwh_delivered),')'),round(T1.kwh)) AS kwh ,
        T1.net_kwh,
        T1.kwh_delivered,
        T1.kwh_expense ,
        T1.net_kwh_expense,
        T1.kwh_delivered_expense,

        if(T1.device_id IN (19,20,21),concat(round(T1.yesterday_kwh),' / (',round(T1.yesterday_kwh_delivered),')'),round(T1.yesterday_kwh)) AS yesterday_kwh,
        
        T1.yesterday_net_kwh,
        T1.yesterday_kwh_delivered,
        T1.yesterday_kwh_expense,
        T1.yesterday_net_kwh_expense,
        T1.yesterday_kwh_delivered_expense,

      
      
        if(T1.device_id IN (19,20,21),concat(round(T2.kwh),' / (',round(T2.kwh_delivered),')'),round(T2.kwh)) AS month_kwh,
        
        T2.net_kwh as month_net_kwh ,
        T2.kwh_delivered as month_kwh_delivered ,
        T2.kwh_expense as month_kwh_expense ,
        T2.net_kwh_expense as month_net_kwh_expense,
        T2.kwh_delivered_expense as month_kwh_delivered_expense,
 
 
       if(T1.device_id IN (19,20,21),CONCAT(round(T3.kwh),' / (',round(T3.kwh_delivered),')'),round(T3.kwh)) AS year_kwh,
        
        T3.net_kwh as year_net_kwh ,
        T3.kwh_delivered as year_kwh_delivered ,
        T3.kwh_expense as year_kwh_expense ,
        T3.net_kwh_expense as year_net_kwh_expense,
        T3.kwh_delivered_expense as year_kwh_delivered_expense

        from
        (select max(T1.date) as date,T2.report_sort,
                        max(category_color) as category_color,
                        max(level_no) as level_no,
                            max(device_name) as name,
                            max(T1.device_id) as device_id,
                            max(device_category) as category,
                            max(device_category_code) as category_code,
                    max(unit) as expense_unit,
                    count(Distinct  T1.device_id) as no_of_feeders,

                        round(sum(T1.rs485_30),1) as kwh,
                        round(sum(T1.kwh_delivered),1) as kwh_delivered,
                            round(sum(T1.net_kwh),1) as net_kwh,
                        round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
                        round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,

                        round(sum(T1.rs485_30 * rate),1) as kwh_expense,

                        round(sum(T3.rs485_30),1) as yesterday_kwh,
                        round(sum(T3.kwh_delivered),1) as yesterday_kwh_delivered,
                            round(sum(T3.net_kwh),1) as yesterday_net_kwh,
                        round(sum(T3.net_kwh * rate),1) as yesterday_net_kwh_expense,
                        round(sum(T3.kwh_delivered * rate),1) as yesterday_kwh_delivered_expense,
                        round(sum(T3.rs485_30 * rate),1) as yesterday_kwh_expense
                    from data_rs485_summary T1 , data_rs485_summary T3
                        join device_details_energy T2 on T3.device_id = T2.device_id
                    cross join rate_config on type ='money'
                    where  visibility = 1
                    and  T3.device_id = T1.device_id
                    and T3.date = date_sub('$date', interval 1 day)
                    and T1.date='$date'
                    and T1.date <= '$date'
                    and T1.date >= '2020-02-08'
                    group by  T3.device_id,T2.report_sort) T1

        left join
        (select month(max(T1.date)) as month,
                year(max(T1.date)) as year,
                monthname(max(T1.date)) as month_name,
                max(category_color) as category_color,
                    max(device_name) as name,
                    max(T1.device_id) as device_id,
                    max(device_category) as category,
                    max(device_category_code) as category_code,
                max(unit) as expense_unit,
                count(Distinct  T1.device_id) as no_of_feeders,
                round(sum(T1.rs485_30),1) as kwh,
                round(sum(T1.kwh_delivered),1) as kwh_delivered,
                    round(sum(T1.net_kwh),1) as net_kwh,
                round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
                round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,
                round(sum(T1.rs485_30 * rate),1) as kwh_expense
            from data_rs485_summary T1
                join device_details_energy T2 on T1.device_id = T2.device_id
            cross join rate_config on type ='money'
            where visibility = 1
            and year(T1.date)  = year('$date')
            and month(T1.date) = month('$date')
            and T1.date <= '$date'
            and T1.date >= '2020-02-08'
            group by T1.device_id) T2
        on T1.device_id = T2.device_id
        left join
        (select
                year(max(T1.date)) as year,
                        max(category_color) as category_color,
                        max(device_name) as name,

                        max(T1.device_id) as device_id,
                    max(device_category) as category,
                    max(device_category_code) as category_code,
                    max(unit) as expense_unit,
                        count(Distinct  T1.device_id) as no_of_feeders,
                        round(sum(T1.rs485_1),1) as kw,
                        round(sum(T1.rs485_30),1) as kwh,
                        round(sum(T1.kwh_delivered),1) as kwh_delivered,
                            round(sum(T1.net_kwh),1) as net_kwh,
                        round(sum(T1.net_kwh * rate),1) as net_kwh_expense,
                        round(sum(T1.kwh_delivered * rate),1) as kwh_delivered_expense,
                        round(sum(T1.rs485_30 * rate),1) as kwh_expense

                    from data_rs485_summary T1
                    join device_details_energy T2
                            on T1.device_id = T2.device_id
                    cross join rate_config on type ='money'
                    where  visibility = 1
                    and year(T1.date)  = year('$date')
                    and T1.date <= '$date'
                    and T1.date >= '2020-02-08'
                    group by T1.device_id) T3
        on T2.device_id = T3.device_id order by report_sort
    ");
}

    function transformHeatmapData($data){
        $result = array();
        $label = array();
        $array = json_decode(json_encode($data), True);
        foreach($array as $k => $v) {
            $result[$v['level_no']][$v['category_code']][] = $v;
            $label[$v['level_no']][$v['category_code']] = $v['category'];
        }

     //  dd($result);
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


