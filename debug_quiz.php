<?php
/**
 * Debug Quiz - Check what's actually in the database
 */

require_once 'config/database.php';

// Get quiz ID from URL
$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0