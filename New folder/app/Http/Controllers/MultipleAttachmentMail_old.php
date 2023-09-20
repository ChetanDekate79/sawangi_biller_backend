<?php

namespace App\Http\Controllers;

use App\Mail\EnergyReport as MailEnergyReport;
use PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MultipleAttachmentMail extends Controller
{
    //
    public function getReport($opration='mail',$date='now',$doc='pdf',$type='try'){

        $now_date = Carbon::now()->format("Y-m-d");
        $old_date = Carbon::now()->subDays(1);
        $old_date = $old_date->format("Y-m-d");

        if($date== 'now' || $date == $now_date ){
            //$yesterday = date("Y-m-d");
           $date = $old_date;
        }
        if($opration=='mail') {

            $email = $this->getList(DB::select("select email from report_mail_to where energy_report = 1 and $type = 1"));
           // print_r($email);
            //return $this->setEnergyReport($date,'pdf');
            $file[0] = $this->setEnergyReport($date,'pdf');
            $file[1]= $this->setProductivityReport($date,'pdf');
            $filename_arr[0] = $file_name = "Energy Report Zim Lab ,Nagpur [DATE- ".$date."].pdf";
            $filename_arr[1] = $file_name = "Productivity Report Zim Lab ,Nagpur [DATE- ".$date."].pdf";
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

    public function genrateHTML($arr){

        $data = json_decode(json_encode($arr['category']), True);
        $data2 = json_decode(json_encode($arr['feeders']), True);
        $date = $data2['date'];
        $html_page['head'] = '
            <head>

            <title>  Zim Laboratories, Nagpur -'.$date.'</title>
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
                     footer { position: fixed; bottom: -60px; left: 0px; right: 0px;}
                     .footer .page-number:after { content: counter(page); }
                     main { text-align: center;}
                     @page {
                        font-family: arial;
                        font-size:14px; !important;
                    }
                    .page_break { page-break-before: always; }
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
                            <img   width="150px" src="http://www.hetadatain.com/img/hetadatain_logo.png"  id=""></span>
                        </td>
                        <td  class="std txt-align" style="text-align: right;">
                            <span style=" ">
                        <img   width="100px" src="http://zimlab.hetadatain.com/assets/client_logo.png" id=""></span>
                        </td>
                    </tr>
                </table>
            </div>
            <center>
                <div>
                    <h2>ZIM Laboratories Limited Nagpur</h2>
                    <h3 > Energy Report For Date - '.$date.' </h3>
                </div>
            </center><hr>';
        $html_page['heading_1'] ='<h3>Total Consumption </h3>';
        $html_page['heading_2'] ='<h3>Main Feeders Consumption </h3>';
        $html_page['heading_3'] ='<h3>Sub-Feeders Consumption </h3>';
        $t_head1 ='<thead>
                        <tr>
                        <th>Name </th>
                        <th> </th>
                        <th>Today</th>
                        <th>Yesterday</th>
                        <th>This Month</th>
                        <th>This Year</th>
                       <!-- <th>Today Money</th>-->
                        </tr>
                    </thead>';
        $t_head2 ='<thead>
                        <tr>
                            <th>Feeders Name</th>
                            <th> </th>
                            <th>Today(kwh)</th>
                            <th>Yesterday(kwh)</th>
                            <th>This Month(kwh)</th>
                            <th>This Year(kwh)</th>
                            <!--<th>Today Money</th>-->
                        </tr>
                    </thead>';
        $t_body1 = '';
        $t_body2 = '';
        $t_body3 = '';
        foreach($data as $key=>$value){
            $t_body1.='
            <tr style="background-color:'.$value['category_color'].'" class="text">
                <td rowspan="1" >'.$value['category'].'</td>
                <td> kwh </td>
                <td>'.$value['kwh'].'</td>
                <td>'.$value['yesterday_kwh'].'</td>
                <td>'.$value['month_kwh'].'</td>
                <td>'.$value['year_kwh'].'</td>
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
                    $str_row =  '<td rowspan="'.$rowspan.'" >'.$data2['label'][1][$key].'</td>';
                    $flag = false;
                }
                $t_body2.='
                <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
                <td>  '.$value1['name'].'  </td>
                <td>'.$value1['kwh'].'</td>
                <td>'.$value1['yesterday_kwh'].'</td>
                <td>'.$value1['month_kwh'].'</td>
                <td>'.$value1['year_kwh'].'</td>
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
                    $str_row =  '<td rowspan="'.$rowspan.'" >'.$data2['label'][2][$key].'</td>';
                    $flag = false;
                }

                $t_body3.='
                <tr style="background-color:'.$value1['category_color'].'">'.$str_row.'
                <td>  '.$value1['name'].'  </td>
                <td>'.$value1['kwh'].'</td>
                <td>'.$value1['yesterday_kwh'].'</td>
                <td>'.$value1['month_kwh'].'</td>
                <td>'.$value1['year_kwh'].'</td>
               <!-- <td>'.round($value1['kwh_expense']).'</td>-->
                </tr>';
            }
        }
         $t_body1 = '<tbody>'.$t_body1.'</tbody>';
         $t_body2 = '<tbody>'.$t_body2.'</tbody>';
         $t_body3 = '<tbody>'.$t_body3.'</tbody>';
         $table1 = '<table>'.$t_head1.''.$t_body1.'</table>';
         $table2 = '<table>'.$t_head2.''.$t_body2.'</table>';
         $table3 = '<table>'.$t_head2.''.$t_body3.'</table>';
         $html_page['body'] =
         '<body>'.$html_page['heading'].''
                .$html_page['heading_1'].''.$table1.''
                 .$html_page['heading_2'].''.$table2.'<div class="page_break"></div>'.$html_page['heading_3'].''.$table3.'
                 </body>';
         $html_page['page'] = '<html>'.$html_page['head'].''.$html_page['body'].'</html>';
    return $html_page['page'];

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


            from data_rs485_summary T1 , data_rs485_summary T3 join device_details_energy T2
            on T3.device_id = T2.device_id

            cross join rate_config on type ='money'
            where device_category_level=1 and visibility = 1
            and  T3.device_id = T1.device_id
            and T3.date = date_sub('$date', interval 1 day)
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
            and T1.date >= '2020-02-08'
            group by device_category_code
            order by device_category_code
                ) T3
                on T2.category_code = T3.category_code;
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

    return DB::select("
        select  T1.date ,T1.name , T1.device_id , T1.expense_unit ,
        T1.no_of_feeders,
        if(T1.level_no = 1,'feeder','sub-feeder') as sld,
        if(T1.category_code = 'cat_1', 0 ,1) as indexs,
        T1.level_no,
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

        from
        (select max(T1.date) as date,
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
        on T2.device_id = T3.device_id order by level_no , category_code,indexs;


        ");
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

    public function genrateHTMLProductivity($arr){

        $data = json_decode(json_encode($arr), True);
        $date = $data['date'];
        $html_page['head'] = '
            <head>

            <title>  Zim Laboratories, Nagpur -'.$date.'</title>
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
                            <img   width="150px" src="http://www.hetadatain.com/img/hetadatain_logo.png"  id=""></span>
                        </td>
                        <td  class="std txt-align" style="text-align: right;">
                            <span style=" ">
                        <img   width="100px" src="http://zimlab.hetadatain.com/assets/client_logo.png" id=""></span>
                        </td>
                    </tr>
                </table>
            </div>
            <center>
                <div>
                    <h2>ZIM Laboratories Limited Nagpur</h2>
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
                            <tr class="table_title"><th colspan="7"><center> '.$value['device_category'].'</center> </th></tr>
                            <tr>
                                <th>Sr. No.</th>
                             <th class="w-50" >Machine Name</th><th class="w-20" >DPR Machine Code</th>

                                <th>Eff</th>
						<th>Eff (Shifts)</th>
                                <th>Running Time</th>
                                <th>Off Time</th>
                            </tr>
                        </thead>';
                $row_tbT1 .=
                    '<tr ">
                        <th class="" >'.($index+1).'</th>
                        <th class="w-50" >'.$value['device_name'].'</th>
<th class="w-20" >'.$value['dpr_machine_code'].'</th>

                        <th class="" >'.$value['eff'].'</th>
<th class="" >'.$value['eff2'].'</th>

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
     $pdf->setPaper('A4', 'portrait');
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
