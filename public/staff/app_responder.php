<?php

/**
 * Teacher's course application logic page
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 21229
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'JSON');
define('PHX_UX',        true);
define('PHX_MAILER',    true);
define('PHX_COURSES',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set default result array
$result = array();

// No information
if (!isset($_POST) || !array_key_exists('method', $_POST)) {
    $result['status'] = 'failed';
    $result['msg'] = 'No method specified';
} else {

    if ($_POST['method'] == 'save') {
        // Save application
        // Split data
        $data = json_decode($_POST['data'], true);
        if (!filter_var($data['form_data']['teacher_email'], FILTER_VALIDATE_EMAIL)) {
            $result['status'] = 'failed';
            $result['msg'] = 'Invalid email provided: '.$data['form_data']['teacher_email'];
        } else {
            // Email is OK, check App ID
            if ($data['app_id']) {
                $app_info = explode('|',base64_decode($data['app_id']));
                $stmt = Data::prepare('SELECT * FROM `applications` WHERE `AppID` = :id LIMIT 1');
                $stmt->bindParam('id', $app_info[0], PDO::PARAM_INT);
                $stmt->execute();
                $old_info = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $old_info = false;
            }

            if (!$old_info) {
                // Treat as new application
                $stmt = Data::prepare('INSERT INTO `applications` (`AppEmail`, `AppFormJSON`, `AppCourseJSON`, `AppCTS`, `AppLETS`, `AppSaveCount`, `AppStatus`) VALUES(:email, :formjson, :coursejson, NOW(), NOW(), 1, "saved")');
                $stmt->bindValue('email', $data['form_data']['teacher_email']);
                $stmt->bindValue('formjson', json_encode($data['form_data']));
                $stmt->bindValue('coursejson', json_encode($data['class_data']));
                $stmt->execute();

                $stmt = Data::prepare('SELECT `AppID`, `AppCTS` FROM `applications` WHERE `AppEmail` = :email AND `AppFormJSON` = :json ORDER BY `AppCTS` DESC LIMIT 0,1');
                $stmt->bindValue('email', $data['form_data']['teacher_email']);
                $stmt->bindValue('json', json_encode($data['form_data']));
                $stmt->execute();
                $new_info = $stmt->fetch(PDO::FETCH_ASSOC);

                $appid = base64_encode($new_info['AppID']."|".hash('crc32b', $new_info['AppCTS']));

                $body = UX::grabPage('text_snippets/email_application_save', array('name' => $data['form_data']['teacher_name'], 'course' => $data['form_data']['course_name'], 'appid' => $appid), true);
                Mailer::send(array('email' => $data['form_data']['teacher_email']), '[Summer] Course Application Information', $body);
                $result['status'] = 'success';
                $result['msg'] = 'Email sent.';

            } else {
                // Update saved information
                try {
                    $stmt = Data::prepare('UPDATE `applications` SET `AppEmail` = :email, `AppFormJSON` = :formjson, `AppCourseJSON` = :coursejson, `AppLETS` = NOW(), `AppSaveCount` = (`AppSaveCount`+1) WHERE `AppID` = :appid LIMIT 1');
                    $stmt->bindValue('appid', $old_info['AppID']);
                    $stmt->bindValue('email', $data['form_data']['teacher_email']);
                    $stmt->bindValue('formjson', json_encode($data['form_data']));
                    $stmt->bindValue('coursejson', json_encode($data['class_data']));
                    $stmt->execute();

                    $result['status'] = 'success';
                    $result['msg'] = 'Course updated.';

                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['msg'] = 'Error occured during update: '.$e->getMessage();
                }
            }
        }

    } elseif ($_POST['method'] == 'load') {
        // Return old savedata
        $reloadData = explode("|", base64_decode($_POST['data']));

        // Check CRC
        $stmt = Data::prepare('SELECT * FROM `applications` WHERE `AppID` = :appid AND `AppStatus` = "saved"');
        $stmt->bindParam('appid', $reloadData[0]);
        $stmt->execute();
        $realData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (hash('crc32b', $realData['AppCTS']) == $reloadData[1]) {
            $result['status'] = 'success';
            $result['last_save'] = array('form_data' => json_decode($realData['AppFormJSON']), 'times_data' => json_decode($realData['AppCourseJSON']));
        } else {
            $result['status'] = 'failed';
            $result['msg'] = 'CRC mismatch';
        }

    } elseif ($_POST['method'] == 'submit') {
        // Save application
        // Split data
        $data = json_decode($_POST['data'], true);
        if (!filter_var($data['form_data']['teacher_email'], FILTER_VALIDATE_EMAIL)) {
            $result['status'] = 'failed';
            $result['msg'] = 'Invalid email provided: '.$data['form_data']['teacher_email'];
        } else {
            // Email is OK, check App ID
            if ($data['app_id']) {
                $app_info = explode('|',base64_decode($data['app_id']));
                $stmt = Data::prepare('SELECT * FROM `applications` WHERE `AppID` = :id LIMIT 1');
                $stmt->bindParam('id', $app_info[0], PDO::PARAM_INT);
                $stmt->execute();
                $old_info = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $old_info = false;
            }

            if (!$old_info) {
                // Treat as new application
                $stmt = Data::prepare('INSERT INTO `applications` (`AppEmail`, `AppFormJSON`, `AppCourseJSON`, `AppCTS`, `AppLETS`, `AppSaveCount`, `AppStatus`) VALUES(:email, :formjson, :coursejson, NOW(), NOW(), 1, "submitted")');
                $stmt->bindValue('email', $data['form_data']['teacher_email']);
                $stmt->bindValue('formjson', json_encode($data['form_data']));
                $stmt->bindValue('coursejson', json_encode($data['class_data']));
                $stmt->execute();

                // Build submission email body
                $submitBody = array('name' => $data['form_data']['teacher_name'],
                                    'course_name' => $data['form_data']['course_name'],
                                    'email' => $data['form_data']['teacher_email'],
                                    'synopsis' => $data['form_data']['course_synop'],
                                    'offering_count' => sizeof($data['class_data']),
                                    'submission_timestamp' => date(DATETIME_FULL)
                                    );

                $body = UX::grabPage('text_snippets/email_application_submit', $submitBody, true);
                Mailer::send(array('email' => $data['form_data']['teacher_email']), '[Summer] Application Submission Confirmed!', $body);

                $result['status'] = 'success';
                $result['msg'] = 'Course submitted.';   

            } else {
                // Update saved information
                try {
                    $stmt = Data::prepare('UPDATE `applications` SET `AppEmail` = :email, `AppFormJSON` = :formjson, `AppCourseJSON` = :coursejson, `AppLETS` = NOW(), `AppSaveCount` = (`AppSaveCount`+1), `AppStatus` = "submitted" WHERE `AppID` = :appid LIMIT 1');
                    $stmt->bindValue('appid', $old_info['AppID']);
                    $stmt->bindValue('email', $data['form_data']['teacher_email']);
                    $stmt->bindValue('formjson', json_encode($data['form_data']));
                    $stmt->bindValue('coursejson', json_encode($data['class_data']));
                    $stmt->execute();

                    // Build submission email body
                    $submitBody = array('name' => $data['form_data']['teacher_name'],
                                        'course_name' => $data['form_data']['course_name'],
                                        'email' => $data['form_data']['teacher_email'],
                                        'synopsis' => $data['form_data']['course_synop'],
                                        'offering_count' => sizeof($data['class_data']),
                                        'submission_timestamp' => date(DATETIME_FULL)
                                        );

                    $body = UX::grabPage('text_snippets/email_application_submit', $submitBody, true);
                    Mailer::send(array('email' => $data['form_data']['teacher_email']), '[Summer] Application Submission Confirmed!', $body);

                    $result['status'] = 'success';
                    $result['msg'] = 'Course submitted.';

                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['msg'] = 'Error occured during update: '.$e->getMessage();
                }
            }
        }

    } else {
        // Wrong method
        $result['status'] = 'failed';
        $result['msg'] = 'Specified method not supported';
    }
}


// Send information and be on your way!
echo json_encode($result);

?>