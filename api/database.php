<?php
class Database extends SQLite3 {
  public $sid;
  public $devid;
  public $mac_addr;

  function __construct() {
    $dbf = __DIR__ . '/foswvs.db';

    $this->open($dbf);
    $this->exec("PRAGMA busy_timeout=300");
    $this->exec("PRAGMA journal_mode=WAL;");

    $file = stat($dbf);

    if( $file['size'] <= 4096 ) {
      chmod($dbf, 0777);
      $this->create_table();
    }
  }

  function __destruct() {
    $this->close();
  }

  public function create_table() {
    $this->exec("CREATE TABLE devices (id INTEGER PRIMARY KEY AUTOINCREMENT, mac_addr BLOB UNIQUE, session_id INTEGER, created_at DEFAULT CURRENT_TIMESTAMP, updated_at DEFAULT CURRENT_TIMESTAMP)");
    $this->exec("CREATE TABLE session (id INTEGER PRIMARY KEY AUTOINCREMENT, device_id INTEGER, piso_count INTEGER DEFAULT 0, mb_credit INTEGER DEFAULT 0, mb_used INTEGER DEFAULT 0, created_at DEFAULT CURRENT_TIMESTAMP, updated_at DEFAULT CURRENT_TIMESTAMP)");
  }

  public function add_device() {
    $this->exec("INSERT INTO devices(mac_addr) VALUES('{$this->mac_addr}')");

    $this->devid = $this->lastInsertRowID();
  }

  public function get_device_id() {
    $res = $this->query("SELECT id FROM devices WHERE mac_addr='{$this->mac_addr}'");
    $row = $res->fetchArray(SQLITE3_ASSOC);

    return $this->devid = $row['id'];
  }

  public function set_device_session() {
    $this->exec("UPDATE devices SET session_id={$this->sid} WHERE id={$this->devid}");
  }

  public function get_device_session() {
    $res = $this->query("SELECT session_id FROM devices WHERE id={$this->devid}");
    $row = $res->fetchArray(SQLITE3_NUM);

    $this->sid = $row[0] ? $row[0] : 0;

    return $this->sid;
  }

  public function add_session() {
    $this->exec("INSERT INTO session(device_id) VALUES({$this->devid})");

    $this->sid = $this->lastInsertRowID();
  }

  public function set_piso_count($piso) {
    $this->exec("UPDATE session SET piso_count={$piso} WHERE id={$this->sid}");
  }

  public function get_piso_count() {
    $res = $this->query("SELECT piso_count FROM session WHERE id={$this->sid}");
    $row = $res->fetchArray(SQLITE3_NUM);

    return $row[0] ? $row[0] : 0;
  }

  public function set_mb_credit($mb) {
    $this->exec("UPDATE session SET mb_credit={$mb} WHERE id={$this->sid}");
  }

  public function get_mb_credit() {
    $res = $this->query("SELECT mb_credit FROM session WHERE id={$this->sid}");
    $row = $res->fetchArray(SQLITE3_NUM);

    return $row[0] ? $row[0] : 0;
  }

  public function get_total_mb_credit() {
    $res = $this->query("SELECT SUM(mb_credit) FROM session WHERE device_id={$this->devid}");
    $row = $res->fetchArray(SQLITE3_NUM);

    return $row[0] ? $row[0] : 0;
  }

  public function set_mb_used($mb) {
    $this->exec("UPDATE session SET mb_used=(mb_used + {$mb}) WHERE id={$this->sid}");
  }

  public function get_mb_used() {
    $res = $this->query("SELECT mb_used FROM session WHERE id={$this->sid}");
    $row = $res->fetchArray(SQLITE3_NUM);

    return $row[0] ? $row[0] : 0;
  }

  public function get_total_mb_used() {
    $res = $this->query("SELECT sum(mb_used) FROM session WHERE device_id={$this->devid}");
    $row = $res->fetchArray(SQLITE3_NUM);

    return $row[0] ? $row[0] : 0;
  }
}
