<?php
/**
* Title
*
* Description
*
* @access public
*/
 function saydym($ph, $level=0, $ding=1, $member_id=0) 
 {
        global $commandLine;
        global $voicemode;

         /*
          if ($commandLine) {
           echo utf2win($ph);
          } else {
           echo $ph;
          }
          */
DebMes('SAY FUNC: '.$ph);

        $rec = array();
        $rec['MESSAGE']   = $ph;
        $rec['ADDED']     = date('Y-m-d H:i:s');
        $rec['ROOM_ID']   = 0;
        $rec['MEMBER_ID'] = $member_id;
//        $rec['SUBSYSTEM_ID'] = $member_id;
  
        if ($level>0) $rec['IMPORTANCE']=$level;
        
        $rec['ID'] = SQLInsert('shouts', $rec);

        if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY!='') {
         eval(SETTINGS_HOOK_BEFORE_SAY);
        }

        if ($level >= (int)getGlobal('minMsgLevel'))
        { 
                //$voicemode!='off' && 

           $lang='en';
           if (defined('SETTINGS_SITE_LANGUAGE')) {
                $lang=SETTINGS_SITE_LANGUAGE;
           }
           if (defined('SETTINGS_VOICE_LANGUAGE')) {
                $lang=SETTINGS_VOICE_LANGUAGE;
           }

           if (!defined('SETTINGS_TTS_GOOGLE') || SETTINGS_TTS_GOOGLE) {
                $google_file=GoogleTTS($ph, $lang);
           } else {
                $google_file=false;
           }

           if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL=='1') {
              $passed=SQLSelectOne("SELECT (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ADDED)) as PASSED FROM shouts WHERE ID!='".$rec['ID']."' ORDER BY ID DESC LIMIT 1");
              if ($passed['PASSED']>20) { // play intro-sound only if more than 30 seconds passed from the last one
		    if ($ding == 1){playSoundDym('dingdong', 1, $level);}
		    if ($ding == 2){playSoundDym('dingdong', 1, $level);}
		    if ($ding == 3){playSoundDym('dingdong', 1, $level);}
		    if ($ding == 4){playSoundDym('dingdong', 1, $level);}
                  }
           }

           if ($google_file) {
                @touch($google_file);
                        playSoundDym($google_file, 1, $level);
           } else {
                safe_exec('cscript '.DOC_ROOT.'/rc/sapi.js '.$ph, 1, $level);
           }
        }

        global $noPatternMode;
        if (!$noPatternMode) {
                include_once(DIR_MODULES.'patterns/patterns.class.php');
                $pt=new patterns();
                $pt->checkAllPatterns();
        }

        if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY!='') {
         eval(SETTINGS_HOOK_AFTER_SAY);
        }


//        if (defined('SETTINGS_PUSHOVER_USER_KEY') && SETTINGS_PUSHOVER_USER_KEY) {
//                include_once(ROOT.'lib/pushover/pushover.inc.php');
//                if (defined('SETTINGS_PUSHOVER_LEVEL')){
//                        if($level>=SETTINGS_PUSHOVER_LEVEL) {
//                                postToPushover($ph);
//                        }
//                } elseif ($level>0) {
//                        postToPushover($ph);
//                }
//        }

//        if (defined('SETTINGS_GROWL_ENABLE') && SETTINGS_GROWL_ENABLE && $level>=SETTINGS_GROWL_LEVEL) {
//         include_once(ROOT.'lib/growl/growl.gntp.php');
//         $growl = new Growl(SETTINGS_GROWL_HOST, SETTINGS_GROWL_PASSWORD);
//         $growl->setApplication('MajorDoMo','Notifications');
         //$growl->registerApplication('http://localhost/img/logo.png');
//         $growl->notify($ph);
//        }

//        postToTwitter($ph);

 }
/**
* Title
*
* Description
*
* @access public
*/
 function playSoundDym($filename, $exclusive=0, $priority=0) {

  if (file_exists(ROOT.'sounds/'.$filename.'.mp3')) {
   $filename=ROOT.'sounds/'.$filename.'.mp3';
  } elseif (file_exists(ROOT.'sounds/'.$filename)) {
   $filename=ROOT.'sounds/'.$filename;
  }

  if (file_exists($filename)) {
     $zap = 'mplayer -af volume='.(-50 + gg('SpeechVolume')) . ' ' . $filename;
     DebMes($zap);
     safe_exec($zap, $exclusive, $priority);
  }
 }

?>
