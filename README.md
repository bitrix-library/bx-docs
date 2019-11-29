# Файлы

_Файлы представлены только для теста, они могут быть незавершенными или частичными._

* [ajax_basket_handler.php - Пример handler-а для работы с корзиной при помощи ajax-запросов](https://github.com/amensum/bitrix-info/blob/master/ajax_basket_handler.php)

* [ajax_basket_template.php - Пример шаблона для работы с корзиной при помощи ajax-запросов](https://github.com/amensum/bitrix-info/blob/master/ajax_basket_template.php)

* [custom_smart_filter_template.php - Пример шаблона умного фильтра](https://github.com/amensum/bitrix-info/blob/master/custom_smart_filter_template.php), где в качестве component.js для работы с GET-параметрами должен быть подключен [init.js](https://github.com/amensum/js-helpers/blob/master/init.js)

# Полезные ссылки

* [Работа с Bitrix API](https://github.com/sidigi/bitrix-info/wiki)

* [Создание многоуровневого меню](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=3498#menu_from_block)

* [Работа с заказами D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/index.php)

* [Работа с корзиной D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/basket.php)

* [Приёмы работы с методами интернет-магазина D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/orders.php)

* [Оформление заказа в компоненте sale.order.ajax без регистрации](http://support.altop.ru/q/nastroika-saita/otklyuchenie-shaga-registratsii-na-sayte-pri-oformlenii-zakaza/)

* [Оформление заказа по номеру телефона](https://marketplace.1c-bitrix.ru/solutions/sotbit.orderphone/#tab-install-link)

* [D7-аналоги любимых функций в 1С-Битрикс](https://www.intervolga.ru/blog/projects/d7-analogi-lyubimykh-funktsiy-v-1s-bitriks/)

* [Работа с HighLoad (документация)](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=5746&LESSON_PATH=3913.5062.5745.5746)

* [Работа с HighLoad (habr)](https://habr.com/ru/post/207700/)

* [Работа датой и временем D7](https://mrcappuccino.ru/blog/post/d7-work-with-datetime)

### Модули

* [Структура полной сборки модуля](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=101&LESSON_ID=3216&LESSON_PATH=8781.4793.3216)

* [Пример создания модуля](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=101&LESSON_ID=2902&LESSON_PATH=8781.4793.2902)


* [Взаимодействие модулей](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=2825&LESSON_PATH=3913.4609.2825)

### События

* [Список событий](https://dev.1c-bitrix.ru/api_help/main/events/index.php)

* [Регистрация и обработка событий D7](https://dev.1c-bitrix.ru/api_d7/bitrix/main/EventManager/index.php)

* [Регистрация событий](https://dev.1c-bitrix.ru/api_help/main/functions/module/registermoduledependences.php)

* [Страница и порядок её выполнения](https://dev.1c-bitrix.ru/api_help/main/general/pageplan.php)

* [События модуля "Интернет-магазин"](https://dev.1c-bitrix.ru/api_help/sale/events/index.php)

# Платежные системы

### Получение списка платежных систем D7

```php
$paysystem = [];
$db_list = \Bitrix\Sale\PaySystem\Manager::getList(
    [
        'select' => ['*'],
        'filter' => [
          '=ACTIVE' => 'Y'
        ]
    ]
);
while ($db_el = $db_list->fetch()) {
    $db_el['LOGOTIP'] = CFile::ResizeImageGet($db_el['LOGOTIP'], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
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

# JS и CSS

## Подключить JS или CSS D7

### В шаблоне компонента
```php
$this->addExternalCss("/local/styles.css");
$this->addExternalJS("/local/liba.js");
```

### В любом месте
```php
\Bitrix\Main\Page\Asset::getInstance()->addCss("/local/styles.css");
\Bitrix\Main\Page\Asset::getInstance()->addJs("/local/liba.js");
```

# Модуль

### Получить путь к корневой директории модуля
_Например в скрипте в директории, которая находится в корневой директории модуля:_
```php
$module_absolute_path = str_replace("\\", "/", dirname(__DIR__ . '\\..\\'));
$module_relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $module_absolute_path);
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
$items = [];
        
$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Application::getInstance()->getContext()->getSite());

// <DISCOUNTS> : apply
$discounts_context = new \Bitrix\Sale\Discount\Context\Fuser(\Bitrix\Sale\Fuser::getId());
$discounts = Discount::buildFromBasket($basket, $discounts_context);
$result = $discounts->calculate()->getData();
$basket->applyDiscount($result['BASKET_ITEMS']);
// </DISCOUNTS>

$basket_items = $basket->getBasketItems();

foreach ($basket_items as $obj) {
    $item = [];
    $item['PRODUCT_ID'] = $obj->getProductId();
    $item['PRICE'] = $obj->getPrice();
    $item['SUM_PRICE'] = $obj->getFinalPrice();
    $item['CURRENCY'] = $obj->getCurrency();
    $item['QUANTITY'] = $obj->getQuantity();
    $item['WEIGHT'] = $obj->getWeight();
    $item['FORMATTED_PRICE'] = \CCurrencyLang::CurrencyFormat($item['PRICE'], $item['CURRENCY']);
    $item['SUM_FORMATTED_PRICE'] = \CCurrencyLang::CurrencyFormat($item['SUM_PRICE'], $item['CURRENCY']);
    
    // Получение IBLOCK_ID элемента с которым связан продукт
    $db_iblock_list = \CIBlockElement::GetById($item['PRODUCT_ID']);
    if ($db_iblock_el = $db_iblock_list->GetNext()) {
        $item['PRODUCT_IBLOCK_ID'] = $db_iblock_el['IBLOCK_ID'];
    }
    unset($db_iblock_list);
    
    $allowed_fields_iblock = [
        'ID',
        'IBLOCK_ID',
        'NAME',
        'PREVIEW_PICTURE',
        'DETAIL_PAGE_URL',
    ]; // Если необходимо получить все свойства: ['ID', 'IBLOCK_ID', '*']
    
    // Получение всех полей элемента с которым связан продукт
    $db_iblock_list = \CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $item['PRODUCT_IBLOCK_ID'], 'ID' => $item['PRODUCT_ID']],
        false,
        false,
        $allowed_fields_iblock
    );
    if ($db_iblock_el = $db_iblock_list->GetNext()) {
        // Получение картинки и изменение ее размеров
        $db_iblock_el['PREVIEW_PICTURE'] = \CFile::ResizeImageGet($db_iblock_el["PREVIEW_PICTURE"], ['width' => 500, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, true);
        $item['PRODUCT'] = $db_iblock_el;
    }
    unset($db_iblock_list);
    
    $items[] = $item;
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

### Применение скидок к корзине
```php
$basket = Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Application::getInstance()->getContext()->getSite());
$discounts_context = new \Bitrix\Sale\Discount\Context\Fuser(\Bitrix\Sale\Fuser::getId());
$discounts = Discount::buildFromBasket($basket, $discounts_context);
$result = $discounts->calculate()->getData();
$basket->applyDiscount($result['BASKET_ITEMS']);
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

# Обработчики событий

### Пример создания и удаления долгосрочного обработчика события
```php
$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->registerEventHandler("main", "OnAfterEpilog", "mymodule", "\MyClass\MyClassBase", "myFunction");
$eventManager->unRegisterEventHandler("main", "OnAfterEpilog", "mymodule", "\MyClass\MyClassBase", "myFunction");
```

### Пример создания и удаления краткосрочного обработчика события
```php
$eventManager = \Bitrix\Main\EventManager::getInstance();
$handler = $eventManager->addEventHandler("main", "OnAfterEpilog", array("\MyClass\MyClassBase", "myFunction"));
$eventManager->removeEventHandler("main", "OnAfterEpilog", $handler);
$handlers = $eventManager->findEventHandlers("main", "OnAfterEpilog");
```

### Добавление полей в почтовые шаблоны события SALE_NEW_ORDER
```php
AddEventHandler("sale", "OnOrderNewSendEmail", "handlerOnOrderNewSendEmail");

function handlerOnOrderNewSendEmail($orderID, &$eventName, &$arFields) {

    $order = CSaleOrder::GetByID($orderID);
    $order_props = CSaleOrderPropsValue::GetOrderProps($orderID);

    $name = "";
    $last_name = "";
    $phone = "";
    $country_name = "";
    $city_name = "";
    $street_name = "";
    $house_name = "";
    $flat_name = "";
    $delivery_name = "";
    $pay_system_name = "";

    while ($arProps = $order_props->Fetch()) {
        if ($arProps["CODE"] == "NAME") {
            $name = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "LASTNAME") {
            $last_name = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "PHONE") {
            $phone = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "COUNTRY") {
            $country_name = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "CITY") {
            $city_name = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "STREET") {
            $street_name = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "HOUSE") {
            $house_name = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "FLAT") {
            $flat_name = htmlspecialchars($arProps["VALUE"]);
        }
    }

    $arDeliv = CSaleDelivery::GetByID($order["DELIVERY_ID"]);
    if ($arDeliv) {
        $delivery_name = $arDeliv["NAME"];
    }

    $arPaySystem = CSalePaySystem::GetByID($order["PAY_SYSTEM_ID"]);
    if ($arPaySystem) {
        $pay_system_name = $arPaySystem["NAME"];
    }

    $arFields["FULL_NAME"] = "$name $last_name";
    $arFields["PHONE"] = $phone;
    $arFields["DELIVERY_NAME"] = $delivery_name;
    $arFields["PAY_SYSTEM_NAME"] = $pay_system_name;
    $arFields["FULL_ADDRESS"] = "$country_name $city_name $street_name $house_name $flat_name";
    $arFields["USER_DESCRIPTION"] = $order["USER_DESCRIPTION"];
}
```
