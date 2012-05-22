<?php
 
if (extension_loaded('xhprof')) {
  $profiler_namespace = '09ua';  // namespace for your application
  $xhprof_data = xhprof_disable();
  $xhprof_runs = new XHProfRuns_Default();
  $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
 
  // url to the XHProf UI libraries (change the host name and path)
  $profiler_url = sprintf('http://profile.bazalt.org.ua/index.php?run=%s&source=%s', $run_id, $profiler_namespace);
 
  // Можем сделать защиту по айпи, или добавить $_GET параметр
  //if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')))
  //{
    // На нашей странице появится ссылка "Profiler output", которая ведет на GUI XHprof с уникальными идентификатором отчета
    echo '<br/><br/><a href="'. $profiler_url .'" target="_blank">Profiler output</a>';
  //}
}