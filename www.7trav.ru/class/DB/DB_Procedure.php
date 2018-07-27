<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Работа с БД
 * А именно работа с хранимыми процедурами
 * 
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Core
 * @subpackage DB
 * @version 23.07.2008
 */
final class DB_Procedure
{
  /**
   * Выполнение хранимых процедур через метод перегрузки методов
   * $StoreProcedureName - название хранимой процедуры
   * $Params список параметров хранимой процедуры
   *
   * @param string $StoreProcedureName
   * @param array $Params
   * @return unknown
   */
  public function __call($StoreProcedureName, $Params)
  {
    $QuotedParams = array();
    foreach($Params as $Param) array_push($QuotedParams, $Param === null ? 'NULL' : "'" . DB::$DB->real_escape_string($Param) . "'");
    $sql = 'CALL ' . $StoreProcedureName . '(' . implode(',', $QuotedParams) . ');';
    /* execute multi query */
    //  if ( !DB::$DB->multi_query($sql) ) return self::Error_DB($sql, DB::$DB->error);
    if ( !DB::Query_Real($sql) ) return false;
    $results = array();
    do
    {
      //  if ( $result = DB::$DB->store_result() )
      if ( false != $result = DB::$DB->use_result() )
      {
        $rows = array();
        while ( false != $row = $result->fetch_assoc() ) $rows[] = $row;
        $result->close();
        $results[] = $rows;
      }
    }
    while ( DB::$DB->next_result() );
    //  while ( DB::$DB->more_results() &&  DB::$DB->next_result() );
    if ( 1 < count($results) ) return $results;
    if ( 1 < count($results[0]) ) return $results[0];
    if ( 1 < count($results[0][0]) ) return $results[0][0];
    return array_shift($results[0][0]);
  }
}