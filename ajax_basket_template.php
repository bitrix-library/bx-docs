<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="cart" id="cart-wrapper">
    <div class="row">
        <!-- cart content-->
        <div class="col-12 col-lg-7 col-xl-8 mb-4 mb-lg-0">
            <div class="cart-list products-container">
                <!-- PRODUCTS -->
            </div>
        </div>
        <!-- cart content-->
        <!-- cart side-->
        <div class="col-12 col-lg-5 col-xl-4">
            <div class="info-container">
                <!-- INFO -->
            </div>
        </div>
        <!-- /cart side-->
    </div>
</div>

<template id="fragment-product">
    <div class="cart-list-el product-element" style="display: none">
        <div class="row align-items-center">
            <div class="col-12 col-md col-lg-12 col-xl">
                <div class="cart-list-good">
                    <!--<a class="catalog-sl-heart active" href="#" title="Удалить товар из избранного"></a>-->
                    <a class="cart-list-good-img" href="#">
                        <img src="" alt=""></a>
                    <div class="cart-list-good-content">
                        <div class="cart-list-good-article"></div>
                        <a class="cart-list-good-name" href="#"></a>
                        <div class="cart-list-good-mass"></div>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="cart-list-input">
                    <input class="number-styled product-quantity-element" type="number" min="1" value="1">
                </div>
            </div>
            <div class="col-auto">
                <div class="cart-list-price-wrapper">
                    <!--<div class="cart-list-price-old">100 979 руб.</div>-->
                    <div class="cart-list-price product-price-element"></div>
                </div>
            </div>
            <div class="col-auto cart-list-delete-wrapper">
                <a class="cart-list-delete product-delete-element" href="#" title="Удалить товар из корзины"></a>
            </div>
        </div>
    </div>
</template>

<template id="fragment-info">
    <div class="cart-side info-element" style="display: none">
        <form action="#">
            <?
            /*
            <div class="cart-side-item bg-grey">
                <div class="cart-side-title">Промокод</div>
                <div class="d-flex flex-wrap">
                    <div class="formsubs-input">
                        <input class="control" type="text" placeholder="Промокод">
                    </div>
                    <button class="formsubs-btn">Применить</button>
                </div>
            </div>
            */
            ?>
            <div class="cart-side-item">
                <ul class="cart-side-list">
                    <li>
                        <div class="cart-side-list-label">Общий вес</div>
                        <div class="cart-side-list-text info-weight-element"></div>
                    </li>
                    <li>
                        <div class="cart-side-list-label">Количество</div>
                        <div class="cart-side-list-text info-quantity-element"></div>
                    </li>
                    <li>
                        <div class="cart-side-list-label">Итого<sup>*</sup>:</div>
                        <div class="cart-side-list-text">
                            <div class="cart-side-list-price-old info-old-price-element"></div>
                            <div class="cart-side-list-price info-price-element"></div>
                        </div>
                    </li>
                </ul><a class="mbtn mbtn-primary d-block" href="<?=$arParams['PATH_TO_ORDER']?>">Оформить заказ</a>
                <div class="cart-side-info"><sup>*</sup> - Общая стоимость без учета доставки</div>
            </div>
        </form>
    </div>
</template>

