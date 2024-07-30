<?php
global $wpdb;

$table = "{$wpdb->prefix}yt_feed";

function generateRandomString($length = 7) {
    // Generar bytes aleatorios
    $bytes = random_bytes($length);
    // Convertir los bytes a una cadena hexadecimal
    $hex = bin2hex($bytes);
    // Asegurarse de que la longitud deseada no exceda la longitud del string hexadecimal generado
    return substr($hex, 0, $length);
}

if(isset($_POST['save_feed'])){
    
    // $query = "SELECT feed_id FROM $table ORDER BY feed_id DESC LIMIT 1";
    // $resultado = $wpdb->get_results($query, ARRAY_A);

    $hex = generateRandomString(7);
    
    $name = $_POST['name'];
    $google_key = $_POST['google_key'];
    $channel_id = $_POST['channel_id'];
    $shortcode = '[FRM_YT_FEED id="'.$hex.'"]';

    $datos = [
        'name' => $name,
        'channel_id' => $channel_id,
        'google_key' => $google_key,
        'shortcode' => $shortcode,
        'hex' => $hex,
    ];

    $wpdb->insert($table, $datos);
}

$query = "SELECT * FROM $table";
$feeds = $wpdb->get_results($query, ARRAY_A);
if (empty($feeds)) {
    $feeds = array();
}
?>

<div class="wrap">
    <?php
    echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <a class="page-title-action" id="add_new_feed">Añadir nueva</a>
    <br><br><br>
    <table class="table wp-list-table widefat fixed striped pages">
        <thead>
            <th>Nombre</th>
            <th>Shortcode</th>
            <th>Acciones</th>
        </thead>
        <tbody>
            <?php if (!empty($feeds)) : ?>
                <?php foreach ($feeds as $key => $value) : ?>
                    <tr>
                        <td><p class="m-0"><?php echo $value['name']; ?></p></td>
                        <td><p class="m-0"><?php echo $value['shortcode']; ?></p></td>
                        <td>
                            <a class="m-0 page-title-action" data-id="<?php echo $value['feed_id']; ?>">Borrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3">
                        <p class="m-0 text-center">Crea un nuevo feed y lo podrás ver listado aqu&iacute;</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="adding_feed" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Nuevo YT Feed</h5>
            </div>
            <form action="" method="post" class="w-100">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nombre del feed">
                    </div>
                    <div class="form-group">
                        <label for="google_key">Google Key</label>
                        <input type="text" class="form-control" id="google_key" name="google_key" placeholder="Key">
                        <small class="form-text text-muted">Obtenlo desde el Google Cloud Console</small>
                    </div>
                    <div class="form-group">
                        <label for="channel_id">Canal YT ID</label>
                        <input type="text" class="form-control" id="channel_id" name="channel_id" placeholder="ID del canal">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" name="save_feed" id="save_feed" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>