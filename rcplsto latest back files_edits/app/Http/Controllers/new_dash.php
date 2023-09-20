<?php
namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\cache;
use Illuminate\Http\Request;

class new_dash extends Controller
{

    public function getSvgData($date)
    {

       $data = DB::select("
       select `date`,`date_time`,time(date_time) as time,`device_id`,`a`, `a2`,`a2i`, `a2h`,`a3`,`a3i`,`kva18`,`s`,
       `b`,`bi`,`bh`,`c`,`ci`,`ch`,`kva19`,`n3`,`n`,`ni`,`nh`,`n2`,`n2i`,`kva23`,`h2`,`h`,`hi`,`hh`,`i`,`ii`,
       `ih`,`kva22`,`o3`,`o`,`oi`,`oh`,`o2`,`o2i`,`kva24`,`t`,`d`,`di`,`dh`,`e`,`ei`,`eh`,`kva20`,`j2`,`j`,`ji`,
       `jh`,`k`,`ki`,`kh`,`kva25`,`p2`,`p`,`pi`,`ph`,`p1`,`p1i`,`kva26`,`u`,`f`,`fi`,`fh`,`g`,`gi`,`gh`,`kva21`,
       `l1`,`l`,`li`,`lh`,`m`,`mi`,`mh`,`kva14`,`q2`,`q`,`qi`,`qh`,`q1`,`q1i`,`kva15`,`r2`,`r`,`ri`,`rh`,`r1`,
       `r1i`,`kva17`,`z2`,`z`,`zi`,`zh`,`z1`,`z1i`,`kva13`,`x2`,`x`,`xi`,`xh`,`x1`,`x1i`,`kva16`,`vm1`,`vm2`,
       `vmload`,`tr1_use`,`tr1_loss`,`tr1_loading`,`tr2_use`,`tr2_loss`,`tr2_loading`,`tr3_use`,`tr3_loss`,
       `tr3_loading`,`pv1`,`pv2`,`pv3`,`cnpvl`,`ctrlk`,`total_loss`,`totalrs`
        from svg_summary 
        where date = '$date' 
        order by date_time
      
       "); 

       return $data;

    }

    public function getGraph($date,$keys){
        $query = $this->getQueryKeys($keys);
    
        $data = DB::connection(@$this->conn)->select($query,[$date]);
    
       return response()->json([
           'data' => $data
       ], 200);
    
    }

    public function getQueryKeys($keys){

    switch($keys) {
        
        case "use1": return "select date_time as date, tr1_use as 'value1', tr2_use as 'value2',
                            tr3_use as 'value3',  tr1_loss as 'value4', tr2_loss as 'value5',
                            tr3_loss as 'value6', tr1_loading as 'value7', tr2_loading as 'value8',
                            tr3_loading as 'value9'
                            from svg_summary
                            where date = ? 
                            and tr1_use <> '0'
                            and tr2_use <> '0'
                            and tr3_use <> '0'
                            and tr1_loss <> '0'
                            and tr1_loading <> '0'
                            and tr2_loss <> '0'
                            and tr2_loading <> '0'
                            and tr3_loss <> '0'
                            and tr3_loading <> '0'";
          
          /* return "select date_time as date, tr1_use as 'TR 1 Use %', tr2_use as 'TR 2 Use %',
                            tr3_use as 'TR 3 Use % ',  tr1_loss as 'TR 1 Loss % ', tr1_loading as 'TR 1 Loading %',
                            tr2_loss as 'TR 2 Loss % ', tr2_loading as 'TR 2 Loading %',
                            tr3_loss as 'TR 3 Loss % ', tr3_loading as 'TR 3 Loading %'
                            from svg_summary
                            where date = ? 
                            and tr1_use <> '0'
                            and tr2_use <> '0'
                            and tr3_use <> '0'
                            and tr1_loss <> '0'
                            and tr1_loading <> '0'
                            and tr2_loss <> '0'
                            and tr2_loading <> '0'
                            and tr3_loss <> '0'
                            and tr3_loading <> '0'"; */
        break; 
        
        case "use2": return "select date_time as date, tr1_use as 'value1', tr2_use as 'value2',
                            tr3_use as 'value3',  tr1_loss as 'value4', tr2_loss as 'value5',
                            tr3_loss as 'value6', tr1_loading as 'value7', tr2_loading as 'value8',
                            tr3_loading as 'value9'
                            from svg_summary
                            where date = ? 
                            and tr1_use <> '0'
                            and tr2_use <> '0'
                            and tr3_use <> '0'
                            and tr1_loss <> '0'
                            and tr1_loading <> '0'
                            and tr2_loss <> '0'
                            and tr2_loading <> '0'
                            and tr3_loss <> '0'
                            and tr3_loading <> '0'";
        break;  

        case "use3": return "select date_time as date, tr1_use as 'value1', tr2_use as 'value2',
                            tr3_use as 'value3',  tr1_loss as 'value4', tr2_loss as 'value5',
                            tr3_loss as 'value6', tr1_loading as 'value7', tr2_loading as 'value8',
                            tr3_loading as 'value9'
                            from svg_summary
                            where date = ? 
                            and tr1_use <> '0'
                            and tr2_use <> '0'
                            and tr3_use <> '0'
                            and tr1_loss <> '0'
                            and tr1_loading <> '0'
                            and tr2_loss <> '0'
                            and tr2_loading <> '0'
                            and tr3_loss <> '0'
                            and tr3_loading <> '0'";
        break;  
        
        /*case "loss1": return "select date_time as date, tr1_loss as 'TR 1 Loss % ', tr1_loading as 'TR 1 Loading %',
                            tr2_loss as 'TR 2 Loss % ', tr2_loading as 'TR 2 Loading %',
                            tr3_loss as 'TR 3 Loss % ', tr3_loading as 'TR 3 Loading %'
                            from svg_summary
                            where date = ? 
                            and tr1_loss <> '0'
                            and tr1_loading <> '0'
                            and tr2_loss <> '0'
                            and tr2_loading <> '0'
                            and tr3_loss <> '0'
                            and tr3_loading <> '0' ";
        break;  

        case "loss2": return  "select date_time as date, tr1_loss as 'TR 1 Loss % ', tr1_loading as 'TR 1 Loading %',
                              tr2_loss as 'TR 2 Loss % ', tr2_loading as 'TR 2 Loading %',
                              tr3_loss as 'TR 3 Loss % ', tr3_loading as 'TR 3 Loading %'
                              from svg_summary
                              where date = ? 
                              and tr1_loss <> '0'
                              and tr1_loading <> '0'
                              and tr2_loss <> '0'
                              and tr2_loading <> '0'
                              and tr3_loss <> '0'
                              and tr3_loading <> '0' ";
        break;  

        case "loss3": return  "select date_time as date, tr1_loss as 'TR 1 Loss % ', tr1_loading as 'TR 1 Loading %',
                              tr2_loss as 'TR 2 Loss % ', tr2_loading as 'TR 2 Loading %',
                              tr3_loss as 'TR 3 Loss % ', tr3_loading as 'TR 3 Loading %'
                              from svg_summary
                              where date = ? 
                              and tr1_loss <> '0'
                              and tr1_loading <> '0'
                              and tr2_loss <> '0'
                              and tr2_loading <> '0'
                              and tr3_loss <> '0'
                              and tr3_loading <> '0' ";
        break; 
        
        */

        case "pv1":return "select date_time as date, pv1 as 'value1', pv2 as 'value11',
                            pv3 as 'value2'
                            from svg_summary
                            where date = ?  ";
        break;
        
        case "pv2": return "select date_time as date, pv1 as 'value1', pv2 as 'value11',
                            pv3 as 'value2'
                            from svg_summary
                            where date = ?  ";
        break; 
        case "pv3": /* return "select date_time as date, pv1 as 'Solar 1 kWh/kWp', pv2 as 'Solar 2 kWh/kWp',
                            pv3 as 'Solar 3 kWh/kWp'
                            from svg_summary
                            where date = ?  "; */

                            return "select date_time as date, pv1 as 'value1', pv2 as 'value11',
                            pv3 as 'value2'
                            from svg_summary
                            where date = ?  ";

        break; 

        case "vm1": return /* "select date_time as date, vm1 as 'Load 1 kW', vm2 as 'Load 2 kW',
                            vmload as 'Load 3 kW' */
                            "select date_time as date, vm1 as 'value1', vm2 as 'value11',
                            vmload as 'value2'
                            from svg_summary
                            where date = ? 
                            and vm1 <> '0'
                            and vm2 <> '0'
                            and vmload <> '0' ";
        break; 

       /*  case "vm2": return "select date_time as date, vm1 as 'Load 1 kW', vm2 as 'Load 2 kW',
                            vmload as 'Load 3 kW'
                            from svg_summary
                            where date = ? 
                            and vm1 <> '0'
                            and vm2 <> '0'
                            and vmload <> '0' ";
        break; 

        case "vmload": return "select date_time as date, vm1 as 'Load 1 kW', vm2 as 'Load 2 kW',
                              vmload as 'Load 3 kW'
                              from svg_summary
                              where date = ? 
                              and vm1 <> '0'
                              and vm2 <> '0'
                              and vmload <> '0' ";
        break; 
 */
        case "incomer": return "select date_time as date, ifnull(a,0) as 'value1', ifnull(a2i,0) as 'value2',
                                ifnull(kva18,0) as 'value11'
                                from svg_summary
                                where date = ? 
                                and a <> '0'
                                and a2i <> '0'
                                and kva18 <> '0'
                                order by date_time ";
        break;  

        case "19kwh": return "select date_time as date, bi as 'value1',
                              ci as 'value11', s as 'value2' 
                            from svg_summary
                            where date = ? 
                            and s <> '0'
                            and bi <> '0'";
        break;
        
       /*  case "20kwh": return "select date_time as date, di as 'kWh Received',
                              ei as 'kWh Delivered', t as 'kW' 
                            from svg_summary
                            where date = ? 
                            and t <> '0'
                            and di <> '0'
                            ";
        break; */

        case "20kwh": return "select date_time as date, di as 'value1',
                              ei as 'value11', t as 'value2' 
                              from svg_summary
                              where date = ? 
                              and t <> '0'
                              and di <> '0'
                              ";
      break;


        case "21kwh": return "select date_time as date, fi as 'value1',
                              gi as 'value11', u as 'value2' 
                            from svg_summary
                            where date = ? 
                            and u <> '0'
                            and fi <> '0'
                            ";
        break;

        case "22kwh": return "select date_time as date, hi as 'value1',
                              ii as 'value11', h2 as 'value2' 
                            from svg_summary
                            where date = ? 
                            and h2 <> '0' 
                            and hi <> '0'
                            ";
        break;

        case "25kwh": return "select date_time as date, ji as 'value1',
                              ki as 'value11', j2 as 'value2' 
                            from svg_summary
                            where date = ? 
                            and j2 <> '0'
                            and ji <> '0'
                            ";
        break;

        case "14kwh": return "select date_time as date, li as 'value1',
                              mi as 'value11', l1 as 'value2' 
                            from svg_summary
                            where date = ? 
                            and l1 <> '0'
                            and li <> '0'
                            ";
        break;

        case "23kwh": return /* "select date_time as date, ni as 'kWh Received', n3 as 'kW', 
                              kva23 as 'kva'  */ 
                            "select date_time as date, ni as 'value2', n3 as 'value1',
                            kva23 as 'value11'                        
                            from svg_summary
                            where date = ? 
                            and n3 <> '0'
                            and ni <> '0'
                            or kva23 <> '0' ";
        break;

        case "26kwh": return /* "select date_time as date, pi as 'kWh Received', p2 as 'kW', 
                             kva26 as 'kva'  */ 
                           "select date_time as date, pi as 'value2', p2 as 'value1',
                            kva26 as 'value11'                         
                            from svg_summary
                            where date = ? 
                            and p2 <> '0'
                            and pi <> '0'
                            or kva26 <> '0' ";
        break;

        case "15kwh": return /* "select date_time as date, qi as 'kWh Received', q2 as 'kW', 
                            kva15 as 'kva'  */ 
                            "select date_time as date, qi as 'value2', q2 as 'value1',
                            kva15 as 'value11'                       
                            from svg_summary
                            where date = ? 
                            and q2 <> '0'
                            and qi <> '0'
                            or kva15 <> '0' ";

                          /*  "SELECT * from
(select date_time as DATE,  CAST(qi AS UNSIGNED) as 'kWh Received', CAST(q2 AS UNSIGNED) as 'kW', 
                            CAST(kva15 AS UNSIGNED) as 'kva'                        
                            from svg_summary
                            where DATE = '2021-06-18'
                            ) a
                  WHERE     `kWh Received`  NOT like 0	AND (kW NOT like 0
						 or  kva NOT LIKE 0)"; */
						
        break;

        case "24kwh": return "select date_time as date, oi as 'kWh Received', o3 as 'kW'                        
                            from svg_summary
                            where date = ? 
                            and o3 <> '0' 
                            and oi <> '0'";
        break;

        case "17kwh": return "select date_time as date, ri as 'kWh Received', r2 as 'kW'                        
                            from svg_summary
                            where date = ?
                            and r2 <> '0' 
                            and ri <> '0' ";
        break;
        
        default : "not match" ;

    } 
   }

}