<script>
    const fade_time = 500;
    const fade_interval = 250;
    let timers_ids = [];

    refreshEventsHandlers = () => {
        $('.product-delete-element').click((e) => {
            e.preventDefault();
            let product_element = $(e.target).closest(`.product-element`);
            let product_id = product_element.attr(`data-product-id`);
            $.ajax({
                url: '<?=SITE_TEMPLATE_PATH?>/ajax.php',
                data: {
                    action: 'remove_product_from_basket',
                    product_id: product_id
                },
                success: (data) => {
                    data = JSON.parse(data);
                    console.log(`remove_product_from_basket:`, data);
                    product_element.fadeOut(fade_time, () => {
                        product_element.remove()
                    });
                    refreshBasketInfo();
                }
            });
        });

        $('.product-quantity-element').change((e) => {
            e.preventDefault();
            let quantity = $(e.target).val();
            let product_element = $(e.target).closest(`.product-element`);
            let product_id      = product_element.attr(`data-product-id`);
            let price           = product_element.attr(`data-product-price`);
            let price_element   = product_element.find(`.product-price-element`);
            timers_ids[product_id] && clearTimeout(timers_ids[product_id]);
            timers_ids[product_id] = setTimeout(() => {
                refreshProductPrice(price * quantity, null, price_element);
            }, 1000);
            timers_ids['info'] && clearTimeout(timers_ids['info']);
            timers_ids['info'] = setTimeout(() => {
                changeProductQuantityInBasket(product_id, quantity);
            }, 1000);
        });
    };

    changeProductQuantityInBasket = (product_id, quantity) => {
        $.ajax({
            url: '<?=SITE_TEMPLATE_PATH?>/ajax.php',
            data: {
                action: 'change_product_quantity_in_basket',
                product_id: product_id,
                quantity: quantity
            },
            success: (data) => {
                data = JSON.parse(data);
                console.log(`change_product_quantity_in_basket:`, data);
                refreshBasketInfo();
            }
        });
    };

    refreshProductPrice = (price, currency, element) => {
        $.ajax({
            url: '<?=SITE_TEMPLATE_PATH?>/ajax.php',
            data: {
                action: 'get_currency_format',
                price: price,
                currency: currency
            },
            success: (data) => {
                data = JSON.parse(data);
                console.log(`get_currency_format:`, data);
                element.fadeOut(fade_time, () => {
                    element.text(data.FORMATTED_PRICE);
                    element.fadeIn(fade_time);
                });
            }
        });
    };

    $('.add-to-basket-button').click((e) => {
        e.preventDefault();
        $.ajax({
            url: '<?=SITE_TEMPLATE_PATH?>/ajax.php',
            data: {
                action: 'add_product_to_basket',
                product_id: $(e.target).data('product-id'),
                quantity: $(e.target).data('quantity')
            },
            success: (data) => {
                let header_info_cart_info = $('.header-info-cart-info')[0];
                let cart_count = header_info_cart_info.getElementsByTagName('span')[0];
                cart_count.innerText = data;
            }
        });
    });

    refreshBasketInfo = () => {
        $.ajax({
            url: '<?=SITE_TEMPLATE_PATH?>/ajax.php',
            data: {
                action: 'get_basket_info'
            },
            success: (data) => {
                data = JSON.parse(data);
                console.log(`get_basket_info:`, data);
                let template    = $(`#fragment-info`)[0];
                let clone       = document.importNode(template.content.childNodes[1], true);
                let element     = $(clone);
                element.find(`.info-weight-element`)        .text(data.WEIGHT / 1000 + ` кг`);
                element.find(`.info-quantity-element`)      .text(data.QUANTITY + ` шт`);
                element.find(`.info-price-element`)         .text(data.FORMATTED_PRICE);
                if(data.PRICE !== data.PRICE_WITHOUT_DISCOUNTS) {
                    element.find(`.info-old-price-element`) .text(data.FORMATTED_PRICE_WITHOUT_DISCOUNTS);
                }

                if($(`.info-container`).children().length) {
                    $(`.info-container`).children().fadeOut(fade_time, () => {
                        $(`.info-container`).children().remove();
                        $(`.info-container`).append(element);
                        element.fadeIn(fade_time);
                    });
                } else {
                    $(`.info-container`).append(element);
                    element.fadeIn(fade_time);
                }
            }
        });
    };

    refreshProductsList = () => {
        $.ajax({
            url: '<?=SITE_TEMPLATE_PATH?>/ajax.php',
            data: {
                action: 'get_items_from_basket'
            },
            success: (data) => {
                data = JSON.parse(data);
                console.log(`get_items_from_basket:`, data);
                let count = 0;
                for(let product of data) {
                    let template    = $(`#fragment-product`)[0];
                    let clone       = document.importNode(template.content.childNodes[1], true);
                    let element     = $(clone);
                    element                                    .attr(`data-product-id`, product.PRODUCT_ID);
                    element                                    .attr(`data-product-price`, product.PRICE);
                    element.find(`.cart-list-good-name`)       .text(product.PRODUCT.NAME);
                    element.find(`.cart-list-good-article`)    .text(`Art.-Nr.: ` + product.PRODUCT.PROPERTY_ARTNUMBER_VALUE);
                    element.find(`.cart-list-price`)           .text(product.SUM_FORMATTED_PRICE);
                    element.find(`.cart-list-good-mass`)       .text(product.WEIGHT + ` г`);
                    element.find(`.cart-list-good-img`)        .children().attr(`src`, product.PRODUCT.PREVIEW_PICTURE.src);
                    element.find(`.cart-list-input`)           .children().attr(`value`, parseInt(product.QUANTITY, 10));
                    element.find(`.cart-list-good-img`)        .attr(`href`, product.PRODUCT.DETAIL_PAGE_URL);
                    element.find(`.cart-list-good-name`)       .attr(`href`, product.PRODUCT.DETAIL_PAGE_URL);
                    $(`.products-container`).append(element);
                    element.delay(fade_interval * count).fadeIn(fade_time);
                    count++;
                }

                refreshEventsHandlers();
            }
        });
    };

    $(() => {
        refreshEventsHandlers();
        refreshBasketInfo();
        refreshProductsList();
    });
</script>
