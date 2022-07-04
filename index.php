<?
require('phpQuery/phpQuery.php');

//Домент откуда будем получать товары
const DOMEN = "https://tachka.ru";
const DOMEN_CAT = "https://tachka.ru/farkop?";

//бренды которые будем парсить, были указанны в задачи
$parsLink = array("auto-hak", "aragon","avtos","bizon-dzk","brink","westfalia","baltex", "treyler");

//При первом срабатывание скрипта проверяем, указан ли бренд, если нет, то берем первый из массива, $parsLink а если указан, то берем его
if(empty($_GET['brand'])){
    $brand = current($parsLink);
} else {
    $brand = $_GET['brand'];
}

$fileName = $_SERVER['DOCUMENT_ROOT'] . '/' . $brand . '.json';//Получаем файл
//Если не номера страницы, то думаем что это первая страница и не передаем в CURL запрос номер пагинации, т.к срабывает массовый редирект
//Если мы впервый раз на страницы, то создаем файл в ктором будем хранить ссылки на товары
//Если мы не первый раз на страницы, то открываем для чтения уже существующий файл
if(empty($_GET['pagen'])){
    $NavPageNomer = 1;
    $get = array(
        'brand'  => $brand,
    );
} else {
    $NavPageNomer = $_GET['pagen'];
    $get = array(
        'brand'  => $brand,
        'page'  => $NavPageNomer,
    );

    $json = file_get_contents($fileName); // читаем данные с файла
    $data = json_decode($json,true); // дикодируем JSON в массив
}

//Отправляем CURL запрос и начинаем парсить все страницы.
// ШАГ 1. Получаем все ссылки детальных страниц.

$ch = curl_init(DOMEN_CAT . http_build_query($get));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$response = curl_exec($ch);
curl_close($ch);

$doc = phpQuery::newDocument($response);
//Выбираем только товары
$products = $doc->find('.catalog-list .catalog-item');
//Перебераем товары
foreach ($products as $product){
    $pq = pq($product);

    $data[$pq->attr('id')]['URL'] = $pq->find('.catalog-item__head a')->attr('href');
    $data[$pq->attr('id')]['ARTICUL'] = $pq->find('.catalog-item__attributes.attributes .attributes__row:eq(0) .attributes__data')->text();
}


file_put_contents($fileName, json_encode($data));

$toPage = $doc->find('.paging .paging__page.paging__page_active')->text();
$countPage = explode(" ", $doc->find('.top_sort__pages')->text())[2];


echo $toPage;
echo ' / ';
echo $countPage;
if($toPage + 1 <= $countPage){
    echo '<meta http-equiv="refresh" content="1;URL=http://parser/?pagen='.($toPage + 1).'&brand='.$brand.'" />';
}

?>

