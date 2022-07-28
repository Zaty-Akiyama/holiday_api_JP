<?php
$year = $_GET['year'];
$month = $_GET['month'];

$request_uri = $_SERVER['REQUEST_URI'];

$error_msg = '';
$request_holiday_array = null;

if( !isset($year) || $year === '' || !isset($month) || $month === '' )
{
  $error_msg = 'yearパラメータとmonthパラメータがセットされていません';

} else
{
  define( 'API_PATH' , __DIR__ . '/fetch' );
  include_once API_PATH . '/fetch_holiday.php';

  $fetch = new fetch_holiday;
  $fetch_holiday_array = $fetch->get_monthly_holiday( $year, $month );

  if( !$fetch_holiday_array )
  {
    $error_msg = 'API ERROR';
  }
  $request_holiday_array = $fetch_holiday_array;
}

if( $error_msg !== '' )
{
  $error = [ 'error' => $error_msg ];
  echo json_encode( $error );
  exit;
}

echo json_encode($request_holiday_array);