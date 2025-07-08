<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();
    
    include("../../classes/db.php");

    $db = new Database();

    $submission_id = (int) $_POST['submission_id'];
    $grade = trim($_POST['grade']);

    $query = "UPDATE work_submission SET Grade = :grade WHERE ID = :id";
    $params = [ ':grade' => $grade, ':id' => $submission_id ];
    
    $db->write($query, $params);

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
?>