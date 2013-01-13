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
}

?>