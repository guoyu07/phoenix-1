<?php

/*
* The user experience class contains features and methods
* to drive page HTML and other source code...
*
* @author      Yectep Studios <info@yectep.hk>
* @version     20930
* @package     Phoenix
*
*/
class Courses {

    /**
     * Returns an array of rooms available for use based on the rooms table.
     * @return mixed
     */
    public function getRoomList() {

        $stmt = Data::query('SELECT * FROM `rooms` ORDER BY `RoomID` ASC, `RoomName` ASC');
        $intData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $retData = array();

        foreach($intData as $room) {
            $retData[$room["RoomID"]] = array(  "name"      => $room["RoomName"],
                                                "block"   => $room["RoomComment"]);
        }

        return $retData;

    }

    /**
     * Gets all applications based on a certain status
     */
    static public function getApps($status = 'submitted') {
        try {
            $stmt = Data::prepare('SELECT * FROM `applications` WHERE `AppStatus` = :status ORDER BY `AppLETS` DESC');
            $stmt->bindParam('status', $status);
            $stmt->execute();
            $courseRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }

        $courses = array();

        foreach($courseRaw as $app) {
            $infoJson = json_decode($app['AppFormJSON'], true);
            $classJson = json_decode($app['AppCourseJSON'], true);

            $student_count = 0;

            foreach($classJson as $class) {
                $student_count += $class['max_students'];
            }

            array_push($courses, array('app_id' => $app['AppID'],
                'program' => $infoJson['program'],
                'subject' => (($infoJson['program'] == 'SP') ? $infoJson['sp_subject'] : $infoJson['ap_subject']),
                'teacher_name' => $infoJson['teacher_name'],
                'teacher_email' => $infoJson['teacher_email'],
                'course_name' => $infoJson['course_name'],
                'count' => $student_count,
                'submitted' => date(DATETIME_SHORT, strtotime($app['AppLETS']))
            ));
        }

        return $courses;
    }
}

?>