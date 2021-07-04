<?php
class Device {
  public $id;
  public $ip;
  public $mac;
  public $ping;

  function __construct($client_ip) {
    $this->ip = $client_ip;
    $this->mac = $this->get_mac();
    $this->ping = $this->ping();
  }

  public function get_mac() {
    return exec("arp {$this->ip} | grep -o '..:..:..:..:..:..'");
  }

  public function ping() {
   return exec("ping 1.1.1.1 -c1 -W2|grep -oP '/(\d+\.\d+)/'|grep -oP '\d+\.\d+'");
  }
}
