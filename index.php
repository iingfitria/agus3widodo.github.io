<?php

    header('Access-Control-Allow-Origin: *');

    if ($_SERVER['REQUEST_METHOD'] != 'POST')
    {
        echo "POST request expected";
        return;
    }

    error_reporting(E_ALL && E_WARNING && E_NOTICE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    require_once 'includes/common.inc.php';

    $requestParameters = RequestParametersParser::getRequestParameters($_POST, !empty($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : null);
    _log($requestParameters);

//set Database access in you server (mine is localhost)
   $servername = "";
   $user = "";
   $password = "";
   $dbname = "";
   // Create connection
   $conn = new mysqli($servername, $user, $password, $dbname);
   // Check connection (this echos will not work since the flash quiz produced by iSpring does not display regular echo)
    if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
   }

//Get Results from Quiz POST to server   
   try
    {
        $quizResults = new QuizResults();
        $quizResults->InitFromRequest($requestParameters);
        $generator = QuizReportFactory::CreateGenerator($quizResults, $requestParameters);
        $report = $generator->createReport();
		$username = $_POST['USER_NAME'];
		$userid = $_POST['USER_ID'];
        $sp = $_POST['sp'];
		$userclass = $_POST['USER_CLASS'];
		$quiz_title = $_POST['qt'];
		$used_time = $_POST['ut'];
        $dateTime = date('Y-m-d_H-i-s');
        $resultFilename = dirname(__FILE__) . "/result/quiz_result_{$dateTime}.txt";
        @file_put_contents($resultFilename, $report);
 //write selected results to MySQL Database
      $sql = "INSERT INTO results (userid, username, USER_CLASS, sp, psp, tp, ut, qt, DateTime, USER_EMAIL, VARIABLE_4, Respostas)
      VALUES ('$userid','$username','$userclass','$sp','$psp','$tp','$used_time','$quiz_title', NOW(), '$useremail', '$variable4', '$detailed_results_xml')";
      if ($conn->query($sql) === TRUE) {
       echo "New record created successfully";
      } else {
       echo "Error: " . $sql . "<br>" . $conn->error;
      }
        echo "OK";
    }
    catch (Exception $e)
    {
        error_log($e);

        echo "Error: " . $e->getMessage();
    }

    function _log($requestParameters)
    {
        $logFilename = dirname(__FILE__) . '/log/quiz_results.log';
        $event       = array('ts' => date('Y-m-d H:i:s'), 'request_parameters' => $requestParameters, 'ts_' => time());
        $logMessage  = json_encode($event);
        $logMessage .= ',' . PHP_EOL;
        @file_put_contents($logFilename, $logMessage, FILE_APPEND);
    }