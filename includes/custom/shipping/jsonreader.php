<?php
/**
 * Función que lee un archivo JSON y retorna una lista.
 *
 * @param string $path La ruta al archivo JSON (requerido).
 * @return array La lista obtenida del archivo JSON.
 * @throws Exception Si no se proporciona un valor para $path.
 */
function json_reader($path) {
    if (empty($path)) {
        throw new Exception('El parámetro $path es requerido.');
    }
    
    // Ruta al archivo JSON
    $hoodsjsonFile = $path;
    // Leer el contenido del archivo JSON
    $jsonString = file_get_contents($hoodsjsonFile);
    // Decodificar el JSON en una estructura de datos de PHP
    $data = json_decode($jsonString, true);
    $region_list = array();
    foreach ($data as $regionName => $region) {
        $region_list[$regionName] = $region['label'];
    }
    // Retorna la lista obtenida del archivo JSON
    return $region_list;
};

/**
 * Función que lee un archivo JSON y retorna una lista.
 *
 * @param string $path La ruta al archivo JSON (requerido).
 * @return array La lista obtenida del archivo JSON.
 * @throws Exception Si no se proporciona un valor para $path.
 */
function json_reader_hoods($path, $region, $type) {
    if (empty($path)) {
        throw new Exception('El parámetro $path es requerido.');
    }
    
    // Ruta al archivo JSON
    $hoodsjsonFile = $path;
    // Leer el contenido del archivo JSON
    $jsonString = file_get_contents($hoodsjsonFile);
    // Decodificar el JSON en una estructura de datos de PHP
    $data = json_decode($jsonString, true);
    $region_list = array();
    if (isset($data[$region][$type])) {
        foreach ($data[$region][$type] as $country) {
            $region_list[] = array(
                'name' => $country['name'],
                'slug' => $country['slug']
            );
        }
    }
 
    return $region_list;
};

function json_reader_shipping_cost($path, $region, $type, $generalZone) {
    if (empty($path)) {
        throw new Exception('El parámetro $path es requerido.');
    }
    
    // Ruta al archivo JSON
    $hoodsjsonFile = $path;
    // Leer el contenido del archivo JSON
    $jsonString = file_get_contents($hoodsjsonFile);
    // Decodificar el JSON en una estructura de datos de PHP
    $data_json = json_decode($jsonString, true);
    $data = $data_json[$generalZone];
    $region_list = array();

    if (isset($data[$region][$type])) {

        foreach ($data[$region][$type] as $country) {
            $slug = $country['slug'];
            $shippingCost = $country['shippingCost'];
            $freeShippingThreshold = $country['freeShippingThreshold'];

            $region_list[$slug] = [
                'shippingCost' => $shippingCost,
                'freeShippingThreshold' => $freeShippingThreshold
            ];
        }
    }

    return $region_list;
}
