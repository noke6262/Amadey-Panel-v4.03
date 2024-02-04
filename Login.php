<?php

   session_start();

   include("Cfg/Config.php");
   include("Cfg/Lang.php");

   {
      echo "      
      <head>
        <meta charset=\"UTF-8\">
        <title>CC [" . $_SERVER['SERVER_NAME'] . "]</title>
        <link rel=\"stylesheet\" href=\"Css\Style.css\">
        <link rel=\"shortcut icon\" href=\"Images\Ico.ico\" type=\"image/x-icon\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
      </head>";

      echo "<table border=\"0\" width=\"100%\" height=\"300\">
               <tr>
                  <td>
                     <div align = center>" . $lang["0164"] . "</div>
                  </td>
               </tr>
            </table>";

      @header("Refresh: 5; url = " . basename($_SERVER["SCRIPT_FILENAME"]));
      exit;
   }

      @session_destroy();
      header("Location: " . basename($_SERVER["SCRIPT_FILENAME"]));
      exit;

   if (isset($_POST["login"]) && isset($_POST["password"]))
   {
      $login = $_POST["login"];
      $password = $_POST["password"];

      if (($login == $conf["login"]) && (md5($password) == $conf["password"]))
      {
         $_SESSION["Name"] = strtoupper(substr($conf["login"], 0, 1)) . substr($conf["login"], 1, 255);
         @header("Refresh: 0; url = Statistic.php");
      }
      else
      {
         if (($login == $conf["observer_login"]) && (md5($password) == $conf["observer_password"]))
         {
            $_SESSION["Name"] = $conf["observer_login"];
            @header("Refresh: 0; url = Statistic.php");
         }
         else
         {
            @header("Refresh: 0; url = " . basename($_SERVER["SCRIPT_FILENAME"]) . "?wrong=1");
         }
      }
   }
    
   echo "<html>
            <head>
               <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">
               <link rel=\"stylesheet\" type=\"text/css\" href=\"Css\Style.css\">
               <title>Authorization</title>
            </head>

            <body>
                  <table border=\"0\" width=\"100%\" height=\"100%\">
                     <tr>
                        <td align=center>
                           <form action=\"" . basename($_SERVER["SCRIPT_FILENAME"]) . "\" method=\"post\"> 
                              <table width=\"515\" height=\"481\">
                                 <tr>
                                    <td align=center>
                                       <table border=\"0\" height=\"120\" cellpadding=\"0\" cellspacing=\"0\">
                                          <tr>
                                             <td></td> 
                                             <td></td>
                                          </tr>
                                          <tr>
                                             <td></td>
                                             <td align=left><input type=\"text\" class=task value=\"" . $_GET["l"] . "\" name=\"login\"></td>
                                          </tr>
                                          <tr>  
                                             <td></td>                                     
                                             <td><input type=\"password\" class=task value=\"" . $_GET["p"] . "\" name=\"password\"></td>
                                          </tr>
                                          <tr>   
                                             <td></td>
                                             <td>
                                                <div align=\"center\">
                                                   <input type=\"submit\" class=\"button\" value=\"" . $lang["__00"] . "\">
                                                </div> 
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                              </table>

                           </form>
                        </td>
                     </tr>
                  </table>
            </body>
         </html>";   
?>


