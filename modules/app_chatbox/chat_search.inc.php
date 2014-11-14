<?php
/*
* @version 0.2 (auto-set)
*/

  global $session;
  global $msg;
  global $getdata;
  global $clear;

//#######################################################################################################
  if ($this->action=='admin' && $clear) {
   SQLExec("DELETE FROM shouts");
   $this->redirect("?");
  }
//#######################################################################################################
  if (!$session->data['SITE_USERNAME']) {
   $out['NOT_LOGGED']=1;
  } else {
   $user=SQLSelectOne("SELECT * FROM users WHERE USERNAME='".DBSafe($session->data['SITE_USERNAME'])."'");
   $session->data['logged_user']=$user['ID'];
  }

//#######################################################################################################
  if ($this->action=='' && $session->data['logged_user'] && $msg!='') {
   $rec=array();
   $rec['MEMBER_ID']=$session->data['logged_user'];
   $rec['MESSAGE']=htmlspecialchars($msg);
   $rec['ADDED']=date('Y-m-d H:i:s');
   SQLInsert('shouts', $rec);

   include_once(DIR_MODULES.'patterns/patterns.class.php');
   $pt=new patterns();

   $res=$pt->checkAllPatterns($rec['MEMBER_ID']);
   if (!$res) {
    processCommand($msg);
   }
   $getdata=1;
  }
//#######################################################################################################

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
//  $qry="1";

  if ($this->action!='admin') {
   $limit="LIMIT 50";
  }

  global $limit;
  if ($limit) {
   $this->limit=$limit;
  }

  if ($this->limit) {
   $limit="LIMIT ".$this->limit;
  } else {
   $limit="LIMIT 50";
  }

  $limit=str_replace('LIMIT LIMIT', 'LIMIT', $limit);

  $out['LIMIT']=$this->limit;

//  $res=SQLSelect("SELECT shouts.*, DATE_FORMAT(shouts.ADDED, '%H:%i') as DAT, TO_DAYS(shouts.ADDED) as DT, users.NAME, users.HOST FROM shouts LEFT JOIN users ON shouts.MEMBER_ID=users.ID WHERE $qry ORDER BY shouts.ADDED DESC, ID DESC $limit");
  $res=SQLSelect("SELECT shouts.*, DATE_FORMAT(shouts.ADDED, '%H:%i') as DAT, TO_DAYS(shouts.ADDED) as DT, users.NAME, users.HOST, users.AVATAR FROM shouts LEFT JOIN users ON shouts.MEMBER_ID=users.ID ORDER BY shouts.ADDED DESC, ID DESC $limit");

//  if ($_GET['reverse']) {
   $this->reverse=1;
//  }

  if (!$this->reverse) {
   $res=array_reverse($res);
  } else {
   $out['REVERSE']=1;
  }
//  $txtdata="<center><font size=5><b>".date('l d F Y')."</b></font></center><br><table border=0>";
  $txtdata="<center><font size=4><b>".day2str(date('w')).date(' d ').mon2str(date('m')).date(' Y')." года</b></font></center><br><table cellpadding=0 cellspasing=0 border=0>";

  if ($this->mobile) {
   $out['MOBILE']=1;
  }


  if ($res[0]['ID']) {
   $old_dt=$res[0]['DT'];
   $total=count($res);
   for($i=0;$i<$total;$i++) {

    // some action for every record if required
    $tmp=explode(' ', $res[$i]['ADDED']);
    $res[$i]['ADDED']=fromDBDate($tmp[0])." ".$tmp[1];
    if ($res[$i]['DT']!=$old_dt) {
        $txtdata.="</table><hr size=2><center><font size=5><b>".day2str(date('w',strtotime($tmp[0]))).date(' d ',strtotime($tmp[0])).mon2str(date('m',strtotime($tmp[0]))).date(' Y',strtotime($tmp[0]))." года</b></font></center><table cellpadding=0 cellspasing=0  border=0>";
        $old_dt=$res[$i]['DT'];
    }
    if ($res[$i]['MEMBER_ID']==0) {
     $res[$i]['NAME']='Алиса';
     $res[$i]['AVATAR']='10_14045.jpg';
    }

	$txtdata.="<tr><td style='font-family:LiquidCrystal;color:#00FF00;font-size:10pt' valign=middle>".$res[$i]['DAT']."</td><td align=center height=25>";
	if ($res[$i]['AVATAR']!=''){
	    $txtdata.="<img src='/cms/avatars/".$res[$i]['AVATAR']."' vallign='middle' border=0 width=25 height=25 alt=\"dddddddddddddd\">";
	}else{
	    $txtdata.="<b><font size=2 color=".$res[$i]['HOST'].">".$res[$i]['NAME']."</b>:</font> ";
	}
	$txtdata.="</td><td><font size=2 color=".$res[$i]['HOST']."> ".nl2br($res[$i]['MESSAGE'])."</font></td></tr>";

   }

   $txtdata.='</table>';
   $out['RESULT']=$res;
   $out['TXT_DATA']=$txtdata;

  } else {
   $txtdata.='No data';
  }
   $txtdata.='</table>';
  $out['SERVER_NAME']=$_SERVER['SERVER_NAME'];

  if ($this->action=='' && $getdata!='') {
   header ("HTTP/1.0: 200 OK\n");
   header ('Content-Type: text/html; charset=utf-8');
   echo $txtdata;
   $session->save();
   exit;
  }

function mon2str($mon){
    switch ($mon){
	case 01: $m="январья";break;
	case 02: $m="февраля";break;
	case 03: $m="марта";break;
	case 04: $m="апреля";break;
	case 05: $m="мая";break;
	case 06: $m="июня";break;
	case 07: $m="июля";break;
	case 08: $m="августа";break;
	case 09: $m="сентября";break;
	case 10: $m="октября";break;
	case 11: $m="ноября";break;
	case 12: $m="декабря";break;
    }
return $m;
}
function day2str($day){
    switch ($day){
	case 1: $m="Понедельник";break;
	case 2: $m="Вторник";break;
	case 3: $m="Среда";break;
	case 4: $m="Четверг";break;
	case 5: $m="Пятница";break;
	case 6: $m="Суббота";break;
	case 0: $m="Воскресенье";break;
    }
return $m;
}

?>
