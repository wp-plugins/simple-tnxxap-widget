<?php
/*
Plugin Name: Simple TNX/XAP Widget
Plugin URI: http://svoibizonline.ru/simple-xap-widget
Author: mad31
Description: Simple TNX/XAP Widget automatically add code of system TNX.NET/XAP.RU on your blog in the sidebar. The knowledge of PHP and HTML is not necessary now.
Version: 1.2
Author URI: http://svoibizonline.ru
*/
/*
Copyright (C) 2009 svoibizonline.ru

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
//Вывод на страницу
function xap_widget($args) {
extract($args);
echo $before_widget;
if (get_option('xap_widget_title_select')==='1'){
  echo $before_title;
  echo get_option('xap_widget_title');
  echo $after_title;};
echo '<ul>';
$tnx = new TNX_l($_login = get_option('xap_widget_login'));
//Ссылки ХАР
echo $tnx->show_link(1);
echo $tnx->show_link(1);
echo $tnx->show_link(1);
echo $tnx->show_link();
echo '</ul>';
echo $after_widget;
}

//Функция регистрации виджета
function register_xap_widget() {
	register_sidebar_widget('Simple TNX/XAP widget', 'xap_widget');
	register_widget_control('Simple TNX/XAP widget', 'xap_widget_control' );
}
//Управление виджетом в админке
function xap_widget_control(){
    //Ваш логин в система ХАР
    if (!empty($_REQUEST['xap_widget_login'])) {
	update_option('xap_widget_login', $_REQUEST['xap_widget_login']);}?>
	Login in TNX/XAP:&nbsp;<i><b><?php echo get_option('xap_widget_login');?></b></i>
    <br /><input type="text" name="xap_widget_login" /><br />

    Show name of links block?<br />
    <input type="radio" onClick="show_hide('hidden')" name="xap_widget_title_select" value="0" <?php if (get_option('xap_widget_title_select')==='0') echo 'checked="checked"'; ?> > No
    <input type="radio" onClick="show_hide('visible')" name="xap_widget_title_select" value="1" <?php if (get_option('xap_widget_title_select')==='1') echo 'checked="checked"'; ?> > Yes<br />
    <?php if (get_option('xap_widget_title_select')==='1') echo 'Name of links block:&nbsp;<i><b>'.get_option('xap_widget_title').'</b></i>';

    if (isset($_REQUEST['xap_widget_title_select']))
        {
        if ($_REQUEST['xap_widget_title_select']==="0") update_option('xap_widget_title_select', '0');
        if ($_REQUEST['xap_widget_title_select']==="1") update_option('xap_widget_title_select', '1');
        }?>
<!-- Скрипт отображения блока названия-->
<script Language="JavaScript">
<!--
function show_hide(value)
{
    document.getElementById('element').style.visibility = value;
}
-->
</script>
<div id="element"  style="visibility: hidden">
    <?php if (!empty($_REQUEST['xap_widget_title'])) {
	update_option('xap_widget_title', $_REQUEST['xap_widget_title']);};?>
    <br /><input type="text" name="xap_widget_title" /><br />
</div>
<?php
    }
add_action('init', 'register_xap_widget');

//Код ХАР
class TNX_l  {
        var $_timeout_connect = 5; // таймаут - максимальное время ожидания загрузки ссылок, секунд
        var $_connect_using = 'fsock'; // curl или fsock - можно выбрать способ соединения
        var $_html_delimiter1 = '<li>'; // разделитель между ссылками, можно изменить на любой
        var $_html_delimiter2 = '</li><br>'; // разделитель между ссылками, можно изменить на любой
        var $_encoding = 'UTF-8'; // выбор кодировки вашего сайта. Пусто - win-1251 (по умолчанию). Также возможны: KOI8-U, UTF-8 (необходим модуль iconv на хостинге)
        var $_exceptions = 'PHPSESSID'; // здесь можно написать через пробел части урлов для запрещения их индексации системой, в т.ч. из robots.txt. Это урлы, не доступные поисковикам, или не существующие страницы. После индексации не менять.
        var $_return_point = 0;
        var $_content = '';
        var $_xaplink_check = 'Plugin work! Wait a <a href="http://svoibizonline.ru/" >links!</a>';
                function TNX_l($_login)
        {
                if($this->_connect_using == 'fsock' AND !function_exists('fsockopen')){echo 'Ошибка, внешние коннекты на хостинге отключены, обратитесь к хостеру или попробуйте CURL.'; return false;}
                if($this->_connect_using == 'curl' AND !function_exists('curl_init')){echo 'Ошибка, CURL не поддерживается, попробуйте fsock.'; return false;}
                if(!empty($this->_encoding) AND !function_exists("iconv")){echo 'Ошибка, iconv не поддерживается.'; return false;}

                if ($_SERVER['REQUEST_URI'] == '') $_SERVER['REQUEST_URI'] = '/';
                if (strlen($_SERVER['REQUEST_URI']) > 180) return false;

                if(!empty($this->_exceptions))
                {
                        $exceptions = explode(' ', $this->_exceptions);
                        for ($i=0; $i<sizeof($exceptions); $i++)
                        {
                                if($_SERVER['REQUEST_URI'] == $exceptions[$i]) return false;
                                if($exceptions[$i] == '/' AND preg_match("#^\/index\.\w{1,5}$#", $_SERVER['REQUEST_URI'])) return false;
                                if(strpos($_SERVER['REQUEST_URI'], $exceptions[$i]) !== false) return false;
                        }
                }

                $this->_login = strtolower($_login); $this->_host = $this->_login . '.tnx.net'; $file = base64_encode($_SERVER['REQUEST_URI']);
                $user_pref = substr($this->_login, 0, 2); $md5 = md5($file); $index = substr($md5, 0, 2);
                $site = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                $this->_path = '/users/' . $user_pref . '/' . $this->_login . '/' . $site. '/' . substr($md5, 0, 1) . '/' . substr($md5, 1, 2) . '/' . $file . '.txt';
                $this->_url = 'http://' . $this->_host . $this->_path;
                $this->_content = $this->get_content();
                if($this->_content !== false)
                {
                        $this->_content_array = explode('<br>', $this->_content);
                        for ($i=0; $i<sizeof($this->_content_array); $i++)
                        {
                                $this->_content_array[$i] = trim($this->_content_array[$i]);
                        }
                }
        }
        function show_link($num = false)
        {
                if(!isset($this->_content_array)) return false;
                $links = '';
                if(!isset($this->_content_array_count)){$this->_content_array_count = sizeof($this->_content_array);}
                if($this->_return_point >= $this->_content_array_count) return false;

                if($num === false OR $num >= $this->_content_array_count)
                {
                        for ($i = $this->_return_point; $i < $this->_content_array_count; $i++)
                        {
                          if (empty($this->_content_array[$i])) $links .= $this->_html_delimiter1 . $this->_xaplink_check . $this->_html_delimiter2;
                                $links .= $this->_html_delimiter1 . $this->_content_array[$i] . $this->_html_delimiter2;
                        }
                        $this->_return_point += $this->_content_array_count;
                }
                else
                {
                        if($this->_return_point + $num > $this->_content_array_count) return false;
                        for ($i = $this->_return_point; $i < $num + $this->_return_point; $i++)
                        {
                          if (empty($this->_content_array[$i])) $links .= $this->_html_delimiter1 . $this->_xaplink_check . $this->_html_delimiter2;
                                $links .= $this->_html_delimiter1 . $this->_content_array[$i] . $this->_html_delimiter2;
                        }
                        $this->_return_point += $num;
                }
                return (!empty($this->_encoding)) ? iconv("windows-1251", $this->_encoding, $links) : $links;
        }
        function get_content()
        {
                $user_agent = 'TNX_l ip: ' . $_SERVER['REMOTE_ADDR'];
                $page = '';
                if ($this->_connect_using == 'curl' OR ($this->_connect_using == '' AND function_exists('curl_init')))
                {
                        $c = curl_init($this->_url);
                        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $this->_timeout_connect);
                        curl_setopt($c, CURLOPT_HEADER, false);
                        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($c, CURLOPT_TIMEOUT, $this->_timeout_connect);
                        curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
                        $page = curl_exec($c);
                        if(curl_error($c) OR (curl_getinfo($c, CURLINFO_HTTP_CODE) != '200' AND curl_getinfo($c, CURLINFO_HTTP_CODE) != '404') OR strpos($page, 'fsockopen') !== false)
                        {
                                curl_close($c);
                                return false;
                        }
                        curl_close($c);
                }
                elseif($this->_connect_using == 'fsock')
                {
                        $buff = '';
                        $fp = @fsockopen($this->_host, 80, $errno, $errstr, $this->_timeout_connect);
                        if ($fp)
                        {
                                fputs($fp, "GET " . $this->_path . " HTTP/1.0\r\n");
                                fputs($fp, "Host: " . $this->_host . "\r\n");
                                fputs($fp, "User-Agent: " . $user_agent . "\r\n");
                                fputs($fp, "Connection: Close\r\n\r\n");

                                stream_set_blocking($fp, true);
                                stream_set_timeout($fp, $this->_timeout_connect);
                                $info = stream_get_meta_data($fp);

                                while ((!feof($fp)) AND (!$info['timed_out']))
                                {
                                        $buff .= fgets($fp, 4096);
                                        $info = stream_get_meta_data($fp);
                                }
                                fclose($fp);

                                if ($info['timed_out']) return false;

                                $page = explode("\r\n\r\n", $buff);
                                $page = $page[1];
                                if((!preg_match("#^HTTP/1\.\d 200$#", substr($buff, 0, 12)) AND !preg_match("#^HTTP/1\.\d 404$#", substr($buff, 0, 12))) OR $errno!=0 OR strpos($page, 'fsockopen') !== false) return false;
                        }
                }
                if(strpos($page, '404 Not Found')) return '';
                return $page;
        }
}
?>