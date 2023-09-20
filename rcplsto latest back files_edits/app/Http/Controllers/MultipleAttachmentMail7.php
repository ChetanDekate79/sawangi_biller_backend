<?php

namespace App\Http\Controllers;

use App\Mail\EnergyReport as MailEnergyReport;
use PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\model\energy;

class MultipleAttachmentMail7 extends Controller
{
    //
    public function getReport($opration='mail',$date='now',$doc='pdf',$type='try'){

        $now_date = Carbon::now()->format("Y-m-d");
        $old_date = Carbon::now()->subDays(1);
        $old_date = $old_date->format("Y-m-d");

        if($date== 'now' || $date == $now_date ){
            //$yesterday = date("Y-m-d");
          // $date = $old_date;
            $date = $old_date;
        }
        if($opration=='mail') {

            $email = $this->getList(DB::select("select email from report_mail_to where energy_report = 1 and $type = 1"));
           // print_r($email);
      // return $this->setEnergyReport2($date,'pdf');
        //    return $this->setProductivityReport2($date,'pdf');
            $file[0] = $this->setEnergyReport2($date,'pdf');
            $file[1]= $this->setProductivityReport2($date,'pdf');
            $filename_arr[0] = $file_name = "Energy Report RC PLASTO TANKS AND PIPES PRIVATE LIMITED  [DATE- ".$date."].pdf";
            $filename_arr[1] = $file_name = "Productivity RC PLASTO TANKS AND PIPES PRIVATE LIMITED [DATE- ".$date."].pdf";
            $this->send($email,$file,$filename_arr);
           return $this->successResponse($email);
        }
    }

