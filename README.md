# Полезные ссылки

* [Работа с Bitrix API](https://github.com/sidigi/bitrix-info/wiki)

* [Работа с заказами D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/index.php)

* [Работа с корзиной D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/basket.php)

* [Приёмы работы с методами интернет-магазина D7](https://dev.1c-bitrix.ru/api_d7/bitrix/sale/technique/orders.php)

# Корзина

### Добавление товара в корзину D7
```
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
```
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
```
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
