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
use App\Http\Controllers\demo_controller;

Route::get("/demo",[demo_controller::class,'demo']);

Route::get('/empty_room_report', [EmptyRoomConsumptionReport_Controller::class, 'getemptyroomreport']);


Route::get('/generate_hourly_data/{folder}/{date}/{id}', [Generate_HourlyData_Controller::class, 'generate_data']);

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

Route::post('/loginnew', [LoginControllernew::class, 'executeQuery']);

Route::get('/login', [LoginController::class, 'executeQuery']);

Route::get('current_datetime/{folder}/{date}', [DatetimeController::class, 'getCurrentDatetime']);

Route::get('/jnmc_report', [Testcontroller::class, 'generate_report']);