    public function setEnergyReport($date,$doc){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');

         $report_data = [
            'category' => $this->dailyCategoryEnergy($date),
            'feeders' => $this->transformEnergyData($this->reportFeedersEnergy($date))
        ];
      //return $report_data;
     // return    $this->genrateHTML($report_data);
        if($doc == 'pdf'){
            return $this->pdf($this->genrateHTML($report_data));
        }
        else
           return  $this->genrateHTML($report_data);

    }
     public function setEnergyReport2($date,$doc){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');

         $report_data = [
            'category' => $this->dailyCategoryEnergy2($date),
            'feeders' => $this->transformEnergyData($this->reportFeedersEnergy2($date))
        ];
      //return $report_data;
     // return    $this->genrateHTML($report_data);
        if($doc == 'pdf'){
            return $this->pdf($this->genrateHTML($report_data));
        }
        else
           return  $this->genrateHTML($report_data);

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
        return DB::select("
       select T1.date ,T1.expense_unit ,
        T1.no_of_feeders,
        T1.category_color,
        T1.category,
        T1.category_code,
        T1.kwh ,
        T1.net_kwh,
        T1.kwh_delivered,
        T1.kwh_expense ,
        T1.net_kwh_expense,
        T1.kwh_delivered_expense,

        T1.yesterday_kwh,
        T1.yesterday_net_kwh,
        T1.yesterday_kwh_delivered,
        T1.yesterday_kwh_expense,
        T1.yesterday_net_kwh_expense,
        T1.yesterday_kwh_delivered_expense,

        T2.kwh as month_kwh ,
        T2.net_kwh as month_net_kwh ,
        T2.kwh_delivered as month_kwh_delivered ,
        T2.kwh_expense as month_kwh_expense ,
        T2.net_kwh_expense as month_net_kwh_expense,
        T2.kwh_delivered_expense as month_kwh_delivered_expense,

        T3.kwh as year_kwh ,
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
                on T2.category_code = T3.category_code;
                ");
    }
    public function dailyCategoryEnergy2($date){
        $dt = new \DateTime($date); // <== instance from another API
       $carbon = Carbon::instance($dt);
       $old_date1=Carbon::parse($carbon)->format("Y-m-d");
       $now_date1=$carbon->addDays(1);
     $now_date1= Carbon::parse($now_date1)->format("Y-m-d");



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

    return DB::select("
        select  T1.date ,T1.name , T1.device_id , T1.expense_unit ,
        T1.no_of_feeders,
        if(T1.level_no = 1,'feeder','sub-feeder') as sld,
        if(T1.category_code = 'cat_1', 0 ,1) as indexs,
        T1.level_no,
        T1.category_color,
        T1.category,
        T1.category_code,
        T1.report_sort,
        T1.kwh ,
        T1.net_kwh,
        T1.kwh_delivered,
        T1.kwh_expense ,
        T1.net_kwh_expense,
        T1.kwh_delivered_expense,

        T1.yesterday_kwh,
        T1.yesterday_net_kwh,
        T1.yesterday_kwh_delivered,
        T1.yesterday_kwh_expense,
        T1.yesterday_net_kwh_expense,
        T1.yesterday_kwh_delivered_expense,

        T2.kwh as month_kwh ,
        T2.net_kwh as month_net_kwh ,
        T2.kwh_delivered as month_kwh_delivered ,
        T2.kwh_expense as month_kwh_expense ,
        T2.net_kwh_expense as month_net_kwh_expense,
        T2.kwh_delivered_expense as month_kwh_delivered_expense,

        T3.kwh as year_kwh ,
        T3.net_kwh as year_net_kwh ,
        T3.kwh_delivered as year_kwh_delivered ,
        T3.kwh_expense as year_kwh_expense ,
        T3.net_kwh_expense as year_net_kwh_expense,
        T3.kwh_delivered_expense as year_kwh_delivered_expense

        from
        (select max(T1.date) as date,
                        max(category_color) as category_color,
                        max(level_no) as level_no,
                        max(report_sort) AS report_sort,
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
                    and T1.date >= '2020-02-08'
                    group by  T3.device_id) T1

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
                    and T1.date >= '2020-02-08'
                    group by T1.device_id) T3
        on T2.device_id = T3.device_id 
		  order by level_no , category_code,report_sort;


        ");
}
public function reportFeedersEnergy2($date){
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

    function transformEnergyData($data){
        $result = array();
        $label = array();
        $array = json_decode(json_encode($data), True);
        foreach($array as $k => $v) {
            $result[$v['level_no']][$v['category_code']][] = $v;
            $label[$v['level_no']][$v['category_code']] = $v['category'];
        }
        return [
            'data'=>$result,
            'label'=>$label,
            'date' => $array[0]['date']
        ];
    }
    /*function pdf($html)
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
    }*/


/*
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
*/
  /*  function getList($data){
        $arr= [];
        foreach($data as $key=>$value){
            $arr[] = $value->email;
        }
        return $arr;
    }*/

    public function setProductivityReport($date,$doc){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');

         $report_data = $this->transformProductivityData($this->ProductionMachinData($date));
     // return $report_data;
     // return    $this->genrateHTML($report_data);
        if($doc == 'pdf'){
            return $this->pdf($this->genrateHTMLProductivity($report_data));
        }
        else
           return  $this->genrateHTMLProductivity($report_data);

    }
      public function setProductivityReport2($date,$doc){
        $day = Carbon::parse($date)->format('d');
        $month = Carbon::parse($date)->format('m');
        $year = Carbon::parse($date)->format('Y');

         $report_data = $this->transformProductivityData($this->ProductionMachinData2($date));
     // return $report_data;
     // return    $this->genrateHTML($report_data);
        if($doc == 'pdf'){
            return $this->pdf($this->genrateHTMLProductivity($report_data));
        }
        else
           return  $this->genrateHTMLProductivity($report_data);

    }

    public function genrateHTMLProductivity($arr){

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
        select device_name,
            concat(location_id,' : ',device_category) device_category,
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
public function ProductionMachinData2($date){
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
        CONCAT(cast(c.dt1 AS DATE),' 7am -',cast(c.dt2 AS DATE),' 7:00am') as date,
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

    function transformProductivityData($data){
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
     $pdf->setPaper('A3', 'portrait');
    // $pdf->loadHTML($this->convert_customer_data_to_html());
     $pdf->loadHTML($html);

     return $pdf->stream();
    }



    public function send($email,$file,$file_name){
       return Mail::bcc($email)->send(new MailEnergyReport($file,$file_name,true));
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
