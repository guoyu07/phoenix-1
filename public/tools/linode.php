<?php
header('Content-Type: application/json');

require('Services/Linode.php');

try {
    $linode = new Services_Linode('jndeKvoKxoDZTulDarVgk3YmumyowzXjY8oRByeNdCEHqQVQIS1e4YxkDXbRnnXt');
    $a = $linode->linode_config_list(159893);

    echo json_encode($a);

} catch (Services_Linode_Exception $e) {
    echo $e->getMessage();
}
?>