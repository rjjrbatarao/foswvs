<?php
require 'autoload.php';

$network = new Network();

$devices = $network->dhcp_leases();

if( empty($devices) ) exit;

$db = new Database();

foreach($devices as $device) {
  $db->mac_addr   = $device['mac'];
  $db->ip_addr    = $device['ip'];
  $db->hostname   = $device['host'];
  $db->updated_at = $device['begin'];

  if( !$db->get_device_id() ) {
    $db->add_device();
  }

  $db->update_device();
}
