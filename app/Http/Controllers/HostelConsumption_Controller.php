<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class HostelConsumption_Controller extends Controller
{
    public function gethostelData(Request $request)
    {
        $date = $request->input('date');
        $client_id = strtolower($request->input('client_id'));  // Convert client_id to lowercase

        // Modify $client_id based on conditions
        $client_id2 = ($client_id === 'radhika') ? 'j8' : (($client_id === 'indira') ? 'j8' : "");
         // Set device_id_new based on client_id
        $device_id_new = ($client_id === 'radhika') ? 20 : (($client_id === 'indira') ? 21 : 0);

       

        $query = "
        SELECT Z1.dt_time,Z1.client_id,Z1.hour,
		
		CASE 
  WHEN Z2.value2 IS NULL THEN Z1.sum
  ELSE ROUND(Z2.value2 / 1000, 0) + Z1.sum 
END AS sum_total,
Z1.sum_ryb,
CASE 
  WHEN Z2.value2 IS NULL THEN pre_common_area
  ELSE (round(Z2.value2 / 1000,0) + Z1.sum) - Z1.sum_ryb
END AS common_area,

CASE 
  WHEN Z2.value2 IS NULL THEN pre_avg
  ELSE ROUND(((round(Z2.value2 / 1000,0) + Z1.sum) - Z1.sum_ryb ) / (round(Z2.value2 / 1000,0) + Z1.sum) * 100,0)
END AS avg,
		Z1.pre_common_area,Z1.pre_avg,Z1.sum,Z2.value2
		from  (SELECT dt_time,client_id,HOUR,sum_ryb,sum,
CASE WHEN com < 0 THEN 0 ELSE com END AS pre_common_area,
    CASE WHEN pre_avg < 0 THEN 0 ELSE pre_avg END AS pre_avg
     FROM (
            SELECT
            ROUND(SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END) / 1000) - ROUND(SUM(CASE WHEN q1.device_id BETWEEN 1 AND 30 THEN q2.ryb - q1.ryb END) / 1000) AS com,
            q2.dt_time,
            q2.client_id,
            q2.HOUR,
            ROUND(SUM(CASE WHEN q1.device_id BETWEEN 1 AND 30 THEN q2.ryb - q1.ryb END) / 1000) AS sum_ryb,
            ROUND(SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END) / 1000) AS sum,
            ROUND((SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END) - SUM(CASE WHEN q1.device_id BETWEEN 1 AND 30 THEN q2.ryb - q1.ryb END)) / 1000) AS pre_common_area,
            ROUND(((SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END) - SUM(CASE WHEN q1.device_id BETWEEN 1 AND 30 THEN q2.ryb - q1.ryb END)) / SUM(CASE WHEN q1.device_id = 31 THEN q2.total - q1.total END)) * 100) AS pre_avg
        
          FROM
              ( SELECT
                    r.hostel_id AS client_id,
                    h.dt_time,
                    h.device_id,
                    HOUR,
                    h.wh_1 +h.wh_2 + h.wh_3 AS ryb,
                    h.wh_r + h.wh_d AS total
                FROM hourly_kwh h
        JOIN room_mfd m ON h.client_id = m.client_id
        JOIN rooms r ON r.room_id = m.room_id
        WHERE date(h.dt_time) = ? and r.hostel_id = ? AND (h.device_id BETWEEN 1 AND 30 OR h.device_id = 31) AND HOUR = 23
        GROUP BY  h.device_id, h.hour) q1
          
          LEFT JOIN
          
              (SELECT h.dt_time,
                    r.hostel_id AS client_id,
                    h.device_id,
                    h.HOUR,
                    h.wh_1 + h.wh_2 + h.wh_3 AS ryb,
                    h.wh_r + h.wh_d AS total
                FROM hourly_kwh h
        JOIN room_mfd m ON h.client_id = m.client_id
        JOIN rooms r ON r.room_id = m.room_id
        WHERE date(h.dt_time) = (? + INTERVAL 1 DAY) and r.hostel_id = ? AND (h.device_id BETWEEN 1 AND 30 OR h.device_id = 31) AND  HOUR = 0
        GROUP BY  h.device_id, h.hour) q2
          
          ON q1.device_id = q2.device_id
          
          
        
        union
        
        
        
        SELECT
        ROUND(SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END) / 1000) - ROUND(SUM(CASE WHEN t1.device_id BETWEEN 1 AND 30 THEN t1.ryb - t2.ryb END) / 1000) AS com,
            t1.dt_time,
            t1.client_id,
            t1.HOUR,
            ROUND(SUM(CASE WHEN t1.device_id BETWEEN 1 AND 30 THEN t1.ryb - t2.ryb END) / 1000) AS sum_ryb,
            ROUND(SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END) / 1000) AS sum,
            ROUND((SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END) - SUM(CASE WHEN t1.device_id BETWEEN 1 AND 30 THEN t1.ryb - t2.ryb END)) / 1000) AS pre_common_area,
            ROUND(((SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END) - SUM(CASE WHEN t1.device_id BETWEEN 1 AND 30 THEN t1.ryb - t2.ryb END)) / SUM(CASE WHEN t1.device_id = 31 THEN t1.total - t2.total END)) * 100) AS pre_avg
        FROM
            (
                SELECT
                    r.hostel_id AS client_id,
                    h.dt_time,
                    h.device_id,
                    HOUR,
                    h.wh_1 +h.wh_2 + h.wh_3 AS ryb,
                    h.wh_r + h.wh_d AS total
                FROM hourly_kwh h
        JOIN room_mfd m ON h.client_id = m.client_id
        JOIN rooms r ON r.room_id = m.room_id
        WHERE date(h.dt_time) = ? and r.hostel_id = ? AND (h.device_id BETWEEN 1 AND 30 OR h.device_id = 31)
        GROUP BY  h.device_id, h.hour
        
            ) t1
        LEFT JOIN
            (
                SELECT
                    r.hostel_id AS client_id,
                    h.device_id,
                    h.HOUR,
                    h.wh_1 + h.wh_2 + h.wh_3 AS ryb,
                    h.wh_r + h.wh_d AS total
                FROM hourly_kwh h
        JOIN room_mfd m ON h.client_id = m.client_id
        JOIN rooms r ON r.room_id = m.room_id
        WHERE date(h.dt_time) = ? and r.hostel_id = ? AND (h.device_id BETWEEN 1 AND 30 OR h.device_id = 31)
        GROUP BY  h.device_id, h.hour
            ) t2
        ON t1.client_id = t2.client_id AND t1.device_id = t2.device_id AND t1.HOUR = t2.HOUR + 1
            WHERE t1.HOUR <> 0
            GROUP BY t1.client_id, t1.HOUR
        ) AS combined_results
        ORDER BY dt_time,HOUR ) Z1  LEFT JOIN (SELECT
      q2.dt_time,
      q2.hour,
      q2.client_Id,
      q2.device_id,
      ROUND(ABS(q1.wh_r - q2.wh_r),0)  AS value2
  FROM
      (SELECT dt_time, HOUR, client_Id, device_id, round(wh_r) AS wh_r
       FROM hourly_kwh
       WHERE DATE(dt_time) = ?
         AND HOUR = 23
         AND client_id = ?
         AND device_id = ?) q1
  
  LEFT JOIN
  
      (SELECT dt_time, HOUR, client_Id, device_id, round(wh_r) AS wh_r
       FROM hourly_kwh
       WHERE DATE(dt_time) = DATE(? + INTERVAL 1 DAY)
         AND HOUR = 0
         AND client_id = ?
         AND device_id = ?) q2
  
  ON q1.device_id = q2.device_id
  
  union


SELECT 
    dt_time,
    HOUR,
    client_id,
    device_id,
    round(wh_r,0) - COALESCE((SELECT wh_r FROM hourly_kwh h2 WHERE h2.dt_time < h1.dt_time AND h2.client_id = ? AND h2.device_id = ? ORDER BY h2.dt_time DESC LIMIT 1), 0) AS diff_wh_r
FROM 
    hourly_kwh h1
WHERE 
    DATE(dt_time) = ?
    AND client_id = ?
    AND device_id = ? AND HOUR != 0
ORDER BY 
    dt_time) Z2 ON Z1.hour = Z2. HOUR ORDER BY Z1.dt_time";

        $results = DB::select($query, [ $date,$client_id, $date, $client_id,$date,$client_id, $date, $client_id,$date,$client_id2,$device_id_new,$date,$client_id2,$device_id_new,$client_id2,$device_id_new,$date,$client_id2,$device_id_new]);

        return response()->json($results);

        // return response()->json([$client_id2,$device_id_new,$results]);

    }
}
