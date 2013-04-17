<?php

//Modified from http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/
function rest_helper($url, $params = null, $verb = 'GET', $format = 'json')
{
  $cparams = array(
    'http' => array(
      'method' => $verb,
      'ignore_errors' => true
    )
  );
  if ($params['body'] !== null) {
    $data = $params['body'];
    if ($verb != 'GET') {
      $cparams['http']['content'] = $data;
    } else {
      $url .= '?' . $data;
    }
  }

  if ($params['headers'] !== null) {
    $cparams['http']['header'] = '';
    foreach($params['headers'] as $header => $value)
    {
      $cparams['http']['header'] .= $header . ': ' . $value . "\r\n";
    }
  }

  $context = stream_context_create($cparams);
  $fp = fopen($url, 'rb', false, $context);
  if (!$fp) {
    $res = false;
  } else {
    // If you're trying to troubleshoot problems, try uncommenting the
    // next two lines; it will show you the HTTP response headers across
    // all the redirects:
    //$meta = stream_get_meta_data($fp);
    //error_log(print_r($meta['wrapper_data'], true));
    $res = stream_get_contents($fp);
  }

  if ($res === false) {
    throw new Exception("$verb $url failed: $php_errormsg");
  }

  switch ($format) {
    case 'json':
      $r = json_decode($res);
      if ($r === null) {
        throw new Exception("failed to decode $res as json");
      }
      return $r;

    case 'xml':
      $r = simplexml_load_string($res);
      if ($r === null) {
        throw new Exception("failed to decode $res as xml");
      }
      return $r;
  }
  return $res;
}

?>
