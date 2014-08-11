<?php

 
function saveBatchExec($batchFileName,$flagMail=true)
{
    $txt= ob_get_clean();
    echo $txt;

    if ($flagMail) {
    mail ( "laurent.mignot@gmail.com", 
    $_SERVER["SERVER_NAME"] .' : '.basename($batchFileName), 
    $txt, 
    'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n" );
    }
    

 return true;
}

function curl_exec_follow(/*resource*/ $ch, /*int*/ &$maxredirect = null) {
    $mr = $maxredirect === null ? 5 : intval($maxredirect);
    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
    } else {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        if ($mr > 0) {
            $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            $rch = curl_copy_handle($ch);
            curl_setopt($rch, CURLOPT_HEADER, true);
            curl_setopt($rch, CURLOPT_NOBODY, true);
            curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
            curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
            do {
                curl_setopt($rch, CURLOPT_URL, $newurl);
                $header = curl_exec($rch);
                if (curl_errno($rch)) {
                    $code = 0;
                } else {
                    $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                    if ($code == 301 || $code == 302) {

                        preg_match('/Location:(.*?)\n/i', $header, $matches);
                        $newurl = trim(array_pop($matches));

                    } else {
                        $code = 0;
                    }
                }
            } while ($code && --$mr);
            curl_close($rch);
            if (!$mr) {
                if ($maxredirect === null) {
                    trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
                } else {
                    $maxredirect = 0;
                }
                return false;
            }
            curl_setopt($ch, CURLOPT_URL, $newurl);
        }
    }
    return curl_exec($ch);
}