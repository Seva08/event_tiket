<?php
if (isset($_GET['id'])) {
    $id_venue = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM venue WHERE id_venue = $id_venue");
}
header("Location: ?p=admin_venue");
exit;
