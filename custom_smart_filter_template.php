<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addJs("{$componentPath}/component.js");
?>

<!-- filter-->
<div class="filter">
    <div class="filter-head d-lg-none">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <div class="filter-head-title">Фильтр</div>
                </div>
                <div class="col-auto"><a class="filter-close" href="#">Закрыть</a></div>
            </div>
        </div>
    </div>
    <div class="filter-slide">
        <? foreach ($arResult["PROPERTIES"] as $prop) :?>
            <!-- el-->
            <div class="filter-item active activen"><a class="filter-link" href="#"><?=$prop['NAME']?></a>
                <div class="filter-item-slide">
                    <ul class="list-ch" data-type="<?=$prop["LIST_TYPE"]?>" data-name="<?=$prop["CODE"]?>">
                        <? foreach ($prop["VALUES"] as $value) :?>
                            <li>
                                <label class="ch-label">
                                    <input type="checkbox" data-value="<?=$value["PROPERTY_{$prop["CODE"]}_VALUE"]?>">
                                    <div><?=$value["PROPERTY_{$prop["CODE"]}_VALUE"]?><span>(<?=$value["CNT"]?>)</span></div>
                                </label>
                            </li>
                        <?endforeach;?>
                    </ul>
                </div>
            </div>
            <!-- / el-->
        <?endforeach;?>
        <!-- el-->
        <div class="filter-item active activen"><a class="filter-link" href="#">Цена</a>
            <div class="filter-item-slide">
                <div id="slider-price"></div>
                <div class="filter-price">
                    <div class="d-flex align-items-center">
                        <div class="filter-price-label">от</div>
                        <div class="filter-price-input">
                            <input id="prinputmin" type="text" disabled="disabled">
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="filter-price-label pl-3 pl-lg-0">до</div>
                        <div class="filter-price-input">
                            <input id="prinputmax" type="text" disabled="disabled">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- / el-->
        <!-- el-->
        <div class="filter-item filter-result">
            <div class="filter-result-sortinfo">Подобрано 25 товаров</div>
            <button class="mbtn mbtn-primary submit-button">Показать результаты</button>
            <div class="filter-result-clear">
                <a class="mbtn" href="#">Сбросить фильтр</a>
            </div>
        </div>
        <!-- / el-->
    </div>
</div>
<!-- /filter-->

<script>
    getFilterParams = () => {
        let params = [];
        $(`ul[data-type='C']`).each((index, element) => {
            let name = $(element).attr(`data-name`);
            let type = $(element).attr(`data-type`);
            let values = [];
            $(element).find(`[data-value]`).each((index, element) => {
                if($(element).is(`:checked`)) {
                    values.push($(element).attr(`data-value`));
                }
            });
            params.push(
                {
                    type    : type,
                    name    : name,
                    values  : values
                }
            );
        });
        return params;
    };

    applyFilterParams = () => {
        let arr = getFilterParams();
        let str = window.location.search.substr(1);
        $(arr).each((index, element) => {
            let name = element.name;
            let values = element.values.join(`,`);
            str = buildGetRequestParams(
                {
                    new_query : `filter_${name}=${values}`,
                    old_query : str,
                }
            );
        });
        window.location.search = str;
    };

    refreshEventsHandlers = () => {
        $(`.submit-button`).click((e) => {
            e.preventDefault();
            applyFilterParams();
        });
    };

    $(() => {
        refreshEventsHandlers();
    });
</script>
