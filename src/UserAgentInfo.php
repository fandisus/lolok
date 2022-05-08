<?php //$_SERVER["REMOTE_ADDR"]

namespace Fandisus\Lolok;

use Jenssegers\Agent\Agent;

class UserAgentInfo {
  public $ip, $browser, $browser_ver, $platform, $platform_ver, $device;
  public function __construct() {
    $this->ip = $_SERVER["REMOTE_ADDR"]; // at Laravel version, have to add Request $request to parameter. And this will be $request->ip();
    $agent = new Agent();
    $this->browser = $agent->browser();
    $this->browser_ver = $agent->version($this->browser);
    $p = $agent->platform();
    $pver = $agent->version($p);
    $this->platform = "$p $pver";
    $this->device = $agent->device();
  }
  public function compare($ip, $browser, $browser_ver, $platform, $platform_ver, $device) {
    if ($this->ip !== $ip) return 'Different IP';
    if ($this->browser !== $browser) return 'Different browser';
    if ($this->browser_ver !== $browser_ver) return 'Different browser version';
    if ($this->platform !== $platform) return 'Different platform';
    if ($this->platform_ver !== $platform_ver) return 'Different platform version';
    if ($this->device !== $device) return 'Different device';
    return '';
  }
  public function compareObj($obj) {
    if ($this->ip !== $obj->ip) return 'Different IP';
    if ($this->browser !== $obj->browser) return 'Different browser';
    if ($this->browser_ver !== $obj->browser_ver) return 'Different browser version';
    if ($this->platform !== $obj->platform) return 'Different platform';
    if ($this->platform_ver !== $obj->platform_ver) return 'Different platform version';
    if ($this->device !== $obj->device) return 'Different device';
    return '';
  }
}
