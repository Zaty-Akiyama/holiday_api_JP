<?php

class fetch_holiday
{
  public static function get_monthly_holiday( String $year, String $month )
  {
    $require_holiday = array();

    $fetch_archive = self::_fetch_archives( intval($year) );

    foreach ($fetch_archive as $key => $value) {
      if( !preg_match( "/^$year\-$month/", $key ) ) continue;

      $require_holiday[$key] = $value;
    }

    return $require_holiday;
  }

  /**
   * @return Array
   */
  private static function _fetch_archives ( int $year )
  {
    $holiday_json = null;
    $file_path = "./archives/$year";
    if( file_exists( $file_path ) )
    {
      $holiday_json = file_get_contents( $file_path . '/date.json' );
    }else
    {
      $holiday_json = self::create_archive_json( $year );
    }

    return json_decode( $holiday_json, true );
  }

  /**
   * この関数を使用する前に対象のディレクトリが存在しないことを確認する
   * 
   * @return String|Boolean Jsonを返す
   */
  private static function create_archive_json ( int $year )
  {
    $api_result = self::_fetch_holidays_from_google( $year );

    $formed_results = self::_forming_api_result( $api_result );

    if( $formed_results === false ) return;

    if( !file_exists( './archives' ) )
    {
      mkdir( "./archives", 0700 );
    }

    mkdir( "./archives/$year", 0700 );
    file_put_contents( "./archives/$year/date.json", $formed_results );

    return $formed_results;
  }

  private static function _fetch_holidays_from_google( int $year ){
    $start_month = mktime( 0, 0, 0, 1, 1, $year );
    $end_month   = mktime( 0, 0, 0, 1, 0, $year + 1);
  
    // Google API の アプリケーションキーは外部ファイルで保存
    include_once('./private.php');
    $app_key = get_application_key(); // private.phpで定義した関数

    $url = sprintf(
        "https://www.googleapis.com/calendar/v3/calendars/%s/events?key=%s&timeMin=%s&timeMax=%s&orderBy=startTime&singleEvents=true",
        'japanese__ja@holiday.calendar.google.com',
        $app_key,
        date('Y-m-d', $start_month).'T00:00:00Z',
        date('Y-m-d', $end_month).'T00:00:00Z'
    );
  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $responce = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($responce, true);
  
    return $result;
  }

  private static function _forming_api_result ( array $result )
  {
    if( !is_array($result) ) return false;
    if( !isset($result['summary']) || $result['summary'] !== '日本の祝日' ) return false;
  
    $public_holiday_yearly = array();
  
    foreach( $result['items'] as $items )
    {
      $summary = $items['summary'];
      $public_holiday_yearly[$items['start']['date']] = $summary;
    }
  
    $public_holiday_json = json_encode($public_holiday_yearly);
  
    return $public_holiday_json;
  }
}
?>