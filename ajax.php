<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Fuser;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

$iblock_properties_to_return = ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL', 'PROPERTY_ARTNUMBER']; // Если необходимо получить все свойства: ['ID', 'IBLOCK_ID', '*']
$basket_properties_to_return = ['PRODUCT_ID', 'QUANTITY', 'PRICE', 'WEIGHT', 'CURRENCY']; // Если необходимо получить все поля: 'select' => ['*']

/**
 * Handler: add_product_to_basket
 */
if($_REQUEST['action'] == 'add_product_to_basket') {

    if (Loader::includeModule('sale')) {

        $product_id = intval($_REQUEST['product_id']);
        $quantity = intval($_REQUEST['quantity']);

        if($product_id && $quantity) {

            $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());

            if ($basketItem = $basket->getExistsItem('catalog', $product_id)) {

                // Обновление товара в корзине
                $basketItem->setField('QUANTITY', $basketItem->getQuantity() + $quantity);
                $basket->save();

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
            }

            echo count($basket->getListOfFormatText());
        }
    }
}

/**
 * Handler: change_product_quantity_in_basket
 */
if($_REQUEST['action'] == 'change_product_quantity_in_basket') {

    $msg['status'] = false;

    if (Loader::includeModule('sale')) {

        $product_id = intval($_REQUEST['product_id']);
        $quantity = intval($_REQUEST['quantity']);

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

    echo json_encode($msg);
}

/**
 * Handler: remove_product_from_basket
 */
if($_REQUEST['action'] == 'remove_product_from_basket') {

    $msg['status'] = false;

    if (Loader::includeModule('sale')) {

        $product_id = intval($_REQUEST['product_id']);

        if($product_id) {

            $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());

            if ($basketItem = $basket->getExistsItem('catalog', $product_id)) {

                $basketItem->delete();
                $basket->save();

                $msg['status'] = true;
            }
        }
    }

    echo json_encode($msg);
}

/**
 * Handler: get_items_from_basket
 */
if($_REQUEST['action'] == 'get_items_from_basket') {

    $items = [];

    if (Loader::includeModule('sale') && Loader::includeModule('iblock')) {

        $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());

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

            $db_basket_el['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($db_basket_el['PRICE'], $db_basket_el['CURRENCY']);
            $db_basket_el['SUM_FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($db_basket_el['PRICE'] * $db_basket_el['QUANTITY'], $db_basket_el['CURRENCY']);

            $items[] = $db_basket_el;
        }

        unset($db_basket_list);
    }

    echo json_encode($items);
}

/**
 * Handler: get_basket_info
 */
if($_REQUEST['action'] == 'get_basket_info') {

    $info = [];

    if (Loader::includeModule('sale') && Loader::includeModule('iblock')) {

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

    echo json_encode($info);
}

/**
 * Handler: get_currency_format
 */
if($_REQUEST['action'] == 'get_currency_format') {

    $msg = [];
    $msg['status'] = false;

    if (Loader::includeModule('sale')) {

        $price = floatval($_REQUEST['price']);
        $currency = CCurrency::GetBaseCurrency();

        if($_REQUEST['currency']) {
            $currency = htmlspecialchars($_REQUEST['currency']);
        }

        if ($price && $currency) {
            $msg['FORMATTED_PRICE'] = CCurrencyLang::CurrencyFormat($price, $currency);
            $msg['status'] = true;
        }
    }

    echo json_encode($msg);
}
