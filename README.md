# Файлы

_Файлы представлены только для теста, они могут быть незавершенными или частичными._

* [ajax_basket_handler.php - Пример handler-а для работы с корзиной при помощи ajax-запросов](https://github.com/amensum/bitrix-info/blob/master/ajax_basket_handler.php)

* [ajax_basket_template.php - Пример шаблона для работы с корзиной при помощи ajax-запросов](https://github.com/amensum/bitrix-info/blob/master/ajax_basket_template.php)

* [custom_smart_filter_template.php - Пример шаблона умного фильтра](https://github.com/amensum/bitrix-info/blob/master/custom_smart_filter_template.php), где в качестве component.js для работы с GET-параметрами должен быть подключен [init.js](https://github.com/amensum/js-helpers/blob/master/init.js)

# Полезные ссылки

* [Работа с Bitrix API](https://github.com/sidigi/bitrix-info/wiki)

* [Создание многоуровневого меню](https://abraxabra.ru/blog/bitrix-zametki/multilevel-menu-bitrix/)

* [Работа с заказами D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/index.php)

* [Работа с корзиной D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/basket.php)

* [Приёмы работы с методами интернет-магазина D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/orders.php)

### Модули

* [Структура полной сборки модуля](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=101&LESSON_ID=3216&LESSON_PATH=8781.4793.3216)

* [Пример создания модуля](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=101&LESSON_ID=2902&LESSON_PATH=8781.4793.2902)


* [Взаимодействие модулей](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=2825&LESSON_PATH=3913.4609.2825)

### События

* [Список событий](https://dev.1c-bitrix.ru/api_help/main/events/index.php)

* [Регистрация и обработка событий D7](https://dev.1c-bitrix.ru/api_d7/bitrix/main/EventManager/index.php)

* [Регистрация событий](https://dev.1c-bitrix.ru/api_help/main/functions/module/registermoduledependences.php)

* [Страница и порядок её выполнения](https://dev.1c-bitrix.ru/api_help/main/general/pageplan.php)

# Платежные системы

### Получение списка платежных систем D7

```php
$paysystem = [];
$db_list = \Bitrix\Sale\PaySystem\Manager::getList();
while ($db_el = $db_list->fetch()) {
  $paysystem[] = $db_el;
}
print_r($paysystem);
```

# Службы доставки

### Получение списка служб доставок D7

```php
$delivery = [];
$list = Delivery\Services\Manager::getActiveList();
foreach ($list as $service) {
  if ($service['CLASS_NAME'] == '\Bitrix\Sale\Delivery\Services\EmptyDeliveryService') {
    continue;
  }
  $service['PROPS_GROUP_ID'] = 'DELIVERY';
  $service['PRICE'] = $service['CONFIG']['MAIN']['PRICE'];
  $service['LOGOTIP'] = CFile::ResizeImageGet($service['LOGOTIP'], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
  $delivery[] = $service;
}
print_r($delivery);
```

# Корзина

### Добавление товара в корзину D7
```php
if (Loader::includeModule('sale')) {
  $product_id = 1;
  $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
  $basketItem = $basket->createItem('catalog', $product_id);
  $basketItem->setFields(
      [
          'QUANTITY' => 4,
          'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
          'LID' => Bitrix\Main\Context::getCurrent()->getSite(),
          'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider'
      ]
  );
  $basket->save();
  print_r($basket->getListOfFormatText());
}
```

### Обновление товара в корзине D7
```php
if (Loader::includeModule('sale')) {
  $product_id = 1;
  $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
  $basketItem = $basket->getExistsItem('catalog', $product_id);
  $basketItem->setField('QUANTITY', $basketItem->getQuantity() + $quantity);
  $basket->save();
  print_r($basket->getListOfFormatText());
}
```

### Получение списка товаров корзины текущего пользователя D7
```php
if (Loader::includeModule('sale')) {
    $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
    $db_list = Basket::getList([
        'select' => ['*'],
        'filter' => [
            '=FUSER_ID' => Fuser::getId(),
            '=ORDER_ID' => null,
            '=LID' => Context::getCurrent()->getSite(),
            '=CAN_BUY' => 'Y',
        ]
    ]);
    while ($db_el = $db_list->fetch())
    {
        print_r($db_el);
    }
}
```

### Получение списка товаров корзины текущего пользователя и связанных с ними элементов инфоблоков
```php
$iblock_properties_to_return = ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL']; // Если необходимо получить все свойства: ['ID', 'IBLOCK_ID', '*']
$basket_properties_to_return = ['PRODUCT_ID', 'QUANTITY', 'PRICE', 'WEIGHT']; // Если необходимо получить все поля: 'select' => ['*']

$items = [];

if (Loader::includeModule('sale') && Loader::includeModule('iblock')) {

    $db_basket_list = Basket::getList([
        'select' => $basket_properties_to_return,
        'filter' => [
            '=FUSER_ID' => Fuser::getId(),
            '=ORDER_ID' => null,
            '=LID' => Context::getCurrent()->getSite(),
            '=CAN_BUY' => 'Y',
        ]
    ]);

    while ($db_basket_el = $db_basket_list->fetch())
    {

        // Получение IBLOCK_ID элемента с которым связан продукт
        $db_iblock_list = CIBlockElement::GetById($db_basket_el['PRODUCT_ID']);
        if ($db_iblock_el = $db_iblock_list->GetNext()) {
            $db_basket_el['PRODUCT_IBLOCK_ID'] = $db_iblock_el['IBLOCK_ID'];
        }
        unset($db_iblock_list);

        // Получение всех полей элемента с которым связан продукт
        $db_iblock_list = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $db_basket_el['PRODUCT_IBLOCK_ID'], 'ID' => $db_basket_el['PRODUCT_ID']],
            false,
            false,
            $iblock_properties_to_return
        );
        if ($db_iblock_el = $db_iblock_list->GetNext()) {
            // Получение картинки и изменение ее размеров
            $db_iblock_el['PREVIEW_PICTURE'] = CFile::ResizeImageGet($db_iblock_el["PREVIEW_PICTURE"], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
            $db_basket_el['PRODUCT'] = $db_iblock_el;
        }
        unset($db_iblock_list);
        
        $db_basket_el['FORMATTED_PRICE'] = CurrencyFormat($db_basket_el['PRICE'], $db_basket_el['CURRENCY']);

        $items[] = $db_basket_el;
    }
    
    unset($db_basket_list);
}

print_r($items);
```

### Получение информации о корзине текущего пользователя D7
```php
$info = [];

if (Loader::includeModule('sale')) {

    $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());

    $info['PRICE'] = $basket->getPrice();
    $info['PRICE_WITHOUT_DISCOUNTS'] = $basket->getBasePrice();
    $info['WEIGHT'] = $basket->getWeight();
    $info['VAT_RATE'] = $basket->getVatRate();
    $info['VAT_SUM'] = $basket->getVatSum();
    $info['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($info['PRICE'], CCurrency::GetBaseCurrency());
    $info['FORMATTED_PRICE_WITHOUT_DISCOUNTS'] = CCurrencyLang::CurrencyFormat($info['PRICE_WITHOUT_DISCOUNTS'], CCurrency::GetBaseCurrency());
    $info['ITEMS_QUANTITY'] = $basket->getQuantityList();
    $info['QUANTITY'] = count($info['ITEMS_QUANTITY']);
}

print_r($info);
```

### Удаление товара из корзины текущего пользователя D7
```php
$msg['status'] = false;

if (Loader::includeModule('sale')) {

    $product_id = 1;

    if($product_id) {

        $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());

        if ($basketItem = $basket->getExistsItem('catalog', $product_id)) {

            $basketItem->delete();
            $basket->save();

            $msg['status'] = true;
        }
    }
}

print_r($msg);
```

### Изменение количества товара в корзине текущего пользователя D7
```php
$msg['status'] = false;

if (Loader::includeModule('sale')) {

    $product_id = 1;
    $quantity = 1;

    if($product_id && $quantity) {

        $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());

        if ($basketItem = $basket->getExistsItem('catalog', $product_id)) {

            // Обновление товара в корзине
            $basketItem->setField('QUANTITY', $quantity);
            $basket->save();

            $msg['status'] = true;
            $msg['action'] = 'update';

        } else {

            // Добавление товара в корзину
            $basketItem = $basket->createItem('catalog', $product_id);
            $basketItem->setFields(
                [
                    'QUANTITY' => $quantity,
                    'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                    'LID' => Bitrix\Main\Context::getCurrent()->getSite(),
                    'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider'
                ]
            );
            $basket->save();

            $msg['status'] = true;
            $msg['action'] = 'add';
        }
    }
}

print_r($msg);
```

# Заказ

### Получить доступные методы доставки

```php
$delivery = [];
$db_list = CSaleDelivery::GetList(["SORT" => "ASC"], ["ACTIVE" => "Y"]);
while ($db_el = $db_list->GetNext()) {
  $delivery[] = $db_el;
}
unset($db_list);
print_r($delivery);
```
