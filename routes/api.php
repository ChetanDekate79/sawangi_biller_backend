<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvDataController;
use App\Http\Controllers\Device_DetailsController;
use App\Http\Controllers\LoginControllernew;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DatetimeController;
use App\Http\Controllers\MeterStatusController;
use App\Http\Controllers\Testcontroller;
use App\Http\Controllers\Hourly_Graph_Controller;
use App\Http\Controllers\HostelConsumption_Controller;
use App\Http\Controllers\HostelConsumptionReport_Controller;
use App\Http\Controllers\EmptyRoomConsumptionReport_Controller;
use App\Http\Controllers\Generate_HourlyData_Controller;
use App\Http\Controllers\Student_noconsumption_report_Controller;
use App\Http\Controllers\Billing_Report_Controller;
use App\Http\Controllers\demo_controller;
use App\Http\Controllers\Monthly_Report_Controller;
use App\Http\Controllers\Bill_Controller;

Route::get('/monthly-bill/{hostel}/{room}/{month}/{year}/{rate}/{comm_area}', [Bill_Controller::class, 'Monthly_Bill']);


Route::get('/room-list/{hostel}', [Billing_Report_Controller::class, 'getDistinctRoomNumbers']);

Route::get('/monthly_consumption_report/{client_id}/{Month}/{Year}', [Monthly_Report_Controller::class, 'gethostelData']);

Route::get('/billing-report-monthly/{hostel}/{room}/{start_date}/{end_date}/{rate}/{comm_area}', [Billing_Report_Controller::class, 'billing_report_monthly']);

Route::get('/billing-report/{hostel}/{room}/{start_date}/{end_date}/{rate}', [Billing_Report_Controller::class, 'billing_report']);

Route::get("/demo",[demo_controller::class,'demo']);

Route::get('/empty_room_report', [EmptyRoomConsumptionReport_Controller::class, 'getemptyroomreport']);

Route::get('/student_noconsumption_report', [Student_noconsumption_report_Controller::class, 'getstudentnoconsumptionreport']);

Route::get('/generate_hourly_data/{folder}/{date}/{id}', [Generate_HourlyData_Controller::class, 'generate_data']);

Route::get('/generate_hourly_data_all/{folder}/{date}', [Generate_HourlyData_Controller::class, 'generate_data_all']);


Route::post('/download-pdf', 'HostelConsumptionReport_Controller@downloadPDF')->name('download-pdf');

Route::get('/hostel_consumption_report', [HostelConsumptionReport_Controller::class, 'gethostelData']);

Route::get('/hostel_consumption', [HostelConsumption_Controller::class, 'gethostelData']);

Route::get('/hourly_graph/{date}/{host}/{device}', [Hourly_Graph_Controller::class, 'hourly_graph']);

Route::get('/api/data', [CsvDataController::class, 'index']);

Route::get('/csv-data/{folder}/{date}/{id}', [CsvDataController::class, 'getByFolderDateId']);

Route::get('/csv-biller/{folder}/{date}/{id}', [CsvDataController::class, 'csv_biller']);

Route::get('/meter-status/{folder}/{date}', [MeterStatusController::class, 'processCsv']);

Route::get('/host', [Device_DetailsController::class, 'host']);

Route::get('/hostel', [Device_DetailsController::class, 'hostel']);

Route::get('/device', [Device_DetailsController::class, 'device']);

Route::get('/room', [Device_DetailsController::class, 'room']);

Route::post('/loginnew', [LoginControllernew::class, 'executeQuery']);

Route::get('/login', [LoginController::class, 'executeQuery']);

Route::get('current_datetime/{folder}/{date}', [DatetimeController::class, 'getCurrentDatetime']);

Route::get('/jnmc_report', [Testcontroller::class, 'generate_report']);