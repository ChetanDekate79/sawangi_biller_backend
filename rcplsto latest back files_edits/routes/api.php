<?php

use Illuminate\Http\Request;
header('Access-Control-Allow-Origin : *');
header('Access-Control-Allow-Headers : Content-Type, X-Auth-Token, Authorization, Origin');
header('Access-Control-Allow-Methods : GET, POST');

ini_set('max_execution_time', 5000);
Route::group([

    'middleware' => 'api',

   /* 'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth'*/

], function ($router) {
 /********************************** working *************************************** */
    Route::post('ip', function(Request $request){
        return $request->ip();
    });

     

    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('sendPasswordResetLink','ResetPasswordController@sendEmail');
    Route::post('resetPassword','changePasswordController@process');


/************************************************************************************ */
    Route::apiResource('users','user@process');
    Route::apiResource('mail_users','user@process');
/********************************** live data API  *************************************** */
Route::get('getCurrentDateTime','EnergyController@getCurrentDateTime');  Route::get('energy/get_header_data_live','EnergyHeaderCard@getHeaderDataLive');
    Route::get('productivity/get_heatmap_data_live','EnergyHeatmap@getHeatmapDataLive');
    Route::get('productivity/get_all_machine_data_live','Machine@getMachineDataLive');
/********************************** working *************************************** */
    Route::get('energy/get_header_data_for_date/{date}','EnergyHeaderCard@getHeaderDataForDate');
    Route::get('energy/get_header_data_for_month/{date}','EnergyHeaderCard@getHeaderDataForMonth');
    Route::get('energy/get_header_data_for_year/{date}','EnergyHeaderCard@getHeaderDataForYear');
    Route::get('energy/get_header_data_monthly_for_year/{date}','EnergyHeaderCard@getHeaderDataMonthlyForYear');

    Route::get('productivity/get_heatmap_data_for_date/{date}','EnergyHeatmap@getHeatmapDataForDate');
    Route::get('productivity/get_heatmap_data_for_month/{date}','EnergyHeatmap@getHeatmapDataForMonth');
    Route::get('productivity/get_heatmap_data_for_year/{date}','EnergyHeatmap@getHeatmapDataForYear');
	Route::get('productivity/get_heatmap_data_for_date/{location}/{category}/{date}','ProductivityController@getHeatmapData');

    Route::get('productivity/get_all_machine_data_for_date/{date}','Machine@getMachineDataForDate');
    Route::get('productivity/get_all_machine_data_for_month/{date}','Machine@getMachineDataForMonth');
    Route::get('productivity/get_all_machine_data_for_year/{date}','Machine@getMachineDataForYear');

/************************ Energy **************************************getDeviceEnergyDataAll*************************************** */

    Route::get('analytics/energy/get_group_data_all/{group_id}/{date}','EnergyAnalysisController@getGroupEnergyDataAll');
    Route::get('analytics/energy/get_group_data/{group_id}/{year}/{month}/{day}','EnergyAnalysisController@getGroupEnergyDataForDay');
    Route::get('analytics/energy/get_group_data/{group_id}/{year}/{month}','EnergyAnalysisController@getGroupEnergyDataForMonth');
    Route::get('analytics/energy/get_group_data/{group_id}/{year}','EnergyAnalysisController@getGroupEnergyDataForYear');

    Route::get('analytics/energy/get_subgroup_data_all/{group_id}/{date}','EnergyAnalysisController@getSubGroupEnergyDataAll');
    Route::get('analytics/energy/get_subgroup_data/{group_id}/{year}/{month}/{day}','EnergyAnalysisController@getSubGroupEnergyDataForDay');
    Route::get('analytics/energy/get_subgroup_data/{group_id}/{year}/{month}','EnergyAnalysisController@getSubGroupEnergyDataForMonth');
    Route::get('analytics/energy/get_subgroup_data/{group_id}/{year}','EnergyAnalysisController@getSubGroupEnergyDataForYear');


    Route::get('analytics/energy/get_cost_data/{year}/{month}/{day}','EnergyCostAnalysis@getEnergyCostForDay');
    Route::get('analytics/energy/get_cost_data/{year}/{month}','EnergyCostAnalysis@getEnergyCostForMonth');
    Route::get('analytics/energy/get_cost_data/{year}','EnergyCostAnalysis@getEnergyCostForYear');
    Route::get('analytics/energy/get_cost_data_all/{date}','EnergyCostAnalysis@getEnergyCosAnalysis');

    Route::get('analytics/energy/get_device_data_all/{device_id}/{date}','EnergyAnalysisController@getDeviceEnergyDataAll');
    Route::get('analytics/energy/get_device_data/{device_id}/{year}/{month}/{day}','EnergyAnalysisController@getDeviceEnergyDataForDay');
    Route::get('analytics/energy/get_device_data/{device_id}/{year}/{month}','EnergyAnalysisController@getDeviceEnergyDataForMonth');
    Route::get('analytics/energy/get_device_data/{device_id}/{year}','EnergyAnalysisController@getDeviceEnergyDataForYear');

    /************************ Productivity* ************************************************************************** */
    Route::get('analytics/productivity/get_machine_data_all/{device_id}/{date}','ProductivityAnalysisController@getDeviceProductivityDataAll');
    Route::get('analytics/productivity/get_machine_data/{device_id}/{year}/{month}/{day}','ProductivityAnalysisController@getDeviceProductivityDataForDay');
    Route::get('analytics/productivity/get_machine_data/{device_id}/{year}/{month}','ProductivityAnalysisController@getDeviceProductivityDataForMonth');
    Route::get('analytics/productivity/get_machine_data/{device_id}/{year}','ProductivityAnalysisController@getDeviceProductivityDataForYear');
    /************************n  sensor api **************************************************************/
    Route::get('sensor/get_live_data/{date}','SensorController@getLiveData');
    Route::get('sensor/get_graph_data/{date}/{id}','SensorController@getGraphData');

    /**************************************************************************************/
    Route::get('info/department_list','@device_details');
	Route::get('analytics/energy/group/{group}/{date}','EnergyController@getGroupDeviceData');
    Route::get('info/category_list','@process');
    Route::get('info/feeder_list','device_details@getDeviceDetails');
    Route::get('info/md_feeder_list','device_details@getDeviceDetailsMD');
    
     Route::get('info/get_display_text','device_details@getDisplayText');
    Route::post('info/set_display_text','device_details@setDisplayText');
    Route::post('info/add_display_text','device_details@addDisplayText');
    Route::post('info/delete_display_text','device_details@deleteDisplayText');
    Route::get('info/delete_all_display_text','device_details@deleteAllDisplayText');


Route::get('analytics/productivity/machine/{year}','@process');
    Route::get('analytics/energy/feeder/{device}/{date}/{parameter}','EnergyController@getDeviceData');
	Route::get('analytics/productivity/feeder/{device}/{date}/{parameter}','ProductivityController@getDeviceData');


    Route::post('dynamic_pdf/pdf', 'DynamicPDFController@pdf');


    Route::get('analytics/energy/get_device_data_all_md/{device_id}/{date}','EnergyMDAnalysisController@getDeviceEnergyDataAll');
    Route::get('analytics/energy/get_device_data_md/{device_id}/{year}/{month}/{day}','EnergyMDAnalysisController@getDeviceEnergyDataForDay');
    Route::get('analytics/energy/get_device_data_md/{device_id}/{year}/{month}','EnergyMDAnalysisController@getDeviceEnergyDataForMonth');
    Route::get('analytics/energy/get_device_data_md/{device_id}/{year}','EnergyMDAnalysisController@getDeviceEnergyDataForYear');
    /************* report ********************************/
    Route::get('dailyEnergyReport/{opration}/{date}/{doc}/{type}', 'EnergyReport@getEnergyReport');
    Route::get('dailyEnergyReport/{opration}/{date}/{doc}', 'EnergyReport@getEnergyReport');
    Route::get('dailyEnergyReport/{opration}/{date}', 'EnergyReport@getEnergyReport');
    Route::get('dailyEnergyReport/{opration}', 'EnergyReport@getEnergyReport');
    Route::get('dailyEnergyReport', 'EnergyReport@getEnergyReport');



    Route::get('dailyEnergyReport7/{opration}/{date}/{doc}/{type}', 'EnergyReport7@getEnergyReport');
    Route::get('dailyEnergyReport7/{opration}/{date}/{doc}', 'EnergyReport7@getEnergyReport');
    Route::get('dailyEnergyReport7/{opration}/{date}', 'EnergyReport7@getEnergyReport');
    Route::get('dailyEnergyReport7/{opration}', 'EnergyReport7@getEnergyReport');
    Route::get('dailyEnergyReport7', 'EnergyReport7@getEnergyReport');




    Route::get('dailyProductivityReport/{opration}/{date}/{doc}/{type}', 'ProductivityReport@getReport');
    Route::get('dailyProductivityReport/{opration}/{date}/{doc}', 'ProductivityReport@getReport');
    Route::get('dailyProductivityReport/{opration}/{date}', 'ProductivityReport@getReport');
    Route::get('dailyProductivityReport/{opration}', 'ProductivityReport@getReport');
    Route::get('dailyProductivityReport', 'ProductivityReport@getReport');
	



     Route::get('dailyProductivityReport7/{opration}/{date}/{doc}/{type}', 'ProductivityReport7@getReport');
    Route::get('dailyProductivityReport7/{opration}/{date}/{doc}', 'ProductivityReport7@getReport');
    Route::get('dailyProductivityReport7/{opration}/{date}', 'ProductivityReport7@getReport');
    Route::get('dailyProductivityReport7/{opration}', 'ProductivityReport7@getReport');
    Route::get('dailyProductivityReport7', 'ProductivityReport7@getReport');



	
	Route::get('MultipleReport/{opration}/{date}/{doc}/{type}', 'MultipleAttachmentMail@getReport');
    Route::get('MultipleReport/{opration}/{date}/{doc}', 'MultipleAttachmentMail@getReport');
    Route::get('MultipleReport/{opration}/{date}', 'MultipleAttachmentMail@getReport');
    Route::get('MultipleReport/{opration}', 'MultipleAttachmentMail@getReport');
    Route::get('MultipleReport', 'MultipleAttachmentMail@getReport');

	 Route::get('MultipleReport7/{opration}/{date}/{doc}/{type}', 'MultipleAttachmentMail7@getReport');
    Route::get('MultipleReport7/{opration}/{date}/{doc}', 'MultipleAttachmentMail7@getReport');
    Route::get('MultipleReport7/{opration}/{date}', 'MultipleAttachmentMail7@getReport');
    Route::get('MultipleReport7/{opration}', 'MultipleAttachmentMail7@getReport');
    Route::get('MultipleReport7', 'MultipleAttachmentMail7@getReport');

    Route::get('getDeviceStatus', 'DeviceController@getDeviceStatus');

    /************* /report ********************************/
Route::get('EnergyReportHourly/{opration}/{date}/{doc}/{type}', 'EnergyReport24HrController@getReport');
    Route::get('EnergyReportHourly/{opration}/{date}/{doc}', 'EnergyReport24HrController@getReport');
    Route::get('EnergyReportHourly/{opration}/{date}', 'EnergyReport24HrController@getReport');
    Route::get('EnergyReportHourly/{opration}', 'EnergyReport24HrController@getReport');
    Route::get('EnergyReportHourly', 'EnergyReport24HrController@getReport');


      Route::get('EnergyReportHourly7/{opration}/{date}/{doc}/{type}', 'EnergyReport24HrController7@getReport');
    Route::get('EnergyReportHourly7/{opration}/{date}/{doc}', 'EnergyReport24HrController7@getReport');
    Route::get('EnergyReportHourly7/{opration}/{date}', 'EnergyReport24HrController7@getReport');
    Route::get('EnergyReportHourly7/{opration}', 'EnergyReport24HrController7@getReport');
    Route::get('EnergyReportHourly7', 'EnergyReport24HrController7@getReport');

	/***************************new-dashboard********************************** */

    Route::get('/svg/{date}','new_dash@getSvgData');
    Route::get('/NewDashboard/{date}/{keys}','new_dash@getGraph');



    Route::get('passwordReset', function () {
        // return view('passwordReset/welcome', ['name' => 'James']);
        return view('Email/passwordReset', ['token' => 'James']);
    });

 /*Route::get('/', function() {
    return View::make('index'); 
}); */
Route::any('{catchall}', function() {
  return View::make('index'); 
})->where('catchall', '.*');
});




